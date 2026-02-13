<?php

namespace App\Http\Controllers\Api\V1\Adoptions;

use App\Http\Controllers\Api\V1\BaseController;
use App\Services\AdopcionService;
use App\Services\ContratoAdopcionService;
use App\Services\DenunciaService;
use App\Services\VisitaSeguimientoService;
use App\Models\Adopcion\VisitaDomiciliaria;
use App\Mail\SolicitudAdopcionMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Services\FileService;

class VisitaSeguimientoController extends BaseController
{
    public function __construct(
        protected AdopcionService $adopcionService,
        protected ContratoAdopcionService $contratoService,
        protected DenunciaService $denunciaService,
        protected VisitaSeguimientoService $visitaSeguimientoService
    ) {}

    /**
     * Listar visitas de seguimiento.
     * GET /api/v1/visitas-seguimiento
     */
    public function index(Request $request)
    {
        try {
            $query = VisitaDomiciliaria::with(['adopcion.animal', 'adopcion.adoptante', 'visitador']);

            // Filtro por estado (pendiente = sin fecha_realizada, realizada = con fecha_realizada)
            if ($request->has('estado')) {
                if ($request->estado === 'pendiente') {
                    $query->whereNull('fecha_realizada');
                } elseif ($request->estado === 'realizada') {
                    $query->whereNotNull('fecha_realizada');
                }
            }

            if ($request->has('visitador_id')) {
                $query->where('visitador_id', $request->visitador_id);
            }

            if ($request->has('tipo_visita')) {
                $query->where('tipo_visita', $request->tipo_visita);
            }

            if ($request->has('fecha_desde')) {
                $query->whereDate('fecha_programada', '>=', $request->fecha_desde);
            }

            if ($request->has('fecha_hasta')) {
                $query->whereDate('fecha_programada', '<=', $request->fecha_hasta);
            }

            if ($request->has('adopcion_id')) {
                $query->where('adopcion_id', $request->adopcion_id);
            }

            $query->orderBy('fecha_programada', 'desc');

            // Soporte para retornar todos sin paginación
            if ($request->get('all') === 'true') {
                $visitas = $query->get();
            } else {
                $visitas = $query->paginate($request->get('per_page', 15));
            }

            return $this->successResponse($visitas);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al listar visitas: ' . $e->getMessage());
        }
    }

    /**
     * Obtener visitas pendientes.
     * GET /api/v1/visitas-seguimiento/pendientes
     */
    public function pendientes()
    {
        try {
            $visitas = VisitaDomiciliaria::with(['adopcion.animal', 'adopcion.adoptante', 'visitador'])
                ->whereNull('fecha_realizada')
                ->orderBy('fecha_programada', 'asc')
                ->get();

            return $this->successResponse($visitas);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener visitas pendientes');
        }
    }

    /**
     * Obtener adopciones que requieren visita.
     * GET /api/v1/visitas-seguimiento/requieren-visita
     */
    public function requierenVisita()
    {
        try {
            $adopciones = $this->adopcionService->getRequierenVisita();
            return $this->successResponse($adopciones);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener adopciones');
        }
    }

