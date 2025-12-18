<?php

namespace App\Http\Controllers\Api\V1\Complaints;

use App\Http\Controllers\Api\V1\BaseController;
use App\Services\DenunciaService;
use App\Models\Denuncia\Rescate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RescateController extends BaseController
{
    public function __construct(
        protected DenunciaService $denunciaService
    ) {}

    /**
     * Listar rescates.
     * GET /api/v1/rescates
     */
    public function index(Request $request)
    {
        try {
            $query = Rescate::with(['denuncia', 'animalRescatado']);

            if ($request->has('fecha_desde')) {
                $query->whereDate('fecha_programada', '>=', $request->fecha_desde);
            }

            if ($request->has('fecha_hasta')) {
                $query->whereDate('fecha_programada', '<=', $request->fecha_hasta);
            }

            if ($request->has('pendientes')) {
                // Solo rescates sin ejecutar
                $query->whereNull('fecha_ejecucion');
            }

            if ($request->has('ejecutados')) {
                // Solo rescates ejecutados
                $query->whereNotNull('fecha_ejecucion');
            }

            $rescates = $query->orderBy('fecha_programada', 'desc')
                ->paginate($request->get('per_page', 50));

            return $this->successResponse($rescates);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al listar rescates');
        }
    }

    /**
     * Registrar rescate asociado a denuncia (asignacion de equipo).
     * POST /api/v1/rescates
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'denuncia_id' => 'required|exists:denuncias,id',
            'fecha_programada' => 'required|date',
            'equipo_rescate' => 'nullable|array',
            'equipo_rescate.nombre' => 'nullable|string|max:200',
            'equipo_rescate.id' => 'nullable|integer',
            'observaciones' => 'nullable|string|max:1000',
            'animal_id' => 'nullable|exists:animals,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $rescate = $this->denunciaService->registrarRescate(
                $request->denuncia_id,
                $request->all()
            );

            return $this->createdResponse($rescate, 'Equipo de rescate asignado exitosamente');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al asignar equipo de rescate: ' . $e->getMessage());
        }
    }

    /**
     * Obtener rescate especifico.
     * GET /api/v1/rescates/{id}
     */
    public function show(string $id)
    {
        try {
            $rescate = Rescate::with(['denuncia', 'animalRescatado'])
                ->findOrFail($id);

            return $this->successResponse($rescate);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Rescate no encontrado');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener rescate');
        }
    }

    /**
     * Actualizar rescate (registrar resultado).
     * PUT /api/v1/rescates/{id}
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'exitoso' => 'nullable|boolean',
            'fecha_ejecucion' => 'nullable|date',
            'observaciones' => 'nullable|string|max:2000',
            'motivo_fallo' => 'nullable|string|max:1000',
            'animal_id' => 'nullable|exists:animals,id',
            'estado_animal_rescate' => 'nullable|in:critico,grave,estable,bueno',
            'destino' => 'nullable|in:refugio,clinica_veterinaria,hogar_paso,liberado,fallecido',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $rescate = Rescate::findOrFail($id);

            $rescate->update($request->only([
                'exitoso',
                'fecha_ejecucion',
                'observaciones',
                'motivo_fallo',
                'animal_id',
                'estado_animal_rescate',
                'destino',
            ]));

            return $this->successResponse(
                $rescate->fresh(['denuncia', 'animalRescatado']),
                'Resultado del rescate registrado exitosamente'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Rescate no encontrado');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al actualizar rescate: ' . $e->getMessage());
        }
    }

    /**
     * Vincular animal rescatado al sistema.
     * PUT /api/v1/rescates/{id}/vincular-animal
     */
    public function vincularAnimal(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'animal_id' => 'required|exists:animals,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $rescate = Rescate::findOrFail($id);

            $rescate->update([
                'animal_rescatado_id' => $request->animal_id,
            ]);

            return $this->successResponse(
                $rescate->fresh(['animalRescatado']),
                'Animal vinculado exitosamente'
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al vincular animal');
        }
    }

    /**
     * Estadisticas de rescates.
     * GET /api/v1/rescates/estadisticas
     */
    public function estadisticas()
    {
        try {
            $stats = [
                'total_mes' => Rescate::where('created_at', '>=', now()->startOfMonth())->count(),
                'pendientes' => Rescate::whereNull('fecha_ejecucion')->count(),
                'ejecutados_mes' => Rescate::whereNotNull('fecha_ejecucion')
                    ->where('fecha_ejecucion', '>=', now()->startOfMonth())
                    ->count(),
                'exitosos_mes' => Rescate::where('exitoso', true)
                    ->where('fecha_ejecucion', '>=', now()->startOfMonth())
                    ->count(),
                'programados_hoy' => Rescate::whereNull('fecha_ejecucion')
                    ->whereDate('fecha_programada', today())
                    ->count(),
            ];

            return $this->successResponse($stats);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener estadisticas');
        }
    }
}
