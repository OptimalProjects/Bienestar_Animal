<?php

namespace App\Http\Controllers\Api\V1\Veterinary;

use App\Http\Controllers\Api\V1\BaseController;
use App\Services\VeterinariaService;
use App\Models\Animal\HistorialClinico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HistorialClinicoController extends BaseController
{
    public function __construct(
        protected VeterinariaService $veterinariaService
    ) {}

    /**
     * Obtener historial clinico completo de un animal.
     * GET /api/v1/animals/{animalId}/historial-clinico
     */
    public function show(string $animalId)
    {
        try {
            $historial = $this->veterinariaService->getHistorialCompleto($animalId);
            return $this->successResponse($historial);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Historial clinico no encontrado');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener historial clinico: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar informacion general del historial.
     * PUT /api/v1/animals/{animalId}/historial-clinico
     */
    public function update(Request $request, string $animalId)
    {
        $validator = Validator::make($request->all(), [
            'estado_general' => 'nullable|in:critico,grave,estable,bueno,excelente',
            'alergias' => 'nullable|string',
            'condiciones_preexistentes' => 'nullable|string',
            'observaciones_generales' => 'nullable|string',
            'chip_id' => 'nullable|string|max:50',
            'esterilizado' => 'nullable|boolean',
            'fecha_esterilizacion' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $historial = HistorialClinico::where('animal_id', $animalId)->firstOrFail();

            $historial->update($request->only([
                'estado_general',
                'alergias',
                'condiciones_preexistentes',
                'observaciones_generales',
                'chip_id',
                'esterilizado',
                'fecha_esterilizacion',
            ]));

            return $this->successResponse($historial->fresh(['animal']), 'Historial actualizado exitosamente');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Historial clinico no encontrado');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al actualizar historial clinico');
        }
    }

    /**
     * Agregar chip/microchip al animal.
     * POST /api/v1/animals/{animalId}/chip
     */
    public function registrarChip(Request $request, string $animalId)
    {
        $validator = Validator::make($request->all(), [
            'chip_id' => 'required|string|max:50|unique:historiales_clinicos,chip_id',
            'fecha_implantacion' => 'nullable|date',
            'veterinario_id' => 'nullable|exists:veterinarios,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $historial = HistorialClinico::where('animal_id', $animalId)->firstOrFail();

            if ($historial->chip_id) {
                return $this->errorResponse('El animal ya tiene un chip registrado', null, 400);
            }

            $historial->update([
                'chip_id' => $request->chip_id,
                'fecha_chip' => $request->fecha_implantacion ?? now(),
            ]);

            return $this->successResponse($historial->fresh(['animal']), 'Chip registrado exitosamente');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Historial clinico no encontrado');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al registrar chip');
        }
    }

    /**
     * Buscar animal por chip.
     * GET /api/v1/historial-clinico/buscar-chip/{chip}
     */
    public function buscarPorChip(string $chip)
    {
        try {
            $historial = HistorialClinico::with(['animal', 'consultas', 'vacunas'])
                ->where('chip_id', $chip)
                ->first();

            if (!$historial) {
                return $this->notFoundResponse('No se encontro animal con ese chip');
            }

            return $this->successResponse($historial);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error en la busqueda');
        }
    }
}