    /**
     * Programar nueva visita.
     * POST /api/v1/visitas-seguimiento
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'adopcion_id' => 'required|exists:adopciones,id',
            'fecha_programada' => 'required|date|after_or_equal:today',
            'tipo_visita' => 'required|in:pre_adopcion,seguimiento_1mes,seguimiento_3meses,seguimiento_6meses,extraordinaria',
            'visitador_id' => 'nullable|exists:usuarios,id',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            // Crear la visita directamente
            $visita = VisitaDomiciliaria::create([
                'adopcion_id' => $request->adopcion_id,
                'fecha_programada' => $request->fecha_programada,
                'tipo_visita' => $request->tipo_visita,
                'visitador_id' => $request->visitador_id ?? auth()->id(),
                'observaciones' => $request->observaciones,
            ]);

            return $this->createdResponse(
                $visita->fresh(['adopcion.animal', 'adopcion.adoptante', 'visitador']),
                'Visita programada exitosamente'
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al programar visita: ' . $e->getMessage());
        }
    }

    /**
     * Obtener visita especifica.
     * GET /api/v1/visitas-seguimiento/{id}
     */
    public function show(string $id)
    {
        try {
            $visita = VisitaDomiciliaria::with(['adopcion.animal', 'adopcion.adoptante', 'visitador'])
                ->findOrFail($id);

            return $this->successResponse($visita);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Visita no encontrada');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener visita');
        }
    }

    /**
     * Registrar visita realizada.
     * POST /api/v1/visitas-seguimiento/{id}/registrar
     */
    public function registrar(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'fecha_realizada' => 'nullable|date',
            'resultado' => 'required|in:satisfactoria,observaciones,critica',
            'condiciones_hogar' => 'nullable|array',
            'condiciones_hogar.limpieza' => 'nullable|in:excelente,buena,regular,deficiente',
            'condiciones_hogar.espacio' => 'nullable|in:amplio,adecuado,reducido,inadecuado',
            'condiciones_hogar.seguridad' => 'nullable|in:alta,media,baja',
            'estado_animal' => 'nullable|array',
            'estado_animal.salud' => 'nullable|in:excelente,bueno,regular,malo',
            'estado_animal.comportamiento' => 'nullable|in:excelente,bueno,regular,malo',
            'estado_animal.alimentacion' => 'nullable|in:adecuada,regular,deficiente',
            'observaciones' => 'nullable|string|max:2000',
            'recomendaciones' => 'nullable|string|max:1000',
            'fotos_respaldo' => 'nullable|array|max:5',
            'fotos_respaldo.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120', // 5MB max por foto
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $visita = VisitaDomiciliaria::findOrFail($id);

            if ($visita->fecha_realizada) {
                return $this->errorResponse('Esta visita ya fue registrada', null, 400);
            }

            // Procesar fotos de respaldo
            $fotosRespaldo = [];
            if ($request->hasFile('fotos_respaldo')) {
                foreach ($request->file('fotos_respaldo') as $foto) {
                    $fileName = 'visita_' . $id . '_' . uniqid() . '.' . $foto->getClientOriginalExtension();
                    $path = $foto->storeAs('documentos/visitas/fotos', $fileName, 's3');
                    $fotosRespaldo[] = [
                        'path' => $path,
                        'url' => FileService::privateUrl($path),
                        'nombre_original' => $foto->getClientOriginalName(),
                        'tamanio' => $foto->getSize(),
                        'fecha_subida' => now()->toISOString(),
                    ];
                }
            }

            $visita->update([
                'fecha_realizada' => $request->fecha_realizada ?? now(),
                'resultado' => $request->resultado,
                'condiciones_hogar' => $request->condiciones_hogar,
                'estado_animal' => $request->estado_animal,
                'observaciones' => $request->observaciones,
                'recomendaciones' => $request->recomendaciones,
                'fotos_respaldo' => !empty($fotosRespaldo) ? $fotosRespaldo : null,
            ]);

            $mensaje = 'Visita registrada exitosamente';
            $adopcionAprobada = false;
            $contratoGenerado = null;

            // Si es visita pre_adopcion y el resultado es satisfactoria, aprobar la adopción y generar contrato
            if ($visita->tipo_visita === 'pre_adopcion' && $request->resultado === 'satisfactoria') {
                $adopcion = $visita->adopcion;

                // Solo aprobar si está en estado solicitada o en_evaluacion
                if (in_array($adopcion->estado, ['solicitada', 'en_evaluacion'])) {
                    try {
                        // Aprobar la adopción y generar contrato
                        $this->contratoService->aprobarAdopcion($adopcion, auth()->id());

                        $adopcionAprobada = true;
                        $contratoGenerado = $adopcion->fresh()->contrato_url
                            ? FileService::privateUrl($adopcion->fresh()->contrato_url)
                            : null;

                        $mensaje = 'Visita registrada exitosamente. La adopción ha sido aprobada y el contrato está listo para firma.';

                        // Enviar notificación por correo al adoptante
                        $this->enviarNotificacionAdopcion($adopcion->fresh(['animal', 'adoptante']), 'aprobada');

                        Log::info('Adopción aprobada automáticamente tras visita pre-adopción satisfactoria', [
                            'visita_id' => $visita->id,
                            'adopcion_id' => $adopcion->id,
                            'evaluador_id' => auth()->id(),
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error al aprobar adopción automáticamente', [
                            'visita_id' => $visita->id,
                            'adopcion_id' => $adopcion->id,
                            'error' => $e->getMessage(),
                        ]);
                        // No fallar la operación, la visita ya fue registrada
                        $mensaje = 'Visita registrada exitosamente. Nota: No se pudo aprobar automáticamente la adopción.';
                    }
                }
            }

            // Si es visita pre_adopcion con resultado crítico, rechazar la adopción
            if ($visita->tipo_visita === 'pre_adopcion' && $request->resultado === 'critica') {
                $adopcion = $visita->adopcion;
                if (in_array($adopcion->estado, ['solicitada', 'en_evaluacion'])) {
                    $adopcion->update([
                        'estado' => 'rechazada',
                        'motivo_rechazo' => 'Visita domiciliaria pre-adopción con resultado crítico. ' . ($request->observaciones ?? ''),
                        'evaluador_id' => auth()->id(),
                    ]);

                    // Enviar notificación por correo al adoptante
                    $this->enviarNotificacionAdopcion($adopcion->fresh(['animal', 'adoptante']), 'rechazada');

                    $mensaje = 'Visita registrada. La solicitud de adopción ha sido rechazada debido al resultado crítico de la visita.';
                }
            }

            // Variables para respuesta de rescate
            $rescateIniciado = false;
            $denunciaInfo = null;
            $rescateInfo = null;

            // Si es visita de seguimiento post-adopción con resultado crítico, crear denuncia y rescate automáticamente
            Log::info('Verificando condiciones para rescate automático', [
                'visita_id' => $visita->id,
                'tipo_visita' => $visita->tipo_visita,
                'resultado' => $request->resultado,
                'es_post_adopcion' => $visita->tipo_visita !== 'pre_adopcion',
                'es_critica' => $request->resultado === 'critica',
            ]);

            if ($visita->tipo_visita !== 'pre_adopcion' && $request->resultado === 'critica') {
                $adopcion = $visita->adopcion;
                $adopcion->load(['animal', 'adoptante']);

                try {
                    // Construir descripción para la denuncia
                    $descripcion = $this->construirDescripcionDenuncia($visita, $adopcion->animal, $adopcion->adoptante);

                    // Crear la denuncia automáticamente
                    // Nota: usar 'web' como canal hasta que se ejecute la migración que añade 'sistema'
                    $resultadoDenuncia = $this->denunciaService->registrar([
                        'canal_recepcion' => 'web',
                        'tipo_denuncia' => 'maltrato',
                        'prioridad' => 'urgente',
                        'descripcion' => $descripcion,
                        'ubicacion' => $adopcion->adoptante->direccion ?? 'Dirección del adoptante no registrada',
                        'es_anonima' => false,
                        'denunciante' => [
                            'nombres' => 'Sistema de Seguimiento',
                            'apellidos' => 'Post-Adopción',
                            'email' => 'seguimiento@sistema.gov.co',
                        ],
                    ]);

                    $denuncia = $resultadoDenuncia['denuncia'];

                    // Programar el rescate para hoy
                    $rescate = $this->denunciaService->registrarRescate($denuncia->id, [
                        'fecha_programada' => now()->format('Y-m-d'),
                        'observaciones' => "Rescate automático por visita de seguimiento crítica. Animal: {$adopcion->animal->nombre} ({$adopcion->animal->codigo_unico})",
                        'animal_id' => $adopcion->animal->id,
                    ]);

                    // Mantener la denuncia en estado 'recibida' (el servicio la cambia a 'en_atencion')
                    // Usar query builder para asegurar que se actualice en la BD
                    \App\Models\Denuncia\Denuncia::where('id', $denuncia->id)->update(['estado' => 'recibida']);
                    $denuncia->refresh();

                    // Vincular la denuncia a la visita
                    $visita->update(['denuncia_id' => $denuncia->id]);

                    // Revocar la adopción
                    $adopcion->update([
                        'estado' => 'revocada',
                        'motivo_rechazo' => "Adopción revocada por resultado crítico en visita de seguimiento. Proceso de rescate iniciado (Ticket: {$denuncia->numero_ticket})",
                    ]);

                    // Cambiar estado del animal
                    $adopcion->animal->update(['estado' => 'en_tratamiento']);

                    $rescateIniciado = true;
                    $denunciaInfo = [
                        'id' => $denuncia->id,
                        'ticket' => $denuncia->numero_ticket,
                        'tipo' => $denuncia->tipo_denuncia,
                        'prioridad' => $denuncia->prioridad,
                        'estado' => 'recibida',
                    ];
                    $rescateInfo = [
                        'id' => $rescate->id,
                        'fecha_programada' => $rescate->fecha_programada,
                    ];

                    $mensaje = "Visita registrada con resultado crítico. Se ha iniciado automáticamente un proceso de rescate (Ticket: {$denuncia->numero_ticket}). La adopción ha sido revocada.";

                    Log::info('Rescate iniciado automáticamente por visita crítica', [
                        'visita_id' => $visita->id,
                        'adopcion_id' => $adopcion->id,
                        'denuncia_id' => $denuncia->id,
                        'denuncia_ticket' => $denuncia->numero_ticket,
                        'rescate_id' => $rescate->id,
                        'usuario_id' => auth()->id(),
                    ]);

                } catch (\Exception $e) {
                    Log::error('Error al crear denuncia/rescate automático', [
                        'visita_id' => $visita->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    // No fallar la operación, la visita ya fue registrada
                    $mensaje = 'Visita registrada con resultado crítico. Nota: No se pudo crear el proceso de rescate automáticamente. Por favor, créelo manualmente desde el módulo de denuncias.';
                }
            }

            $responseData = $visita->fresh(['adopcion.animal', 'adopcion.adoptante', 'visitador', 'denuncia']);

            // Enviar notificación de visita realizada al adoptante (solo para seguimientos post-adopción)
            if ($visita->tipo_visita !== 'pre_adopcion' && !$rescateIniciado) {
                $this->visitaSeguimientoService->notificarVisitaRealizada($responseData);
            }

            // Añadir información adicional si se aprobó la adopción
            if ($adopcionAprobada) {
                $responseData = [
                    'visita' => $responseData,
                    'adopcion_aprobada' => true,
                    'contrato_url' => $contratoGenerado,
                ];
            }

            // Añadir información de rescate si se inició
            if ($rescateIniciado) {
                $responseData = [
                    'visita' => is_array($responseData) ? $responseData['visita'] : $responseData,
                    'rescate_iniciado' => true,
                    'adopcion_revocada' => true,
                    'denuncia' => $denunciaInfo,
                    'rescate' => $rescateInfo,
                ];
            }

            return $this->successResponse($responseData, $mensaje);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Visita no encontrada');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al registrar visita: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar visita programada (eliminar).
     * DELETE /api/v1/visitas-seguimiento/{id}
     */
    public function destroy(string $id)
    {
        try {
            $visita = VisitaDomiciliaria::findOrFail($id);

            if ($visita->fecha_realizada) {
                return $this->errorResponse('No se pueden eliminar visitas ya realizadas', null, 400);
            }

            $visita->delete();

            return $this->successResponse(null, 'Visita eliminada exitosamente');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Visita no encontrada');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al eliminar visita');
        }
    }

    /**
     * Reprogramar visita.
     * PUT /api/v1/visitas-seguimiento/{id}/reprogramar
     */
    public function reprogramar(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'fecha_programada' => 'required|date|after_or_equal:today',
            'observaciones' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $visita = VisitaDomiciliaria::findOrFail($id);

            if ($visita->fecha_realizada) {
                return $this->errorResponse('No se pueden reprogramar visitas ya realizadas', null, 400);
            }

            $observacionAnterior = $visita->observaciones;
            $nuevaObservacion = $request->observaciones
                ? "Reprogramada: {$request->observaciones}"
                : ($observacionAnterior ? $observacionAnterior . " | Reprogramada" : "Reprogramada");

            $visita->update([
                'fecha_programada' => $request->fecha_programada,
                'observaciones' => $nuevaObservacion,
            ]);

            return $this->successResponse(
                $visita->fresh(['adopcion.animal', 'adopcion.adoptante', 'visitador']),
                'Visita reprogramada exitosamente'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Visita no encontrada');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al reprogramar visita');
        }
    }

    /**
     * Obtener visitas por adopción.
     * GET /api/v1/visitas-seguimiento/adopcion/{adopcionId}
     */
    public function visitasPorAdopcion(string $adopcionId)
    {
        try {
            $visitas = VisitaDomiciliaria::with(['visitador', 'denuncia'])
                ->where('adopcion_id', $adopcionId)
                ->orderBy('fecha_programada', 'desc')
                ->get();

            return $this->successResponse($visitas);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener visitas de la adopción');
        }
    }

    /**
     * Descargar PDF de resumen de visita.
     * GET /api/v1/visitas-seguimiento/{id}/pdf
     */
    public function descargarPdf(string $id)
    {
        try {
            $visita = VisitaDomiciliaria::with(['adopcion.animal', 'adopcion.adoptante', 'visitador'])
                ->findOrFail($id);

            // Solo permitir descargar PDF de visitas realizadas
            if (!$visita->fecha_realizada) {
                return $this->errorResponse('Solo se puede descargar el PDF de visitas realizadas', null, 400);
            }

            $pdf = $this->visitaSeguimientoService->obtenerPdfResumen($visita);

            $nombreAnimal = $visita->adopcion->animal->nombre ?? $visita->adopcion->animal->codigo_unico;
            $fileName = 'resumen_visita_' . preg_replace('/[^a-zA-Z0-9]/', '_', $nombreAnimal) . '_' . $visita->fecha_realizada->format('Y-m-d') . '.pdf';

            return $pdf->download($fileName);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Visita no encontrada');
        } catch (\Exception $e) {
            Log::error('Error al generar PDF de visita: ' . $e->getMessage());
            return $this->serverErrorResponse('Error al generar PDF de visita');
        }
    }

    /**
     * Ver PDF de resumen de visita en el navegador.
     * GET /api/v1/visitas-seguimiento/{id}/pdf/ver
     */
    public function verPdf(string $id)
    {
        try {
            $visita = VisitaDomiciliaria::with(['adopcion.animal', 'adopcion.adoptante', 'visitador'])
                ->findOrFail($id);

            // Solo permitir ver PDF de visitas realizadas
            if (!$visita->fecha_realizada) {
                return $this->errorResponse('Solo se puede ver el PDF de visitas realizadas', null, 400);
            }

            $pdf = $this->visitaSeguimientoService->obtenerPdfResumen($visita);

            return $pdf->stream('resumen_visita.pdf');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Visita no encontrada');
        } catch (\Exception $e) {
            Log::error('Error al generar PDF de visita: ' . $e->getMessage());
            return $this->serverErrorResponse('Error al generar PDF de visita');
        }
    }

    /**
     * Construir descripción detallada para la denuncia de rescate.
     */
    protected function construirDescripcionDenuncia(
        VisitaDomiciliaria $visita,
        $animal,
        $adoptante
    ): string {
        $partes = [];

        $partes[] = "RESCATE AUTOMÁTICO POR VISITA DE SEGUIMIENTO POST-ADOPCIÓN CON RESULTADO CRÍTICO";
        $partes[] = "";
        $partes[] = "=== DATOS DEL ANIMAL ===";
        $partes[] = "Nombre: " . ($animal->nombre ?? 'Sin nombre');
        $partes[] = "Código: " . $animal->codigo_unico;
        $partes[] = "Especie: " . $animal->especie;
        $partes[] = "Raza: " . ($animal->raza ?? 'Mestizo');

        $partes[] = "";
        $partes[] = "=== DATOS DE LA VISITA ===";
        $partes[] = "Tipo de visita: " . $this->getTipoVisitaTexto($visita->tipo_visita);
        $partes[] = "Fecha de visita: " . ($visita->fecha_realizada?->format('d/m/Y') ?? now()->format('d/m/Y'));

        if ($visita->condiciones_hogar) {
            $partes[] = "";
            $partes[] = "Condiciones del hogar:";
            if (isset($visita->condiciones_hogar['limpieza'])) {
                $partes[] = "- Limpieza: " . $visita->condiciones_hogar['limpieza'];
            }
            if (isset($visita->condiciones_hogar['espacio'])) {
                $partes[] = "- Espacio: " . $visita->condiciones_hogar['espacio'];
            }
            if (isset($visita->condiciones_hogar['seguridad'])) {
                $partes[] = "- Seguridad: " . $visita->condiciones_hogar['seguridad'];
            }
        }

        if ($visita->estado_animal) {
            $partes[] = "";
            $partes[] = "Estado del animal encontrado:";
            if (isset($visita->estado_animal['salud'])) {
                $partes[] = "- Salud: " . $visita->estado_animal['salud'];
            }
            if (isset($visita->estado_animal['comportamiento'])) {
                $partes[] = "- Comportamiento: " . $visita->estado_animal['comportamiento'];
            }
            if (isset($visita->estado_animal['alimentacion'])) {
                $partes[] = "- Alimentación: " . $visita->estado_animal['alimentacion'];
            }
        }

        if ($visita->observaciones) {
            $partes[] = "";
            $partes[] = "=== OBSERVACIONES DE LA VISITA ===";
            $partes[] = $visita->observaciones;
        }

        if ($visita->recomendaciones) {
            $partes[] = "";
            $partes[] = "=== RECOMENDACIONES ===";
            $partes[] = $visita->recomendaciones;
        }

        $partes[] = "";
        $partes[] = "=== UBICACIÓN DEL RESCATE ===";
        $partes[] = "Dirección: " . ($adoptante->direccion ?? 'No registrada');
        $partes[] = "Teléfono contacto: " . ($adoptante->telefono ?? 'No registrado');

        return implode("\n", $partes);
    }

    /**
     * Obtener texto legible del tipo de visita.
     */
    protected function getTipoVisitaTexto(string $tipo): string
    {
        return match($tipo) {
            'pre_adopcion' => 'Pre-adopción',
            'seguimiento_1mes' => 'Seguimiento 1 mes',
            'seguimiento_3meses' => 'Seguimiento 3 meses',
            'seguimiento_6meses' => 'Seguimiento 6 meses',
            'extraordinaria' => 'Extraordinaria',
            default => $tipo,
        };
    }

    /**
     * Enviar notificación de adopción por correo electrónico.
     *
     * @param \App\Models\Adopcion\Adopcion $adopcion
     * @param string $tipo 'aprobada', 'rechazada', 'completada'
     */
    protected function enviarNotificacionAdopcion($adopcion, string $tipo): void
    {
        try {
            $emailAdoptante = $adopcion->adoptante->email ?? null;

            if ($emailAdoptante && filter_var($emailAdoptante, FILTER_VALIDATE_EMAIL)) {
                Mail::to($emailAdoptante)->send(new SolicitudAdopcionMail($adopcion, $tipo));

                Log::info("Notificación de adopción [{$tipo}] enviada desde visita", [
                    'adopcion_id' => $adopcion->id,
                    'animal' => $adopcion->animal->nombre ?? $adopcion->animal->codigo_unico,
                    'destinatario' => $emailAdoptante,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error enviando notificación de adopción [{$tipo}] desde visita", [
                'adopcion_id' => $adopcion->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
