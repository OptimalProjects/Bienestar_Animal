<?php

namespace App\Http\Controllers\Api\V1\Adoptions;

use App\Http\Controllers\Api\V1\BaseController;
use App\Services\AdopcionService;
use App\Models\Adopcion\VisitaDomiciliaria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VisitaSeguimientoController extends BaseController
{
    public function __construct(
        protected AdopcionService $adopcionService
    ) {}

    /**
     * Listar visitas de seguimiento.
     * GET /api/v1/visitas-seguimiento
     */
    public function index(Request $request)
    {
        try {
            $query = VisitaDomiciliaria::with(['adopcion.animal', 'adopcion.adoptante', 'funcionario']);

            if ($request->has('estado')) {
                $query->where('estado', $request->estado);
            }

            if ($request->has('funcionario_id')) {
                $query->where('funcionario_id', $request->funcionario_id);
            }

            if ($request->has('fecha_desde')) {
                $query->whereDate('fecha_programada', '>=', $request->fecha_desde);
            }

            if ($request->has('fecha_hasta')) {
                $query->whereDate('fecha_programada', '<=', $request->fecha_hasta);
            }

            $visitas = $query->orderBy('fecha_programada', 'asc')
                ->paginate($request->get('per_page', 15));

            return $this->successResponse($visitas);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al listar visitas');
        }
    }

    /**
     * Obtener visitas pendientes.
     * GET /api/v1/visitas-seguimiento/pendientes
     */
    public function pendientes()
    {
        try {
            $visitas = VisitaDomiciliaria::with(['adopcion.animal', 'adopcion.adoptante'])
                ->where('estado', 'programada')
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
            'fecha_programada' => 'required|date|after:today',
            'tipo_visita' => 'nullable|in:seguimiento_inicial,seguimiento,verificacion,emergencia',
            'funcionario_id' => 'nullable|exists:usuarios,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $visita = $this->adopcionService->programarVisitaSeguimiento(
                $request->adopcion_id,
                $request->all()
            );

            return $this->createdResponse($visita, 'Visita programada exitosamente');
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
            $visita = VisitaDomiciliaria::with(['adopcion.animal', 'adopcion.adoptante', 'funcionario'])
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
     * PUT /api/v1/visitas-seguimiento/{id}/registrar
     */
    public function registrar(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'fecha_visita' => 'nullable|date',
            'estado_animal' => 'required|in:excelente,bueno,regular,malo,critico',
            'condiciones_vivienda' => 'required|in:adecuadas,aceptables,inadecuadas',
            'observaciones' => 'nullable|string|max:1000',
            'recomendaciones' => 'nullable|string|max:1000',
            'requiere_seguimiento' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $visita = $this->adopcionService->registrarVisita(
                $id,
                $request->all(),
                auth()->id()
            );

            return $this->successResponse($visita, 'Visita registrada exitosamente');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al registrar visita: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar visita programada.
     * PUT /api/v1/visitas-seguimiento/{id}/cancelar
     */
    public function cancelar(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'motivo_cancelacion' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $visita = VisitaDomiciliaria::findOrFail($id);

            if ($visita->estado !== 'programada') {
                return $this->errorResponse('Solo se pueden cancelar visitas programadas', null, 400);
            }

            $visita->update([
                'estado' => 'cancelada',
                'observaciones' => $request->motivo_cancelacion,
            ]);

            return $this->successResponse($visita, 'Visita cancelada');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al cancelar visita');
        }
    }

    /**
     * Reprogramar visita.
     * PUT /api/v1/visitas-seguimiento/{id}/reprogramar
     */
    public function reprogramar(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'nueva_fecha' => 'required|date|after:today',
            'motivo' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $visita = VisitaDomiciliaria::findOrFail($id);

            if ($visita->estado !== 'programada') {
                return $this->errorResponse('Solo se pueden reprogramar visitas programadas', null, 400);
            }

            $visita->update([
                'fecha_programada' => $request->nueva_fecha,
                'observaciones' => $request->motivo
                    ? "Reprogramada: {$request->motivo}"
                    : $visita->observaciones,
            ]);

            return $this->successResponse($visita->fresh(), 'Visita reprogramada exitosamente');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al reprogramar visita');
        }
    }
}
