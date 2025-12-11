<?php

namespace App\Services;

use App\Repositories\Contracts\AdopcionRepositoryInterface;
use App\Models\Adopcion\Adopcion;
use App\Models\Adopcion\Adoptante;
use App\Models\Adopcion\VisitaDomiciliaria;
use App\Models\Animal\Animal;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AdopcionService
{
    public function __construct(
        protected AdopcionRepositoryInterface $adopcionRepository
    ) {}

    /**
     * Listar adopciones con paginacion.
     */
    public function listar(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->adopcionRepository->paginateWithFilters($perPage, $filters);
    }

    /**
     * Obtener adopciones pendientes de evaluacion.
     */
    public function getPendientes(): Collection
    {
        return $this->adopcionRepository->getPendientesEvaluacion();
    }

    /**
     * Obtener adopcion con todos los detalles.
     */
    public function obtener(string $id)
    {
        return $this->adopcionRepository->getWithVisitas($id);
    }

    /**
     * Crear solicitud de adopcion.
     */
    public function crearSolicitud(array $data): Adopcion
    {
        return DB::transaction(function () use ($data) {
            // Verificar que el animal este disponible
            $animal = Animal::findOrFail($data['animal_id']);
            if ($animal->estado !== 'en_adopcion') {
                throw new \InvalidArgumentException('El animal no esta disponible para adopcion');
            }

            // Buscar o crear adoptante
            $adoptante = $this->obtenerOCrearAdoptante($data['adoptante']);

            // Crear solicitud
            $adopcion = Adopcion::create([
                'animal_id' => $data['animal_id'],
                'adoptante_id' => $adoptante->id,
                'fecha_solicitud' => now(),
                'estado' => 'pendiente',
                'motivo_adopcion' => $data['motivo_adopcion'] ?? null,
                'experiencia_mascotas' => $data['experiencia_mascotas'] ?? null,
                'tiene_otras_mascotas' => $data['tiene_otras_mascotas'] ?? false,
                'descripcion_hogar' => $data['descripcion_hogar'] ?? null,
                'acepta_visita_seguimiento' => $data['acepta_visita_seguimiento'] ?? true,
            ]);

            return $adopcion->fresh(['animal', 'adoptante']);
        });
    }

    /**
     * Evaluar solicitud de adopcion.
     */
    public function evaluar(string $id, array $data, string $evaluadorId): Adopcion
    {
        return DB::transaction(function () use ($id, $data, $evaluadorId) {
            $adopcion = Adopcion::findOrFail($id);

            if ($adopcion->estado !== 'pendiente') {
                throw new \InvalidArgumentException('La solicitud ya fue evaluada');
            }

            $nuevoEstado = $data['aprobada'] ? 'aprobada' : 'rechazada';

            $adopcion->update([
                'estado' => $nuevoEstado,
                'evaluado_por' => $evaluadorId,
                'fecha_evaluacion' => now(),
                'notas_evaluacion' => $data['notas'] ?? null,
                'motivo_rechazo' => !$data['aprobada'] ? ($data['motivo_rechazo'] ?? null) : null,
            ]);

            // Si se aprueba, actualizar estado del animal
            if ($data['aprobada']) {
                $adopcion->animal->update(['estado' => 'adoptado']);

                // Programar primera visita de seguimiento si aplica
                if ($adopcion->acepta_visita_seguimiento) {
                    $this->programarVisitaSeguimiento($adopcion->id, [
                        'fecha_programada' => now()->addMonth(),
                        'tipo_visita' => 'seguimiento_inicial',
                    ]);
                }
            }

            return $adopcion->fresh(['animal', 'adoptante', 'evaluador']);
        });
    }

    /**
     * Programar visita de seguimiento.
     */
    public function programarVisitaSeguimiento(string $adopcionId, array $data): VisitaDomiciliaria
    {
        $adopcion = Adopcion::findOrFail($adopcionId);

        return VisitaDomiciliaria::create([
            'adopcion_id' => $adopcionId,
            'fecha_programada' => $data['fecha_programada'],
            'tipo_visita' => $data['tipo_visita'] ?? 'seguimiento',
            'estado' => 'programada',
            'funcionario_id' => $data['funcionario_id'] ?? null,
        ]);
    }

    /**
     * Registrar visita de seguimiento realizada.
     */
    public function registrarVisita(string $visitaId, array $data, string $funcionarioId): VisitaDomiciliaria
    {
        $visita = VisitaDomiciliaria::findOrFail($visitaId);

        $visita->update([
            'fecha_visita' => $data['fecha_visita'] ?? now(),
            'funcionario_id' => $funcionarioId,
            'estado' => 'realizada',
            'estado_animal' => $data['estado_animal'],
            'condiciones_vivienda' => $data['condiciones_vivienda'],
            'observaciones' => $data['observaciones'] ?? null,
            'recomendaciones' => $data['recomendaciones'] ?? null,
            'requiere_seguimiento' => $data['requiere_seguimiento'] ?? false,
        ]);

        // Si requiere seguimiento, programar proxima visita
        if ($data['requiere_seguimiento'] ?? false) {
            $this->programarVisitaSeguimiento($visita->adopcion_id, [
                'fecha_programada' => now()->addMonths(3),
                'tipo_visita' => 'seguimiento_adicional',
            ]);
        }

        return $visita->fresh(['adopcion', 'funcionario']);
    }

    /**
     * Obtener adopciones que requieren visita.
     */
    public function getRequierenVisita(): Collection
    {
        return $this->adopcionRepository->getRequierenVisita();
    }

    /**
     * Generar contrato de adopcion.
     */
    public function generarContrato(string $adopcionId): array
    {
        $adopcion = $this->adopcionRepository->getWithVisitas($adopcionId);

        if ($adopcion->estado !== 'aprobada') {
            throw new \InvalidArgumentException('Solo se puede generar contrato para adopciones aprobadas');
        }

        return [
            'numero_contrato' => 'CONT-' . date('Y') . '-' . str_pad($adopcion->id, 6, '0', STR_PAD_LEFT),
            'fecha_generacion' => now()->toDateString(),
            'adopcion' => $adopcion,
            'adoptante' => $adopcion->adoptante,
            'animal' => $adopcion->animal,
            'compromisos' => $this->getCompromisosAdopcion(),
        ];
    }

    /**
     * Obtener estadisticas.
     */
    public function getEstadisticas(): array
    {
        return $this->adopcionRepository->getEstadisticas();
    }

    /**
     * Obtener o crear adoptante.
     */
    protected function obtenerOCrearAdoptante(array $data): Adoptante
    {
        $adoptante = Adoptante::where('documento_identidad', $data['documento_identidad'])->first();

        if (!$adoptante) {
            $adoptante = Adoptante::create([
                'tipo_documento' => $data['tipo_documento'],
                'documento_identidad' => $data['documento_identidad'],
                'nombre_completo' => $data['nombre_completo'],
                'telefono' => $data['telefono'],
                'email' => $data['email'] ?? null,
                'direccion' => $data['direccion'],
                'comuna' => $data['comuna'] ?? null,
                'barrio' => $data['barrio'] ?? null,
                'ocupacion' => $data['ocupacion'] ?? null,
            ]);
        }

        return $adoptante;
    }

    /**
     * Obtener lista de compromisos de adopcion.
     */
    protected function getCompromisosAdopcion(): array
    {
        return [
            'Proporcionar alimentacion adecuada y agua fresca diariamente',
            'Brindar atencion veterinaria cuando sea necesario',
            'Mantener al animal en condiciones higienicas apropiadas',
            'No abandonar, maltratar ni sacrificar al animal',
            'Permitir visitas de seguimiento del programa',
            'Notificar cualquier cambio de domicilio o situacion del animal',
            'Esterilizar al animal si no lo esta (aplica condiciones)',
            'Cumplir con el calendario de vacunacion',
        ];
    }
}
