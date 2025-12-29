<?php

namespace App\Http\Controllers\Api\V1\Veterinary;

use App\Http\Controllers\Api\V1\BaseController;
use App\Models\Veterinaria\Veterinario;
use Illuminate\Http\Request;

class VeterinarioController extends BaseController
{
    /**
     * Listar veterinarios (para dropdown).
     * GET /api/v1/veterinarios?activos=1
     */
    public function index(Request $request)
    {
        try {
            $query = Veterinario::query();

            if ($request->boolean('activos')) {
                $query->activos();
            }

            $veterinarios = $query->orderBy('nombres')->get([
                'id',
                'nombres',
                'apellidos',
                'numero_tarjeta_profesional',
                'especialidad',
                'telefono',
                'email',
                'activo',
            ]);

            return $this->successResponse($veterinarios);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al listar veterinarios: ' . $e->getMessage());
        }
    }
}
