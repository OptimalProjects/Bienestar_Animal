<?php

namespace App\Services;

use App\Repositories\Contracts\AdopcionRepositoryInterface;
use App\Models\Adopcion\Adopcion;
use App\Models\Adopcion\Adoptante;
use App\Models\Adopcion\VisitaDomiciliaria;
use App\Models\Animal\Animal;
use App\Models\User\Usuario;
use App\Models\User\Rol;
use App\Mail\SolicitudAdopcionMail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
     * Listar todas las adopciones sin paginación (con filtros).
     */
    public function listarTodas(array $filters = []): Collection
    {
        return $this->adopcionRepository->getAllWithFilters($filters);
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
    public function crearSolicitud(array $data, array $archivos = []): Adopcion
    {
        return DB::transaction(function () use ($data, $archivos) {
            // Verificar que el animal este disponible
            $animal = Animal::findOrFail($data['animal_id']);
            if ($animal->estado !== 'en_adopcion') {
                throw new \InvalidArgumentException('El animal no esta disponible para adopcion');
            }

            // Buscar o crear adoptante
            $adoptante = $this->obtenerOCrearAdoptante($data['adoptante'], $archivos);

            // Crear solicitud - Concatenar toda la informacion adicional en observaciones
            $observaciones = [];
            if (!empty($data['motivo_adopcion'])) {
                $observaciones[] = "Motivo de adopcion: " . $data['motivo_adopcion'];
            }
            if (!empty($data['descripcion_hogar'])) {
                $observaciones[] = "Descripcion del hogar: " . $data['descripcion_hogar'];
            }
            if (isset($data['acepta_visita_seguimiento'])) {
                $acepta = filter_var($data['acepta_visita_seguimiento'], FILTER_VALIDATE_BOOLEAN);
                $observaciones[] = "Acepta visitas de seguimiento: " . ($acepta ? 'Si' : 'No');
            }

            // Obtener coordinador disponible para asignar como evaluador
            $evaluadorId = $this->obtenerCoordinadorDisponible();

            $adopcion = Adopcion::create([
                'animal_id' => $data['animal_id'],
                'adoptante_id' => $adoptante->id,
                'evaluador_id' => $evaluadorId,
                'fecha_solicitud' => now(),
                'estado' => 'solicitada',
                'observaciones' => !empty($observaciones) ? implode("\n", $observaciones) : null,
            ]);

            $adopcion = $adopcion->fresh(['animal', 'adoptante', 'evaluador']);

            // Enviar notificación por correo
            $this->enviarNotificacionAdopcion($adopcion, 'nueva');

            return $adopcion;
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

            $adopcion = $adopcion->fresh(['animal', 'adoptante', 'evaluador']);

            // Enviar notificación por correo
            $tipoNotificacion = $data['aprobada'] ? 'aprobada' : 'rechazada';
            $this->enviarNotificacionAdopcion($adopcion, $tipoNotificacion);

            return $adopcion;
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
    protected function obtenerOCrearAdoptante(array $data, array $archivos = []): Adoptante
    {
        $adoptante = Adoptante::where('numero_documento', $data['numero_documento'])->first();

        if (!$adoptante) {
            $adoptanteData = [
                'nombres' => $data['nombres'],
                'apellidos' => $data['apellidos'],
                'tipo_documento' => $data['tipo_documento'],
                'numero_documento' => $data['numero_documento'],
                'fecha_nacimiento' => $data['fecha_nacimiento'],
                'telefono' => $data['telefono'],
                'email' => $data['email'] ?? null,
                'direccion' => $data['direccion'],
                'tipo_vivienda' => $data['tipo_vivienda'] ?? 'casa',
                'tiene_patio' => filter_var($data['tiene_patio'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'experiencia_animales' => $data['experiencia_animales'] ?? null,
                'num_personas_hogar' => $data['num_personas_hogar'] ?? null,
                'estado' => 'activo',
            ];

            // Subir documentos si existen
            if (isset($archivos['copia_cedula'])) {
                $adoptanteData['copia_cedula'] = $this->guardarDocumento($archivos['copia_cedula'], 'adoptantes/cedulas');
            }
            if (isset($archivos['comprobante_domicilio'])) {
                $adoptanteData['comprobante_domicilio'] = $this->guardarDocumento($archivos['comprobante_domicilio'], 'adoptantes/domicilios');
            }

            $adoptante = Adoptante::create($adoptanteData);
        } else {
            // Actualizar datos del adoptante existente si hay cambios
            $updateData = [];

            if (isset($data['telefono'])) $updateData['telefono'] = $data['telefono'];
            if (isset($data['email'])) $updateData['email'] = $data['email'];
            if (isset($data['direccion'])) $updateData['direccion'] = $data['direccion'];
            if (isset($data['tipo_vivienda'])) $updateData['tipo_vivienda'] = $data['tipo_vivienda'];
            if (isset($data['tiene_patio'])) $updateData['tiene_patio'] = filter_var($data['tiene_patio'], FILTER_VALIDATE_BOOLEAN);
            if (isset($data['experiencia_animales'])) $updateData['experiencia_animales'] = $data['experiencia_animales'];
            if (isset($data['num_personas_hogar'])) $updateData['num_personas_hogar'] = $data['num_personas_hogar'];

            // Subir documentos si se proporcionan nuevos
            if (isset($archivos['copia_cedula'])) {
                $updateData['copia_cedula'] = $this->guardarDocumento($archivos['copia_cedula'], 'adoptantes/cedulas');
            }
            if (isset($archivos['comprobante_domicilio'])) {
                $updateData['comprobante_domicilio'] = $this->guardarDocumento($archivos['comprobante_domicilio'], 'adoptantes/domicilios');
            }

            if (!empty($updateData)) {
                $adoptante->update($updateData);
            }
        }

        return $adoptante;
    }

    /**
     * Guardar documento en storage S3.
     */
    protected function guardarDocumento($archivo, string $carpeta): string
    {
        $nombreArchivo = time() . '_' . uniqid() . '.' . $archivo->getClientOriginalExtension();
        $path = $archivo->storeAs('documentos/' . $carpeta, $nombreArchivo, 's3');
        return $path;
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

    /**
     * Obtener coordinador disponible para asignar como evaluador.
     * Por ahora asigna al coordinador con menos adopciones pendientes.
     * En el futuro se puede implementar logica mas avanzada de disponibilidad.
     */
    protected function obtenerCoordinadorDisponible(): ?string
    {
        // Buscar usuarios con rol COORDINADOR que esten activos
        $coordinadores = Usuario::activos()
            ->whereHas('roles', function ($query) {
                $query->where('codigo', 'COORDINADOR')
                      ->where('usuario_rol.activo', true);
            })
            ->get();

        if ($coordinadores->isEmpty()) {
            Log::warning('No hay coordinadores disponibles para asignar a la solicitud de adopcion');
            return null;
        }

        // Si solo hay un coordinador, retornarlo directamente
        if ($coordinadores->count() === 1) {
            return $coordinadores->first()->id;
        }

        // Asignar al coordinador con menos adopciones pendientes (balanceo de carga)
        $coordinadorConMenosCarga = $coordinadores->map(function ($coordinador) {
            $adopcionesPendientes = Adopcion::where('evaluador_id', $coordinador->id)
                ->whereIn('estado', ['solicitada', 'en_evaluacion'])
                ->count();

            return [
                'id' => $coordinador->id,
                'carga' => $adopcionesPendientes,
            ];
        })->sortBy('carga')->first();

        return $coordinadorConMenosCarga['id'];
    }

    /**
     * Enviar notificación de adopción por correo electrónico.
     *
     * @param Adopcion $adopcion
     * @param string $tipo 'nueva', 'aprobada', 'rechazada', 'completada'
     */
    protected function enviarNotificacionAdopcion(Adopcion $adopcion, string $tipo): void
    {
        try {
            $destinatarios = $this->obtenerDestinatariosNotificacion($adopcion, $tipo);

            foreach ($destinatarios as $email) {
                Mail::to($email)->send(new SolicitudAdopcionMail($adopcion, $tipo));
            }

            Log::info("Notificación de adopción [{$tipo}] enviada", [
                'adopcion_id' => $adopcion->id,
                'animal' => $adopcion->animal->nombre ?? $adopcion->animal->codigo_unico,
                'destinatarios' => $destinatarios,
            ]);
        } catch (\Exception $e) {
            // No interrumpir el flujo si falla el envío de correo
            Log::error("Error enviando notificación de adopción [{$tipo}]", [
                'adopcion_id' => $adopcion->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Obtener destinatarios para la notificación según el tipo.
     *
     * @param Adopcion $adopcion
     * @param string $tipo
     * @return array
     */
    protected function obtenerDestinatariosNotificacion(Adopcion $adopcion, string $tipo): array
    {
        $destinatarios = [];

        // Email del administrador/coordinador (siempre se notifica)
        $adminEmail = config('mail.admin_address') ?? env('MAIL_ADMIN_ADDRESS');
        if ($adminEmail) {
            $destinatarios[] = $adminEmail;
        }

        // Notificar al adoptante en todos los casos (nueva, aprobada, rechazada, completada)
        $emailAdoptante = $adopcion->adoptante->email ?? null;
        if ($emailAdoptante && filter_var($emailAdoptante, FILTER_VALIDATE_EMAIL)) {
            $destinatarios[] = $emailAdoptante;
        }

        // Si hay evaluador asignado, notificarle de nuevas solicitudes
        if ($tipo === 'nueva' && $adopcion->evaluador) {
            $emailEvaluador = $adopcion->evaluador->email ?? null;
            if ($emailEvaluador && filter_var($emailEvaluador, FILTER_VALIDATE_EMAIL)) {
                $destinatarios[] = $emailEvaluador;
            }
        }

        return array_unique($destinatarios);
    }
}
