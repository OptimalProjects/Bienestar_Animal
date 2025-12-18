<?php

namespace App\Http\Controllers\Api\V1\Complaints;

use App\Http\Controllers\Api\V1\BaseController;
use App\Services\DenunciaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DenunciaController extends BaseController
{
    public function __construct(
        protected DenunciaService $denunciaService
    ) {}

    /**
     * Listar denuncias (requiere autenticacion).
     * GET /api/v1/denuncias
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only([
                'estado',
                'prioridad',
                'tipo_denuncia',
                'asignado_a',
                'comuna',
                'fecha_desde',
                'fecha_hasta',
                'busqueda',
                'order_by',
                'order',
            ]);

            $denuncias = $this->denunciaService->listar(
                $request->get('per_page', 15),
                $filters
            );

            return $this->successResponse($denuncias);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al listar denuncias: ' . $e->getMessage());
        }
    }

    /**
     * Registrar nueva denuncia (publico).
     * POST /api/v1/denuncias
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Campos de la denuncia
            'canal_recepcion' => 'required|in:web,telefono,presencial,email,whatsapp',
            'tipo_denuncia' => 'required|in:maltrato,abandono,animal_herido,animal_peligroso,otro',
            'prioridad' => 'required|in:baja,media,alta,urgente',
            'descripcion' => 'required|string|min:20|max:2000',
            'ubicacion' => 'required|string|max:300',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'evidencias' => 'nullable|array',
            'evidencias.*' => 'nullable|string',
            'es_anonima' => 'nullable|boolean',

            // Datos del denunciante (opcional si es anonima)
            'denunciante.nombres' => 'nullable|string|max:100',
            'denunciante.apellidos' => 'nullable|string|max:100',
            'denunciante.telefono' => 'nullable|string|max:20',
            'denunciante.email' => 'nullable|email|max:100',
            'denunciante.direccion' => 'nullable|string|max:300',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $resultado = $this->denunciaService->registrar($request->all());

            return $this->createdResponse([
                'ticket' => $resultado['ticket'],
                'mensaje' => $resultado['mensaje'],
            ], 'Denuncia registrada exitosamente');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al registrar denuncia: ' . $e->getMessage());
        }
    }

    /**
     * Consultar estado de denuncia por ticket (publico).
     * GET /api/v1/denuncias/consultar/{ticket}
     */
    public function consultarTicket(string $ticket)
    {
        try {
            $resultado = $this->denunciaService->consultarPorTicket($ticket);

            if (!$resultado) {
                return $this->notFoundResponse('No se encontro denuncia con ese ticket');
            }

            return $this->successResponse($resultado);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al consultar denuncia');
        }
    }

    /**
     * Obtener denuncia especifica (requiere autenticacion).
     * GET /api/v1/denuncias/{id}
     */
    public function show(string $id)
    {
        try {
            $denuncia = $this->denunciaService->obtener($id);
            return $this->successResponse($denuncia);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Denuncia no encontrada');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener denuncia');
        }
    }

    /**
     * Asignar denuncia a funcionario.
     * PUT /api/v1/denuncias/{id}/asignar
     */
    public function asignar(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'funcionario_id' => 'required|exists:usuarios,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $denuncia = $this->denunciaService->asignar(
                $id,
                $request->funcionario_id,
                auth()->id()
            );

            return $this->successResponse($denuncia, 'Denuncia asignada exitosamente');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al asignar denuncia');
        }
    }

    /**
     * Actualizar estado de denuncia.
     * PUT /api/v1/denuncias/{id}/estado
     */
    public function actualizarEstado(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'estado' => 'required|in:recibida,en_revision,asignada,en_atencion,resuelta,cerrada,desestimada',
            'observaciones_resolucion' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $denuncia = $this->denunciaService->actualizarEstado(
                $id,
                $request->estado,
                $request->only(['observaciones_resolucion'])
            );

            return $this->successResponse($denuncia, 'Estado actualizado exitosamente');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al actualizar estado: ' . $e->getMessage());
        }
    }

    /**
     * Obtener denuncias urgentes sin asignar.
     * GET /api/v1/denuncias/urgentes
     */
    public function urgentes()
    {
        try {
            $denuncias = $this->denunciaService->getUrgentesSinAsignar();
            return $this->successResponse($denuncias);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener denuncias urgentes');
        }
    }

    /**
     * Obtener mis denuncias asignadas.
     * GET /api/v1/denuncias/mis-asignaciones
     */
    public function misAsignaciones()
    {
        try {
            $denuncias = $this->denunciaService->getMisAsignaciones(auth()->id());
            return $this->successResponse($denuncias);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener asignaciones');
        }
    }

    /**
     * Obtener estadisticas de denuncias.
     * GET /api/v1/denuncias/estadisticas
     */
    public function estadisticas()
    {
        try {
            $stats = $this->denunciaService->getEstadisticas();
            return $this->successResponse($stats);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener estadisticas');
        }
    }

    /**
     * Obtener mapa de calor por comuna.
     * GET /api/v1/denuncias/mapa-calor
     */
    public function mapaCalor()
    {
        try {
            $mapa = $this->denunciaService->getMapaCalor();
            return $this->successResponse($mapa);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener mapa de calor');
        }
    }
}
