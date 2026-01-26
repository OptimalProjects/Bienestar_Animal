<?php

namespace App\Services;

use App\Repositories\Contracts\DenunciaRepositoryInterface;
use App\Models\Denuncia\Denuncia;
use App\Models\Denuncia\Denunciante;
use App\Models\Denuncia\Rescate;
use App\Models\User\Usuario;
use App\Models\User\Rol;
use App\Mail\DenunciaMail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

            // Asignar automáticamente a un operador de rescate disponible
            $operadorId = $this->obtenerOperadorDisponible();

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
                'estado' => $operadorId ? 'asignada' : 'recibida',
                'es_anonima' => $esAnonima,
                'responsable_id' => $operadorId,
                'fecha_asignacion' => $operadorId ? now() : null,
            ]);

            // Cargar relaciones para el correo
            $denuncia = $denuncia->fresh(['responsable', 'denunciante']);

            // Enviar notificación al operador asignado
            if ($operadorId) {
                $this->enviarNotificacionDenuncia($denuncia, 'nueva');

                Log::info('Denuncia asignada automáticamente a operador de rescate', [
                    'denuncia_id' => $denuncia->id,
                    'ticket' => $ticket,
                    'operador_id' => $operadorId,
                    'operador' => $denuncia->responsable->nombre_completo ?? 'N/A',
                ]);
            }

            return [
                'denuncia' => $denuncia,
                'ticket' => $ticket,
                'mensaje' => 'Denuncia registrada exitosamente. Guarde su numero de ticket para consultar el estado.',
            ];
        });
    }

    /**
     * Asignar denuncia a funcionario (rescatista).
     */
    public function asignar(string $id, string $funcionarioId, ?string $asignadoPorId = null): Denuncia
    {
        $denuncia = Denuncia::findOrFail($id);

        $denuncia->update([
            'responsable_id' => $funcionarioId,
            'estado' => 'asignada',
            'fecha_asignacion' => now(),
        ]);

        $denuncia = $denuncia->fresh(['responsable', 'denunciante', 'rescates']);

        // Enviar notificación por correo al rescatista asignado
        $this->enviarNotificacionDenuncia($denuncia, 'nueva');

        return $denuncia;
    }

    /**
     * Actualizar estado de denuncia.
     */
    public function actualizarEstado(string $id, string $estado, ?array $data = []): Denuncia
    {
        $denuncia = Denuncia::findOrFail($id);

        $updateData = ['estado' => $estado];

        // Si es resuelta o cerrada, establecer fecha de resolución
        if (in_array($estado, ['resuelta', 'cerrada'])) {
            $updateData['fecha_resolucion'] = now();
        }

        // Guardar observaciones de resolución si vienen
        if (!empty($data['observaciones_resolucion'])) {
            $updateData['observaciones_resolucion'] = $data['observaciones_resolucion'];
        }

        $denuncia->update($updateData);

        $denuncia = $denuncia->fresh(['responsable', 'denunciante', 'rescates']);

        // Enviar notificación según el nuevo estado
        if (in_array($estado, ['resuelta', 'cerrada'])) {
            $this->enviarNotificacionDenuncia($denuncia, $estado);
        }

        return $denuncia;
    }

    /**
     * Registrar rescate asociado a denuncia (asignacion de equipo).
     */
    public function registrarRescate(string $denunciaId, array $data): Rescate
    {
        return DB::transaction(function () use ($denunciaId, $data) {
            $denuncia = Denuncia::findOrFail($denunciaId);

            $rescate = Rescate::create([
                'denuncia_id' => $denunciaId,
                'fecha_programada' => $data['fecha_programada'],
                'equipo_rescate' => $data['equipo_rescate'] ?? null,
                'observaciones' => $data['observaciones'] ?? null,
                'animal_rescatado_id' => $data['animal_id'] ?? null,
            ]);

            // Actualizar denuncia a en_atencion cuando se asigna rescate
            $denuncia->update([
                'estado' => 'en_atencion',
            ]);

            $denuncia = $denuncia->fresh(['responsable', 'denunciante', 'rescates']);

            // Enviar notificación de que está en atención
            $this->enviarNotificacionDenuncia($denuncia, 'en_atencion');

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

    /**
     * Obtener operador de rescate disponible con menor carga de trabajo.
     *
     * @return string|null ID del operador o null si no hay disponibles
     */
    protected function obtenerOperadorDisponible(): ?string
    {
        // Buscar el rol de operador de rescate
        $rolOperador = Rol::where('codigo', 'OPERADOR')->first();

        if (!$rolOperador) {
            Log::warning('No se encontró el rol OPERADOR para asignar denuncias');
            return null;
        }

        // Obtener todos los operadores activos
        $operadores = Usuario::activos()
            ->whereHas('roles', function ($query) use ($rolOperador) {
                $query->where('rol_id', $rolOperador->id)
                      ->where('usuario_rol.activo', true);
            })
            ->get();

        if ($operadores->isEmpty()) {
            Log::warning('No hay operadores de rescate activos disponibles');
            return null;
        }

        // Calcular carga de trabajo (denuncias pendientes asignadas)
        $operadorConMenosCarga = $operadores->map(function ($operador) {
            $denunciasPendientes = Denuncia::where('responsable_id', $operador->id)
                ->whereNotIn('estado', ['resuelta', 'cerrada', 'desestimada'])
                ->count();

            return [
                'id' => $operador->id,
                'nombre' => $operador->nombre_completo,
                'carga' => $denunciasPendientes,
            ];
        })->sortBy('carga')->first();

        Log::info('Operador de rescate seleccionado para asignación', [
            'operador_id' => $operadorConMenosCarga['id'],
            'operador_nombre' => $operadorConMenosCarga['nombre'],
            'carga_actual' => $operadorConMenosCarga['carga'],
        ]);

        return $operadorConMenosCarga['id'];
    }

    /**
     * Enviar notificación de denuncia por correo electrónico al rescatista.
     *
     * @param Denuncia $denuncia
     * @param string $tipo 'nueva', 'asignada', 'en_atencion', 'resuelta', 'cerrada'
     */
    protected function enviarNotificacionDenuncia(Denuncia $denuncia, string $tipo): void
    {
        try {
            // Notificar al rescatista/responsable asignado
            $emailResponsable = $denuncia->responsable->email ?? null;

            if ($emailResponsable && filter_var($emailResponsable, FILTER_VALIDATE_EMAIL)) {
                Mail::to($emailResponsable)->send(new DenunciaMail($denuncia, $tipo));

                Log::info("Notificación de denuncia [{$tipo}] enviada al rescatista", [
                    'denuncia_id' => $denuncia->id,
                    'ticket' => $denuncia->numero_ticket,
                    'tipo_denuncia' => $denuncia->tipo_denuncia,
                    'prioridad' => $denuncia->prioridad,
                    'destinatario' => $emailResponsable,
                    'responsable' => $denuncia->responsable->nombre_completo ?? $denuncia->responsable->nombres,
                ]);
            } else {
                Log::warning('No se pudo enviar notificación de denuncia: email del responsable no válido', [
                    'denuncia_id' => $denuncia->id,
                    'ticket' => $denuncia->numero_ticket,
                    'responsable_id' => $denuncia->responsable_id,
                ]);
            }
        } catch (\Exception $e) {
            // No interrumpir el flujo si falla el envío de correo
            Log::error("Error enviando notificación de denuncia [{$tipo}]", [
                'denuncia_id' => $denuncia->id,
                'ticket' => $denuncia->numero_ticket,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
