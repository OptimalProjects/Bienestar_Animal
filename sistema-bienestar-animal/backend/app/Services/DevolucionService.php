<?php

namespace App\Services;

use App\Models\Adopcion\Adopcion;
use App\Models\Adopcion\Devolucion;
use App\Models\Animal\Animal;
use App\Models\Animal\HistorialClinico;
use App\Models\Veterinaria\Consulta;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DevolucionService
{
    /**
     * Registrar una devolución de animal adoptado.
     */
    public function registrarDevolucion(array $data, string $usuarioId): array
    {
        return DB::transaction(function () use ($data, $usuarioId) {
            // Obtener la adopción
            $adopcion = Adopcion::with(['animal', 'adoptante'])->findOrFail($data['adopcion_id']);

            // Validar que la adopción esté completada
            if ($adopcion->estado !== 'completada') {
                throw new \Exception('Solo se pueden devolver animales de adopciones completadas.');
            }

            // Validar que no exista ya una devolución
            if ($adopcion->devolucion) {
                throw new \Exception('Esta adopción ya tiene una devolución registrada.');
            }

            $animal = $adopcion->animal;

            // Determinar el estado inicial del animal según el estado reportado
            $estadoAnimal = $this->determinarEstadoAnimal($data['estado_animal_devolucion'] ?? 'bueno');

            // Crear registro de devolución
            $devolucion = Devolucion::create([
                'adopcion_id' => $adopcion->id,
                'animal_id' => $animal->id,
                'registrado_por' => $usuarioId,
                'fecha_devolucion' => $data['fecha_devolucion'] ?? now()->format('Y-m-d'),
                'motivo' => $data['motivo'],
                'descripcion_motivo' => $data['descripcion_motivo'],
                'estado_animal_devolucion' => $data['estado_animal_devolucion'] ?? 'bueno',
                'observaciones_estado' => $data['observaciones_estado'] ?? null,
                'estado_proceso' => 'recibido',
                'revision_veterinaria_completada' => false,
                'fecha_revision_programada' => now()->addDays(1)->format('Y-m-d'), // Programar para mañana
            ]);

            // Actualizar estado de la adopción
            $adopcion->update([
                'estado' => 'devuelta',
                'motivo_revocacion' => "Devolución voluntaria: " . Devolucion::MOTIVOS[$data['motivo']] . ". " . $data['descripcion_motivo'],
            ]);

            // Cambiar estado del animal
            $animal->update([
                'estado' => $estadoAnimal,
            ]);

            // Programar consulta de revisión veterinaria obligatoria
            $consulta = $this->programarRevisionVeterinaria($animal, $devolucion, $usuarioId);

            if ($consulta) {
                $devolucion->update([
                    'consulta_revision_id' => $consulta->id,
                    'estado_proceso' => 'en_revision',
                ]);
            }

            // Registrar en historial del animal
            $this->registrarEnHistorial($animal, $devolucion, $adopcion);

            Log::info('Devolución de animal registrada', [
                'devolucion_id' => $devolucion->id,
                'adopcion_id' => $adopcion->id,
                'animal_id' => $animal->id,
                'motivo' => $data['motivo'],
                'registrado_por' => $usuarioId,
            ]);

            return [
                'devolucion' => $devolucion->fresh(['adopcion', 'animal', 'registradoPor', 'consultaRevision']),
                'consulta_programada' => $consulta,
                'mensaje' => 'Devolución registrada exitosamente. Se ha programado una revisión veterinaria obligatoria.',
            ];
        });
    }

    /**
     * Determinar el estado del animal según el estado reportado en devolución.
     */
    protected function determinarEstadoAnimal(string $estadoDevolucion): string
    {
        return match ($estadoDevolucion) {
            'critico', 'malo' => 'en_tratamiento',
            'regular' => 'en_refugio',
            default => 'en_refugio', // 'bueno' también va a refugio hasta revisión
        };
    }

    /**
     * Programar revisión veterinaria obligatoria.
     */
    protected function programarRevisionVeterinaria(Animal $animal, Devolucion $devolucion, string $usuarioId): ?Consulta
    {
        try {
            // Obtener o crear historial clínico
            $historial = HistorialClinico::firstOrCreate(
                ['animal_id' => $animal->id],
                [
                    'fecha_apertura' => now(),
                    'estado' => 'activo',
                ]
            );

            // Crear consulta de revisión
            $consulta = Consulta::create([
                'historial_clinico_id' => $historial->id,
                'veterinario_id' => null, // Se asignará después
                'fecha_consulta' => now()->addDays(1), // Mañana
                'motivo' => 'Revisión obligatoria por devolución de adopción',
                'sintomas' => "Animal devuelto. Motivo de devolución: " . Devolucion::MOTIVOS[$devolucion->motivo] . ". Estado reportado: " . Devolucion::ESTADOS_ANIMAL[$devolucion->estado_animal_devolucion],
                'diagnostico' => null,
                'observaciones' => $devolucion->observaciones_estado ?? 'Pendiente de evaluación veterinaria completa antes de re-adopción.',
                'tipo_consulta' => 'control',
                'proxima_cita' => null,
            ]);

            Log::info('Consulta de revisión programada para animal devuelto', [
                'consulta_id' => $consulta->id,
                'animal_id' => $animal->id,
                'devolucion_id' => $devolucion->id,
            ]);

            return $consulta;
        } catch (\Exception $e) {
            Log::error('Error al programar revisión veterinaria', [
                'animal_id' => $animal->id,
                'devolucion_id' => $devolucion->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Registrar devolución en historial del animal.
     */
    protected function registrarEnHistorial(Animal $animal, Devolucion $devolucion, Adopcion $adopcion): void
    {
        // El historial se registra a través de la consulta creada
        // Adicionalmente, actualizamos las observaciones del animal
        $historialTexto = sprintf(
            "[%s] DEVOLUCIÓN: Animal devuelto por %s. Motivo: %s. Estado al momento de devolución: %s. Adopción ID: %s",
            now()->format('Y-m-d H:i'),
            $adopcion->adoptante->nombre_completo ?? 'Adoptante',
            Devolucion::MOTIVOS[$devolucion->motivo],
            Devolucion::ESTADOS_ANIMAL[$devolucion->estado_animal_devolucion],
            $adopcion->id
        );

        $observacionesActuales = $animal->observaciones ?? '';
        $animal->update([
            'observaciones' => $observacionesActuales . "\n\n" . $historialTexto,
        ]);
    }

    /**
     * Completar revisión veterinaria y aprobar para re-adopción.
     */
    public function completarRevision(string $devolucionId, array $data, string $usuarioId): Devolucion
    {
        return DB::transaction(function () use ($devolucionId, $data, $usuarioId) {
            $devolucion = Devolucion::with(['animal', 'adopcion'])->findOrFail($devolucionId);

            $animal = $devolucion->animal;

            // Actualizar la consulta si viene información
            if ($devolucion->consultaRevision && isset($data['diagnostico'])) {
                $devolucion->consultaRevision->update([
                    'diagnostico' => $data['diagnostico'],
                    'observaciones' => $data['observaciones_veterinario'] ?? null,
                    'recomendaciones' => $data['recomendaciones'] ?? null,
                ]);
            }

            // Determinar si puede ir a adopción o necesita tratamiento
            $aptoAdopcion = $data['apto_adopcion'] ?? false;

            if ($aptoAdopcion) {
                $devolucion->update([
                    'revision_veterinaria_completada' => true,
                    'estado_proceso' => 'aprobado_readopcion',
                ]);

                // Cambiar estado del animal a disponible para adopción
                $animal->update([
                    'estado' => 'en_adopcion',
                    'estado_salud' => $data['estado_salud'] ?? 'bueno',
                ]);

                Log::info('Animal aprobado para re-adopción', [
                    'devolucion_id' => $devolucion->id,
                    'animal_id' => $animal->id,
                ]);
            } else {
                $devolucion->update([
                    'revision_veterinaria_completada' => true,
                    'estado_proceso' => 'en_tratamiento',
                ]);

                // Mantener o cambiar a tratamiento
                $animal->update([
                    'estado' => 'en_tratamiento',
                    'estado_salud' => $data['estado_salud'] ?? 'regular',
                ]);

                Log::info('Animal requiere tratamiento antes de re-adopción', [
                    'devolucion_id' => $devolucion->id,
                    'animal_id' => $animal->id,
                ]);
            }

            return $devolucion->fresh(['animal', 'adopcion', 'consultaRevision']);
        });
    }

    /**
     * Listar devoluciones con paginación.
     */
    public function listar(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Devolucion::with(['adopcion.adoptante', 'animal', 'registradoPor']);

        if (!empty($filters['motivo'])) {
            $query->where('motivo', $filters['motivo']);
        }

        if (!empty($filters['estado_proceso'])) {
            $query->where('estado_proceso', $filters['estado_proceso']);
        }

        if (!empty($filters['fecha_desde'])) {
            $query->whereDate('fecha_devolucion', '>=', $filters['fecha_desde']);
        }

        if (!empty($filters['fecha_hasta'])) {
            $query->whereDate('fecha_devolucion', '<=', $filters['fecha_hasta']);
        }

        if (isset($filters['pendientes_revision']) && $filters['pendientes_revision']) {
            $query->pendientesRevision();
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Obtener devolución por ID.
     */
    public function obtener(string $id): Devolucion
    {
        return Devolucion::with([
            'adopcion.adoptante',
            'adopcion.visitasDomiciliarias',
            'animal',
            'registradoPor',
            'consultaRevision',
        ])->findOrFail($id);
    }

    /**
     * Obtener estadísticas de devoluciones.
     */
    public function getEstadisticas(): array
    {
        $total = Devolucion::count();
        $delMes = Devolucion::delMes()->count();
        $pendientesRevision = Devolucion::pendientesRevision()->count();
        $listasReadopcion = Devolucion::listasReadopcion()->count();

        // Distribución por motivo
        $porMotivo = Devolucion::selectRaw('motivo, COUNT(*) as total')
            ->groupBy('motivo')
            ->pluck('total', 'motivo')
            ->toArray();

        // Distribución por estado del proceso
        $porEstado = Devolucion::selectRaw('estado_proceso, COUNT(*) as total')
            ->groupBy('estado_proceso')
            ->pluck('total', 'estado_proceso')
            ->toArray();

        return [
            'total' => $total,
            'del_mes' => $delMes,
            'pendientes_revision' => $pendientesRevision,
            'listas_readopcion' => $listasReadopcion,
            'por_motivo' => $porMotivo,
            'por_estado' => $porEstado,
        ];
    }

    /**
     * Obtener motivos disponibles.
     */
    public function getMotivos(): array
    {
        return Devolucion::MOTIVOS;
    }
}
