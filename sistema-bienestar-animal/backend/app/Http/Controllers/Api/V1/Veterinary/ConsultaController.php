<?php

namespace App\Http\Controllers\Api\V1\Veterinary;

use App\Http\Controllers\Api\V1\BaseController;
use App\Services\VeterinariaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConsultaController extends BaseController
{
    public function __construct(
        protected VeterinariaService $veterinariaService
    ) {}

    /**
     * Listar consultas.
     * GET /api/v1/consultas
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only([
                'veterinario_id',
                'tipo_consulta',
                'estado',
                'fecha_desde',
                'fecha_hasta',
                'animal_id',
            ]);

            $consultas = $this->veterinariaService->listarConsultas(
                $request->get('per_page', 15),
                $filters
            );

            return $this->successResponse($consultas);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al listar consultas: ' . $e->getMessage());
        }
    }

    /**
     * Obtener consultas del dia.
     * GET /api/v1/consultas/hoy
     */
    public function consultasHoy()
    {
        try {
            $consultas = $this->veterinariaService->getConsultasDelDia();
            return $this->successResponse($consultas);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener consultas del dia');
        }
    }

    /**
     * Obtener consultas pendientes.
     * GET /api/v1/consultas/pendientes
     */
    public function pendientes()
    {
        try {
            $consultas = $this->veterinariaService->getConsultasPendientes();
            return $this->successResponse($consultas);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener consultas pendientes');
        }
    }

    /**
     * Registrar nueva consulta.
     * POST /api/v1/consultas
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'historial_clinico_id' => 'required|exists:historiales_clinicos,id',
            'veterinario_id' => 'required|exists:veterinarios,id',
            'tipo_consulta' => 'required|in:general,emergencia,seguimiento,especializada',
            'motivo_consulta' => 'required|string|max:500',
            'sintomas' => 'nullable|string',
            'diagnostico' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'peso' => 'nullable|numeric|min:0',
            'temperatura' => 'nullable|numeric|min:30|max:45',
            'frecuencia_cardiaca' => 'nullable|integer|min:0',
            'frecuencia_respiratoria' => 'nullable|integer|min:0',
            'estado_salud' => 'nullable|string',
            'tratamientos' => 'nullable|array',
            'tratamientos.*.descripcion' => 'required_with:tratamientos|string',
            'tratamientos.*.medicamento_id' => 'nullable|exists:medicamentos,id',
            'tratamientos.*.dosis' => 'nullable|string',
            'tratamientos.*.frecuencia' => 'nullable|string',
            'tratamientos.*.duracion_dias' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $consulta = $this->veterinariaService->registrarConsulta($request->all());
            return $this->createdResponse($consulta, 'Consulta registrada exitosamente');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al registrar consulta: ' . $e->getMessage());
        }
    }

    /**
     * Obtener consulta especifica.
     * GET /api/v1/consultas/{id}
     */
    public function show(string $id)
    {
        try {
            $consulta = $this->veterinariaService->obtenerConsulta($id);
            return $this->successResponse($consulta);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Consulta no encontrada');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener consulta');
        }
    }

    /**
     * Obtener estadisticas de veterinaria.
     * GET /api/v1/consultas/estadisticas
     */
    public function estadisticas()
    {
        try {
            $stats = $this->veterinariaService->getEstadisticas();
            return $this->successResponse($stats);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener estadisticas');
        }
    }
}
