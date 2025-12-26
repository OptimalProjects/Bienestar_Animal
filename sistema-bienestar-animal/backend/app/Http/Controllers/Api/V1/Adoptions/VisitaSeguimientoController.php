<?php

namespace App\Http\Controllers\Api\V1\Adoptions;

use App\Http\Controllers\Api\V1\BaseController;
use App\Services\AdopcionService;
use App\Services\ContratoAdopcionService;
use App\Models\Adopcion\VisitaDomiciliaria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class VisitaSeguimientoController extends BaseController
{
    public function __construct(
        protected AdopcionService $adopcionService,
        protected ContratoAdopcionService $contratoService
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

            $visitas = $query->orderBy('fecha_programada', 'asc')
                ->paginate($request->get('per_page', 15));

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
                    $path = $foto->storeAs('visitas/fotos', $fileName, 'public');
                    $fotosRespaldo[] = [
                        'path' => $path,
                        'url' => Storage::disk('public')->url($path),
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
                            ? Storage::disk('public')->url($adopcion->fresh()->contrato_url)
                            : null;

                        $mensaje = 'Visita registrada exitosamente. La adopción ha sido aprobada y el contrato está listo para firma.';

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
                    $mensaje = 'Visita registrada. La solicitud de adopción ha sido rechazada debido al resultado crítico de la visita.';
                }
            }

            $responseData = $visita->fresh(['adopcion.animal', 'adopcion.adoptante', 'visitador']);

            // Añadir información adicional si se aprobó la adopción
            if ($adopcionAprobada) {
                $responseData = [
                    'visita' => $responseData,
                    'adopcion_aprobada' => true,
                    'contrato_url' => $contratoGenerado,
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
            $visitas = VisitaDomiciliaria::with(['visitador'])
                ->where('adopcion_id', $adopcionId)
                ->orderBy('fecha_programada', 'desc')
                ->get();

            return $this->successResponse($visitas);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener visitas de la adopción');
        }
    }
}
