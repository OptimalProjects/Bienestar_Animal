<?php

namespace App\Services;

use App\Repositories\Contracts\DenunciaRepositoryInterface;
use App\Models\Denuncia\Denuncia;
use App\Models\Denuncia\Denunciante;
use App\Models\Denuncia\Rescate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DenunciaService
{
    public function __construct(
        protected DenunciaRepositoryInterface $denunciaRepository
    ) {}

    /**
     * Listar denuncias con paginacion.
     */
    public function listar(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->denunciaRepository->paginateWithFilters($perPage, $filters);
    }

    /**
     * Obtener denuncia por ID.
     */
    public function obtener(string $id)
    {
        return $this->denunciaRepository->getWithRescates($id);
    }

    /**
     * Consultar denuncia por ticket (publico).
     */
    public function consultarPorTicket(string $ticket)
    {
        $denuncia = $this->denunciaRepository->findByTicket($ticket);

        if (!$denuncia) {
            return null;
        }

        // Retornar solo informacion publica (con zona horaria de Colombia)
        return [
            'ticket' => $denuncia->numero_ticket,
            'fecha_registro' => $denuncia->fecha_denuncia->setTimezone('America/Bogota')->toIso8601String(),
            'tipo' => $denuncia->tipo_denuncia,
            'estado' => $denuncia->estado,
            'prioridad' => $denuncia->prioridad,
            'ubicacion' => $denuncia->ubicacion,
            'fecha_resolucion' => $denuncia->fecha_resolucion?->setTimezone('America/Bogota')->toIso8601String(),
            'observaciones' => $denuncia->observaciones_resolucion,
        ];
    }

    /**
     * Registrar nueva denuncia (puede ser anonima).
     */
    public function registrar(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // Crear o vincular denunciante (puede ser anonimo)
            $denuncianteId = null;
            $esAnonima = $data['es_anonima'] ?? $data['anonima'] ?? false;

            if (!empty($data['denunciante']) && !$esAnonima) {
                $denunciante = $this->obtenerOCrearDenunciante($data['denunciante']);
                $denuncianteId = $denunciante->id;
            }

            // Generar ticket de consulta
            $ticket = $this->generarTicket();

            // Usar prioridad del frontend si viene, sino clasificar automaticamente
            $prioridad = $data['prioridad'] ?? Denuncia::clasificarPrioridad(
                $data['tipo_denuncia'],
                $data['descripcion']
            );

            // Crear denuncia
            $denuncia = Denuncia::create([
                'denunciante_id' => $denuncianteId,
                'numero_ticket' => $ticket,
                'fecha_denuncia' => now(),
                'canal_recepcion' => $data['canal_recepcion'] ?? 'web',
                'tipo_denuncia' => $data['tipo_denuncia'],
                'descripcion' => $data['descripcion'],
                'ubicacion' => $data['ubicacion'] ?? $data['direccion'] ?? '',
                'latitud' => $data['latitud'] ?? $data['coordenadas_lat'] ?? null,
                'longitud' => $data['longitud'] ?? $data['coordenadas_lng'] ?? null,
                'evidencias' => $data['evidencias'] ?? null,
                'prioridad' => $prioridad,
                'estado' => 'recibida',
                'es_anonima' => $esAnonima,
            ]);

            return [
                'denuncia' => $denuncia,
                'ticket' => $ticket,
                'mensaje' => 'Denuncia registrada exitosamente. Guarde su numero de ticket para consultar el estado.',
            ];
        });
    }

    /**
     * Asignar denuncia a funcionario.
     */
    public function asignar(string $id, string $funcionarioId, ?string $asignadoPorId = null): Denuncia
    {
        $denuncia = Denuncia::findOrFail($id);

        $denuncia->update([
            'asignado_a' => $funcionarioId,
            'estado' => 'en_proceso',
            'fecha_asignacion' => now(),
        ]);

        return $denuncia->fresh(['asignadoA', 'denunciante']);
    }

    /**
     * Actualizar estado de denuncia.
     */
    public function actualizarEstado(string $id, string $estado, ?array $data = []): Denuncia
    {
        $denuncia = Denuncia::findOrFail($id);

        $updateData = ['estado' => $estado];

        if ($estado === 'resuelta') {
            $updateData['fecha_resolucion'] = now();
            $updateData['resolucion'] = $data['resolucion'] ?? null;
        }

        if (!empty($data['observaciones'])) {
            $updateData['observaciones_internas'] = $data['observaciones'];
        }

        $denuncia->update($updateData);

        return $denuncia->fresh();
    }

    /**
     * Registrar rescate asociado a denuncia.
     */
    public function registrarRescate(string $denunciaId, array $data): Rescate
    {
        return DB::transaction(function () use ($denunciaId, $data) {
            $denuncia = Denuncia::findOrFail($denunciaId);

            $rescate = Rescate::create([
                'denuncia_id' => $denunciaId,
                'animal_rescatado_id' => $data['animal_id'] ?? null,
                'fecha_rescate' => $data['fecha_rescate'] ?? now(),
                'responsable_rescate' => $data['responsable_rescate'],
                'descripcion_rescate' => $data['descripcion_rescate'] ?? null,
                'estado_animal_rescate' => $data['estado_animal_rescate'],
                'destino' => $data['destino'] ?? 'refugio',
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            // Actualizar denuncia si todos los rescates estan completos
            if (!empty($data['cierra_denuncia'])) {
                $denuncia->update([
                    'estado' => 'resuelta',
                    'fecha_resolucion' => now(),
                    'resolucion' => 'Rescate realizado exitosamente',
                ]);
            }

            return $rescate->fresh(['denuncia', 'animalRescatado']);
        });
    }

    /**
     * Obtener denuncias urgentes sin asignar.
     */
    public function getUrgentesSinAsignar(): Collection
    {
        return $this->denunciaRepository->getUrgentesSinAsignar();
    }

    /**
     * Obtener denuncias asignadas a funcionario.
     */
    public function getMisAsignaciones(string $funcionarioId): Collection
    {
        return $this->denunciaRepository->findByAsignadoA($funcionarioId);
    }

    /**
     * Obtener estadisticas.
     */
    public function getEstadisticas(): array
    {
        return $this->denunciaRepository->getEstadisticas();
    }

    /**
     * Obtener mapa de calor por comuna.
     */
    public function getMapaCalor(): array
    {
        return $this->denunciaRepository->getMapaCalorPorComuna();
    }

    /**
     * Generar ticket de consulta unico.
     */
    protected function generarTicket(): string
    {
        do {
            $ticket = 'DN-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (Denuncia::where('numero_ticket', $ticket)->exists());

        return $ticket;
    }

    /**
     * Obtener o crear denunciante.
     */
    protected function obtenerOCrearDenunciante(array $data): Denunciante
    {
        // Buscar por email si se proporciona (para evitar duplicados)
        if (!empty($data['email'])) {
            $denunciante = Denunciante::where('email', $data['email'])->first();
            if ($denunciante) {
                return $denunciante;
            }
        }

        // Buscar por telefono si se proporciona
        if (!empty($data['telefono'])) {
            $denunciante = Denunciante::where('telefono', $data['telefono'])->first();
            if ($denunciante) {
                return $denunciante;
            }
        }

        // Crear nuevo denunciante
        return Denunciante::create([
            'nombres' => $data['nombres'] ?? '',
            'apellidos' => $data['apellidos'] ?? '',
            'telefono' => $data['telefono'] ?? null,
            'email' => $data['email'] ?? null,
            'direccion' => $data['direccion'] ?? null,
        ]);
    }
}
