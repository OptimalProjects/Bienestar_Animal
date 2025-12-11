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
                $query->whereDate('fecha_rescate', '>=', $request->fecha_desde);
            }

            if ($request->has('fecha_hasta')) {
                $query->whereDate('fecha_rescate', '<=', $request->fecha_hasta);
            }

            if ($request->has('destino')) {
                $query->where('destino', $request->destino);
            }

            $rescates = $query->orderBy('fecha_rescate', 'desc')
                ->paginate($request->get('per_page', 15));

            return $this->successResponse($rescates);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al listar rescates');
        }
    }

    /**
     * Registrar rescate asociado a denuncia.
     * POST /api/v1/rescates
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'denuncia_id' => 'required|exists:denuncias,id',
            'animal_id' => 'nullable|exists:animals,id',
            'fecha_rescate' => 'nullable|date',
            'responsable_rescate' => 'required|string|max:200',
            'descripcion_rescate' => 'nullable|string|max:1000',
            'estado_animal_rescate' => 'required|in:critico,grave,estable,bueno',
            'destino' => 'nullable|in:refugio,clinica_veterinaria,hogar_paso,liberado,fallecido',
            'observaciones' => 'nullable|string|max:1000',
            'cierra_denuncia' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $rescate = $this->denunciaService->registrarRescate(
                $request->denuncia_id,
                $request->all()
            );

            return $this->createdResponse($rescate, 'Rescate registrado exitosamente');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al registrar rescate: ' . $e->getMessage());
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
     * Actualizar rescate.
     * PUT /api/v1/rescates/{id}
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'animal_id' => 'nullable|exists:animals,id',
            'estado_animal_rescate' => 'nullable|in:critico,grave,estable,bueno',
            'destino' => 'nullable|in:refugio,clinica_veterinaria,hogar_paso,liberado,fallecido',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $rescate = Rescate::findOrFail($id);

            $rescate->update($request->only([
                'animal_id',
                'estado_animal_rescate',
                'destino',
                'observaciones',
            ]));

            return $this->successResponse($rescate->fresh(), 'Rescate actualizado exitosamente');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Rescate no encontrado');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al actualizar rescate');
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
                'total_mes' => Rescate::where('fecha_rescate', '>=', now()->startOfMonth())->count(),
                'por_destino' => Rescate::selectRaw('destino, count(*) as cantidad')
                    ->where('fecha_rescate', '>=', now()->startOfMonth())
                    ->groupBy('destino')
                    ->pluck('cantidad', 'destino'),
                'por_estado' => Rescate::selectRaw('estado_animal_rescate, count(*) as cantidad')
                    ->where('fecha_rescate', '>=', now()->startOfMonth())
                    ->groupBy('estado_animal_rescate')
                    ->pluck('cantidad', 'estado_animal_rescate'),
            ];

            return $this->successResponse($stats);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener estadisticas');
        }
    }
}
