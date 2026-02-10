<?php

namespace App\Http\Controllers\Api\V1\Adoptions;

use App\Http\Controllers\Api\V1\BaseController;
use App\Services\AdopcionService;
use App\Services\ContratoAdopcionService;
use App\Services\DevolucionService;
use App\Models\Adopcion\Adopcion;
use App\Models\Adopcion\Devolucion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AdopcionController extends BaseController
{
    public function __construct(
        protected AdopcionService $adopcionService,
        protected ContratoAdopcionService $contratoService,
        protected DevolucionService $devolucionService
    ) {}

    /**
     * Listar adopciones.
     * GET /api/v1/adopciones
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only([
                'estado',
                'adoptante_id',
                'animal_id',
                'fecha_desde',
                'fecha_hasta',
                'busqueda',
            ]);

            // Soporte para retornar todos los registros sin paginación
            if ($request->get('all') === 'true') {
                $adopciones = $this->adopcionService->listarTodas($filters);
            } else {
                $adopciones = $this->adopcionService->listar(
                    $request->get('per_page', 15),
                    $filters
                );
            }

            return $this->successResponse($adopciones);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al listar adopciones: ' . $e->getMessage());
        }
    }

    /**
     * Obtener adopciones pendientes de evaluacion.
     * GET /api/v1/adopciones/pendientes
     */
    public function pendientes()
    {
        try {
            $adopciones = $this->adopcionService->getPendientes();
            return $this->successResponse($adopciones);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener adopciones pendientes');
        }
    }

    /**
     * Crear solicitud de adopcion.
     * POST /api/v1/adopciones
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Datos del animal
            'animal_id' => 'required|exists:animals,id',

            // Datos del adoptante (segun tabla adoptantes)
            'adoptante.nombres' => 'required|string|max:100',
            'adoptante.apellidos' => 'required|string|max:100',
            'adoptante.tipo_documento' => 'required|in:CC,CE,TI,PA,PEP',
            'adoptante.numero_documento' => 'required|string|max:20',
            'adoptante.fecha_nacimiento' => 'required|date|before:today',
            'adoptante.telefono' => 'required|string|max:20',
            'adoptante.email' => 'required|email|max:100',
            'adoptante.direccion' => 'required|string|max:300',
            'adoptante.tipo_vivienda' => 'required|in:casa,apartamento,finca,otro',
            'adoptante.tiene_patio' => 'nullable|in:0,1,true,false',
            'adoptante.experiencia_animales' => 'nullable|string|max:1000',
            'adoptante.num_personas_hogar' => 'nullable|integer|min:1|max:20',

            // Documentos adjuntos
            'copia_cedula' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'comprobante_domicilio' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',

            // Datos de la solicitud
            'motivo_adopcion' => 'nullable|string|max:1000',
            'experiencia_mascotas' => 'nullable|string|max:500',
            'tiene_otras_mascotas' => 'nullable|boolean',
            'descripcion_hogar' => 'nullable|string|max:500',
            'acepta_visita_seguimiento' => 'nullable|in:0,1,true,false',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            // Preparar archivos
            $archivos = [];
            if ($request->hasFile('copia_cedula')) {
                $archivos['copia_cedula'] = $request->file('copia_cedula');
            }
            if ($request->hasFile('comprobante_domicilio')) {
                $archivos['comprobante_domicilio'] = $request->file('comprobante_domicilio');
            }

            $adopcion = $this->adopcionService->crearSolicitud($request->all(), $archivos);
            return $this->createdResponse($adopcion, 'Solicitud de adopcion creada exitosamente');
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al crear solicitud: ' . $e->getMessage());
        }
    }

    /**
     * Obtener adopcion especifica.
     * GET /api/v1/adopciones/{id}
     */
    public function show(string $id)
    {
        try {
            $adopcion = $this->adopcionService->obtener($id);
            return $this->successResponse($adopcion);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Adopcion no encontrada');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener adopcion');
        }
    }

    /**
     * Evaluar solicitud de adopcion.
     * PUT /api/v1/adopciones/{id}/evaluar
     */
    public function evaluar(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'aprobada' => 'required|boolean',
            'notas' => 'nullable|string|max:1000',
            'motivo_rechazo' => 'required_if:aprobada,false|nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $adopcion = $this->adopcionService->evaluar(
                $id,
                $request->all(),
                auth()->id()
            );

            $mensaje = $request->aprobada
                ? 'Adopcion aprobada exitosamente'
                : 'Solicitud rechazada';

            return $this->successResponse($adopcion, $mensaje);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al evaluar solicitud');
        }
    }

    /**
     * Obtener/generar contrato de adopcion (PDF).
     * GET /api/v1/adopciones/{id}/contrato
     */
    public function contrato(string $id)
    {
        try {
            $adopcion = Adopcion::with(['animal', 'adoptante', 'evaluador'])->findOrFail($id);

            // Solo para adopciones aprobadas o completadas
            if (!in_array($adopcion->estado, ['aprobada', 'completada'])) {
                return $this->errorResponse('Solo se puede generar contrato para adopciones aprobadas', null, 400);
            }

            // Si ya tiene contrato, retornar la URL existente
            if ($adopcion->contrato_url && Storage::disk('public')->exists($adopcion->contrato_url)) {
                return $this->successResponse([
                    'url' => Storage::disk('public')->url($adopcion->contrato_url),
                    'firmado' => $adopcion->contrato_firmado,
                    'fecha_firma' => $adopcion->fecha_entrega?->toISOString(),
                ]);
            }

            // Generar nuevo contrato
            $contratoInfo = $this->contratoService->generarContrato($adopcion);

            return $this->successResponse([
                'url' => $contratoInfo['url'],
                'firmado' => false,
            ], 'Contrato generado exitosamente');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Adopcion no encontrada');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al generar contrato: ' . $e->getMessage());
        }
    }

    /**
     * Descargar contrato de adopcion como PDF.
     * GET /api/v1/adopciones/{id}/contrato/descargar
     */
    public function descargarContrato(string $id)
    {
        try {
            $adopcion = Adopcion::with(['animal', 'adoptante', 'evaluador'])->findOrFail($id);

            // Permitir descarga si tiene contrato generado o si está en estado que permite contrato
            $estadosConContrato = ['aprobada', 'completada', 'devuelta', 'revocada'];
            if (!$adopcion->contrato_url && !in_array($adopcion->estado, $estadosConContrato)) {
                return $this->errorResponse('Esta adopción no tiene contrato generado', null, 400);
            }

            $pdf = $this->contratoService->obtenerContratoPdf($adopcion);

            $filename = 'contrato_adopcion_' . $adopcion->animal->codigo_unico . '.pdf';

            return $pdf->download($filename);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Adopcion no encontrada');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al descargar contrato: ' . $e->getMessage());
        }
    }

    /**
     * Firmar contrato de adopcion electronicamente.
     * POST /api/v1/adopciones/{id}/contrato/firmar
     */
    public function firmarContrato(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'firma' => 'required|string', // Base64 de la imagen de firma
            'acepta_terminos' => 'required|accepted',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $adopcion = Adopcion::with(['animal', 'adoptante', 'evaluador'])->findOrFail($id);

            if ($adopcion->estado !== 'aprobada') {
                return $this->errorResponse('Solo se pueden firmar contratos de adopciones aprobadas', null, 400);
            }

            if ($adopcion->contrato_firmado) {
                return $this->errorResponse('Este contrato ya fue firmado', null, 400);
            }

            // Registrar la firma
            $adopcionFirmada = $this->contratoService->registrarFirma(
                $adopcion,
                $request->firma,
                $request->ip()
            );

            return $this->successResponse([
                'adopcion' => $adopcionFirmada,
                'contrato_url' => Storage::disk('public')->url($adopcionFirmada->contrato_url),
                'mensaje' => 'Contrato firmado exitosamente. Se han programado los seguimientos post-adopcion.',
            ], 'Contrato firmado exitosamente');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Adopcion no encontrada');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al firmar contrato: ' . $e->getMessage());
        }
    }

    /**
     * Obtener estado del contrato y adopcion.
     * GET /api/v1/adopciones/{id}/estado-contrato
     */
    public function estadoContrato(string $id)
    {
        try {
            $adopcion = Adopcion::with(['animal', 'adoptante', 'evaluador', 'visitasDomiciliarias'])
                ->findOrFail($id);

            $response = [
                'estado_adopcion' => $adopcion->estado,
                'contrato_generado' => !empty($adopcion->contrato_url),
                'contrato_firmado' => $adopcion->contrato_firmado,
                'fecha_aprobacion' => $adopcion->fecha_aprobacion?->toISOString(),
                'fecha_firma' => $adopcion->fecha_entrega?->toISOString(),
            ];

            if ($adopcion->contrato_url && Storage::disk('public')->exists($adopcion->contrato_url)) {
                $response['contrato_url'] = Storage::disk('public')->url($adopcion->contrato_url);
            }

            // Incluir seguimientos programados
            $response['seguimientos'] = $adopcion->visitasDomiciliarias
                ->where('tipo_visita', '!=', 'pre_adopcion')
                ->map(function ($visita) {
                    return [
                        'id' => $visita->id,
                        'tipo' => $visita->tipo_visita,
                        'fecha_programada' => $visita->fecha_programada->toISOString(),
                        'realizada' => !is_null($visita->fecha_realizada),
                        'resultado' => $visita->resultado,
                    ];
                })->values();

            return $this->successResponse($response);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Adopcion no encontrada');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener estado del contrato');
        }
    }

    /**
     * Obtener estadisticas de adopciones.
     * GET /api/v1/adopciones/estadisticas
     */
    public function estadisticas()
    {
        try {
            $stats = $this->adopcionService->getEstadisticas();
            return $this->successResponse($stats);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener estadisticas');
        }
    }

    /**
     * Consulta publica del estado de adopcion (sin autenticacion).
     * GET /api/v1/adopciones/consulta-publica
     */
    public function consultaPublica(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo_documento' => 'required|in:CC,CE,TI,PA,PEP',
            'numero_documento' => 'required|string|max:20',
            'codigo_adopcion' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            // Buscar adoptante por documento
            $adoptante = \App\Models\Adopcion\Adoptante::where('tipo_documento', $request->tipo_documento)
                ->where('numero_documento', $request->numero_documento)
                ->first();

            if (!$adoptante) {
                return $this->notFoundResponse('No se encontraron solicitudes de adopcion con los datos proporcionados');
            }

            // Buscar adopciones del adoptante
            $query = Adopcion::with(['animal', 'evaluador', 'visitasDomiciliarias'])
                ->where('adoptante_id', $adoptante->id);

            // Si se proporciona codigo de adopcion, filtrar
            if ($request->codigo_adopcion) {
                $query->where('id', $request->codigo_adopcion);
            }

            $adopciones = $query->orderBy('created_at', 'desc')->get();

            if ($adopciones->isEmpty()) {
                return $this->notFoundResponse('No se encontraron solicitudes de adopcion');
            }

            // Formatear respuesta con datos publicos
            $resultado = $adopciones->map(function ($adopcion) use ($adoptante) {
                $response = [
                    'id' => $adopcion->id,
                    'estado' => $adopcion->estado,
                    'fecha_solicitud' => $adopcion->created_at->toISOString(),
                    'fecha_aprobacion' => $adopcion->fecha_aprobacion?->toISOString(),
                    'fecha_entrega' => $adopcion->fecha_entrega?->toISOString(),
                    'motivo_rechazo' => $adopcion->motivo_rechazo,
                    'contrato_firmado' => $adopcion->contrato_firmado,
                    'contrato_url' => null,

                    // Datos del adoptante
                    'adoptante' => [
                        'nombre_completo' => $adoptante->nombre_completo,
                        'tipo_documento' => $adoptante->tipo_documento,
                        'numero_documento' => $adoptante->numero_documento,
                        'direccion' => $adoptante->direccion,
                        'telefono' => $adoptante->telefono,
                        'email' => $adoptante->email,
                        'tipo_vivienda' => $adoptante->tipo_vivienda,
                    ],

                    // Datos del animal
                    'animal' => $adopcion->animal ? [
                        'id' => $adopcion->animal->id,
                        'codigo' => $adopcion->animal->codigo_unico,
                        'nombre' => $adopcion->animal->nombre,
                        'especie' => $adopcion->animal->especie,
                        'raza' => $adopcion->animal->raza,
                        'sexo' => $adopcion->animal->sexo,
                        'edad' => $adopcion->animal->edad_formateada,
                        'tamanio' => $adopcion->animal->tamanio,
                        'foto_url' => $adopcion->animal->foto_url,
                    ] : null,

                    // Evaluador (solo nombre)
                    'evaluador' => $adopcion->evaluador ? [
                        'nombre' => $adopcion->evaluador->nombre_completo ??
                            ($adopcion->evaluador->nombres . ' ' . $adopcion->evaluador->apellidos),
                    ] : null,

                    // Seguimientos
                    'seguimientos' => $adopcion->visitasDomiciliarias->map(function ($visita) {
                        return [
                            'id' => $visita->id,
                            'tipo' => $visita->tipo_visita,
                            'fecha_programada' => $visita->fecha_programada->toISOString(),
                            'fecha_realizada' => $visita->fecha_realizada?->toISOString(),
                            'resultado' => $visita->resultado,
                            'observaciones' => $visita->observaciones,
                        ];
                    })->values(),
                ];

                // URL del contrato si existe y esta aprobada/completada
                if (in_array($adopcion->estado, ['aprobada', 'completada']) &&
                    $adopcion->contrato_url &&
                    Storage::disk('public')->exists($adopcion->contrato_url)) {
                    $response['contrato_url'] = Storage::disk('public')->url($adopcion->contrato_url);
                }

                return $response;
            });

            return $this->successResponse($resultado);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al consultar estado de adopcion: ' . $e->getMessage());
        }
    }

    /**
     * Firmar contrato publicamente (sin autenticacion, validando documento).
     * POST /api/v1/adopciones/{id}/contrato/firmar-publico
     */
    public function firmarContratoPublico(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'tipo_documento' => 'required|in:CC,CE,TI,PA,PEP',
            'numero_documento' => 'required|string|max:20',
            'firma' => 'required|string', // Base64 de la imagen de firma
            'acepta_terminos' => 'required|accepted',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $adopcion = Adopcion::with(['animal', 'adoptante', 'evaluador'])->findOrFail($id);

            // Verificar que el documento coincida con el adoptante
            if ($adopcion->adoptante->tipo_documento !== $request->tipo_documento ||
                $adopcion->adoptante->numero_documento !== $request->numero_documento) {
                return $this->errorResponse('Los datos de identificacion no coinciden con el adoptante de esta solicitud', null, 403);
            }

            if ($adopcion->estado !== 'aprobada') {
                return $this->errorResponse('Solo se pueden firmar contratos de adopciones aprobadas', null, 400);
            }

            if ($adopcion->contrato_firmado) {
                return $this->errorResponse('Este contrato ya fue firmado', null, 400);
            }

            // Registrar la firma
            $adopcionFirmada = $this->contratoService->registrarFirma(
                $adopcion,
                $request->firma,
                $request->ip()
            );

            return $this->successResponse([
                'adopcion_id' => $adopcionFirmada->id,
                'estado' => $adopcionFirmada->estado,
                'contrato_firmado' => true,
                'contrato_url' => Storage::disk('public')->url($adopcionFirmada->contrato_url),
                'fecha_firma' => $adopcionFirmada->fecha_entrega?->toISOString(),
                'mensaje' => 'Contrato firmado exitosamente. Se han programado los seguimientos post-adopcion.',
            ], 'Contrato firmado exitosamente');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Adopcion no encontrada');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al firmar contrato: ' . $e->getMessage());
        }
    }

    // ============================================
    // CONTRATOS
    // ============================================

    /**
     * Listar adopciones con contrato generado.
     * GET /api/v1/adopciones/contratos
     */
    public function listarContratos(Request $request)
    {
        try {
            $query = Adopcion::with(['adoptante', 'animal'])
                ->whereNotNull('contrato_url')
                ->orderBy('fecha_aprobacion', 'desc');

            // Filtrar por estado del contrato
            if ($request->has('firmado')) {
                $firmado = filter_var($request->firmado, FILTER_VALIDATE_BOOLEAN);
                $query->where('contrato_firmado', $firmado);
            }

            // Búsqueda
            if ($request->has('busqueda') && !empty($request->busqueda)) {
                $busqueda = $request->busqueda;
                $query->where(function ($q) use ($busqueda) {
                    $q->whereHas('adoptante', function ($sub) use ($busqueda) {
                        $sub->where('nombres', 'like', "%{$busqueda}%")
                            ->orWhere('apellidos', 'like', "%{$busqueda}%")
                            ->orWhere('numero_documento', 'like', "%{$busqueda}%");
                    })
                    ->orWhereHas('animal', function ($sub) use ($busqueda) {
                        $sub->where('nombre', 'like', "%{$busqueda}%")
                            ->orWhere('codigo_unico', 'like', "%{$busqueda}%");
                    });
                });
            }

            // Función de transformación
            $transformar = function ($adopcion) {
                return [
                    'id' => $adopcion->id,
                    'numero_contrato' => $this->generarNumeroContrato($adopcion),
                    'estado_adopcion' => $adopcion->estado,
                    'contrato_firmado' => $adopcion->contrato_firmado ?? false,
                    'fecha_generacion' => $adopcion->fecha_aprobacion?->toISOString(),
                    'fecha_firma' => $adopcion->fecha_entrega?->toISOString(),
                    'adoptante' => [
                        'id' => $adopcion->adoptante?->id,
                        'nombre_completo' => $adopcion->adoptante?->nombre_completo ??
                            trim(($adopcion->adoptante?->nombres ?? '') . ' ' . ($adopcion->adoptante?->apellidos ?? '')),
                        'numero_documento' => $adopcion->adoptante?->numero_documento,
                        'tipo_documento' => $adopcion->adoptante?->tipo_documento,
                        'telefono' => $adopcion->adoptante?->telefono,
                        'email' => $adopcion->adoptante?->email,
                    ],
                    'animal' => [
                        'id' => $adopcion->animal?->id,
                        'codigo_unico' => $adopcion->animal?->codigo_unico,
                        'nombre' => $adopcion->animal?->nombre,
                        'especie' => $adopcion->animal?->especie,
                        'raza' => $adopcion->animal?->raza,
                    ],
                ];
            };

            // Soporte para retornar todos sin paginación
            if ($request->get('all') === 'true') {
                $contratos = $query->get()->map($transformar)->values();
                return $this->successResponse($contratos);
            }

            $contratos = $query->paginate($request->get('per_page', 20));
            $contratos->getCollection()->transform($transformar);

            return $this->successResponse($contratos);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al listar contratos: ' . $e->getMessage());
        }
    }

    /**
     * Generar número de contrato basado en la adopción.
     */
    private function generarNumeroContrato(Adopcion $adopcion): string
    {
        $year = $adopcion->fecha_aprobacion?->year ?? now()->year;
        $id = strtoupper(substr($adopcion->id, 0, 8));
        return "CONT-{$year}-{$id}";
    }

    // ============================================
    // DEVOLUCIONES
    // ============================================

    /**
     * Listar devoluciones.
     * GET /api/v1/adopciones/devoluciones
     */
    public function listarDevoluciones(Request $request)
    {
        try {
            $filters = $request->only([
                'motivo',
                'estado_proceso',
                'fecha_desde',
                'fecha_hasta',
                'pendientes_revision',
            ]);

            $devoluciones = $this->devolucionService->listar(
                $request->get('per_page', 15),
                $filters
            );

            return $this->successResponse($devoluciones);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al listar devoluciones: ' . $e->getMessage());
        }
    }

    /**
     * Obtener motivos de devolucion disponibles.
     * GET /api/v1/adopciones/devoluciones/motivos
     */
    public function motivosDevolucion()
    {
        return $this->successResponse(Devolucion::MOTIVOS);
    }

    /**
     * Obtener estadisticas de devoluciones.
     * GET /api/v1/adopciones/devoluciones/estadisticas
     */
    public function estadisticasDevoluciones()
    {
        try {
            $stats = $this->devolucionService->getEstadisticas();
            return $this->successResponse($stats);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener estadisticas de devoluciones');
        }
    }

    /**
     * Registrar devolucion de animal adoptado.
     * POST /api/v1/adopciones/{id}/devolucion
     */
    public function registrarDevolucion(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'motivo' => 'required|in:incompatibilidad,cambio_situacion,problemas_comportamiento,enfermedad_animal,enfermedad_adoptante,mudanza,economico,fallecimiento_adoptante,alergias,otro',
            'descripcion_motivo' => 'required|string|min:10|max:2000',
            'estado_animal_devolucion' => 'required|in:bueno,regular,malo,critico',
            'observaciones_estado' => 'nullable|string|max:1000',
            'fecha_devolucion' => 'nullable|date|before_or_equal:today',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $data = $request->all();
            $data['adopcion_id'] = $id;

            $resultado = $this->devolucionService->registrarDevolucion($data, auth()->id());

            // Notificar al adoptante por correo con PDF adjunto
            if (isset($resultado['devolucion'])) {
                $this->devolucionService->notificarDevolucionRegistrada($resultado['devolucion']);
            }

            return $this->createdResponse($resultado, $resultado['mensaje']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Adopcion no encontrada');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al registrar devolucion: ' . $e->getMessage());
        }
    }

    /**
     * Descargar PDF de resumen de devolución.
     * GET /api/v1/adopciones/devoluciones/{devolucionId}/pdf
     */
    public function descargarPdfDevolucion(string $devolucionId)
    {
        try {
            $devolucion = $this->devolucionService->obtener($devolucionId);
            $pdf = $this->devolucionService->obtenerPdfResumen($devolucion);

            $animalNombre = $devolucion->animal->nombre ?? $devolucion->animal->codigo_unico;
            $nombreLimpio = preg_replace('/[^a-zA-Z0-9_-]/', '_', $animalNombre);
            $fileName = "Resumen_Devolucion_{$nombreLimpio}.pdf";

            return $pdf->download($fileName);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Devolución no encontrada');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al generar PDF: ' . $e->getMessage());
        }
    }

    /**
     * Ver PDF de resumen de devolución en el navegador.
     * GET /api/v1/adopciones/devoluciones/{devolucionId}/pdf/ver
     */
    public function verPdfDevolucion(string $devolucionId)
    {
        try {
            $devolucion = $this->devolucionService->obtener($devolucionId);
            $pdf = $this->devolucionService->obtenerPdfResumen($devolucion);

            return $pdf->stream('resumen_devolucion.pdf');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Devolución no encontrada');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al generar PDF: ' . $e->getMessage());
        }
    }

    /**
     * Obtener detalle de una devolucion.
     * GET /api/v1/adopciones/devoluciones/{devolucionId}
     */
    public function obtenerDevolucion(string $devolucionId)
    {
        try {
            $devolucion = $this->devolucionService->obtener($devolucionId);
            return $this->successResponse($devolucion);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Devolucion no encontrada');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener devolucion');
        }
    }

    /**
     * Completar revision veterinaria de devolucion.
     * PUT /api/v1/adopciones/devoluciones/{devolucionId}/revision
     */
    public function completarRevisionDevolucion(Request $request, string $devolucionId)
    {
        $validator = Validator::make($request->all(), [
            'diagnostico' => 'required|string|max:2000',
            'observaciones_veterinario' => 'nullable|string|max:1000',
            'recomendaciones' => 'nullable|string|max:1000',
            'apto_adopcion' => 'required|boolean',
            'estado_salud' => 'required|in:critico,grave,estable,bueno,excelente',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $devolucion = $this->devolucionService->completarRevision(
                $devolucionId,
                $request->all(),
                auth()->id()
            );

            $mensaje = $request->apto_adopcion
                ? 'Revision completada. El animal ha sido aprobado para re-adopcion.'
                : 'Revision completada. El animal requiere tratamiento antes de re-adopcion.';

            return $this->successResponse($devolucion, $mensaje);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Devolucion no encontrada');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al completar revision: ' . $e->getMessage());
        }
    }
}
