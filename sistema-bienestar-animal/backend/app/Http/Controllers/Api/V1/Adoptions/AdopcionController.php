<?php

namespace App\Http\Controllers\Api\V1\Adoptions;

use App\Http\Controllers\Api\V1\BaseController;
use App\Services\AdopcionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdopcionController extends BaseController
{
    public function __construct(
        protected AdopcionService $adopcionService
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
            'animal_id' => 'required|exists:animals,id',
            'adoptante.tipo_documento' => 'required|in:CC,CE,TI,PP,NIT',
            'adoptante.documento_identidad' => 'required|string|max:20',
            'adoptante.nombre_completo' => 'required|string|max:200',
            'adoptante.telefono' => 'required|string|max:20',
            'adoptante.email' => 'nullable|email|max:100',
            'adoptante.direccion' => 'required|string|max:300',
            'adoptante.comuna' => 'nullable|string|max:50',
            'adoptante.barrio' => 'nullable|string|max:100',
            'adoptante.ocupacion' => 'nullable|string|max:100',
            'motivo_adopcion' => 'nullable|string|max:1000',
            'experiencia_mascotas' => 'nullable|string|max:500',
            'tiene_otras_mascotas' => 'nullable|boolean',
            'descripcion_hogar' => 'nullable|string|max:500',
            'acepta_visita_seguimiento' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $adopcion = $this->adopcionService->crearSolicitud($request->all());
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
     * Generar contrato de adopcion.
     * GET /api/v1/adopciones/{id}/contrato
     */
    public function contrato(string $id)
    {
        try {
            $contrato = $this->adopcionService->generarContrato($id);
            return $this->successResponse($contrato);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al generar contrato');
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
}
