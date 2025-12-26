<?php

namespace App\Http\Controllers\Api\V1\Adoptions;

use App\Http\Controllers\Api\V1\BaseController;
use App\Services\AdopcionService;
use App\Services\ContratoAdopcionService;
use App\Models\Adopcion\Adopcion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AdopcionController extends BaseController
{
    public function __construct(
        protected AdopcionService $adopcionService,
        protected ContratoAdopcionService $contratoService
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

            $adopciones = $this->adopcionService->listar(
                $request->get('per_page', 15),
                $filters
            );

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

            if (!in_array($adopcion->estado, ['aprobada', 'completada'])) {
                return $this->errorResponse('Solo se puede descargar contrato para adopciones aprobadas', null, 400);
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
}
