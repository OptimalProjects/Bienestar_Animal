<?php

namespace App\Http\Controllers\Api\V1\Animal;

use App\Http\Controllers\Api\V1\BaseController;
use App\Models\Animal\Animal;
use App\Models\Animal\HistorialClinico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AnimalController extends BaseController
{
    /**
     * Listar animales con filtros.
     * GET /api/v1/animals
     */
    public function index(Request $request)
    {
        try {
            $query = Animal::with(['historialClinico', 'creador:id,nombres,apellidos']);

            // Filtros
            if ($request->has('especie')) {
                $query->where('especie', $request->especie);
            }

            if ($request->has('estado')) {
                $query->where('estado', $request->estado);
            }

            if ($request->has('estado_salud')) {
                $query->where('estado_salud', $request->estado_salud);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('codigo_unico', 'like', "%{$search}%")
                      ->orWhere('nombre', 'like', "%{$search}%")
                      ->orWhere('raza', 'like', "%{$search}%");
                });
            }

            // Ordenamiento
            $query->orderBy($request->get('sort_by', 'created_at'), $request->get('sort_order', 'desc'));

            // Paginación
            $perPage = $request->get('per_page', 15);
            $animals = $query->paginate($perPage);

            return $this->successResponse($animals, 'Animales obtenidos exitosamente');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener animales: ' . $e->getMessage());
        }
    }

    /**
     * Crear nuevo animal.
     * POST /api/v1/animals
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'nullable|string|max:100',
            'especie' => 'required|string|max:50',
            'raza' => 'nullable|string|max:100',
            'sexo' => 'nullable|in:macho,hembra,desconocido',
            'edad_aproximada' => 'nullable|integer|min:0',
            'peso_actual' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|max:100',
            'tamanio' => 'nullable|in:pequenio,mediano,grande,muy_grande',
            'senias_particulares' => 'nullable|string',
            'foto_principal' => 'nullable|string',
            'galeria_fotos' => 'nullable|array',
            'fecha_rescate' => 'nullable|date',
            'ubicacion_rescate' => 'nullable|string',
            'estado' => 'required|in:en_calle,en_refugio,en_adopcion,adoptado,fallecido,en_tratamiento',
            'estado_salud' => 'nullable|in:critico,grave,estable,bueno,excelente',
            'observaciones' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            // Crear animal
            $animal = Animal::create(array_merge(
                $request->all(),
                ['created_by' => auth()->id()]  // Usar null si no hay autenticación
            ));

            // Crear historial clínico automáticamente
            HistorialClinico::create([
                'animal_id' => $animal->id,
                'fecha_apertura' => now(),
                'estado' => 'activo',
            ]);

            DB::commit();

            $animal->load(['historialClinico', 'creador']);

            return $this->createdResponse($animal, 'Animal registrado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse('Error al crear animal: ' . $e->getMessage());
        }
    }

    /**
     * Obtener un animal específico.
     * GET /api/v1/animals/{id}
     */
    public function show(string $id)
    {
        try {
            $animal = Animal::with([
                'historialClinico.consultas' => function ($query) {
                    $query->orderBy('fecha_consulta', 'desc')->limit(5);
                },
                'historialClinico.vacunas' => function ($query) {
                    $query->orderBy('fecha_aplicacion', 'desc');
                },
                'adopciones' => function ($query) {
                    $query->orderBy('fecha_solicitud', 'desc');
                },
                'creador:id,nombres,apellidos'
            ])->findOrFail($id);

            return $this->successResponse($animal, 'Animal obtenido exitosamente');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Animal no encontrado');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener animal: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar animal.
     * PUT /api/v1/animals/{id}
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'nullable|string|max:100',
            'raza' => 'nullable|string|max:100',
            'sexo' => 'nullable|in:macho,hembra,desconocido',
            'edad_aproximada' => 'nullable|integer|min:0',
            'peso_actual' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|max:100',
            'tamanio' => 'nullable|in:pequenio,mediano,grande,muy_grande',
            'senias_particulares' => 'nullable|string',
            'estado' => 'nullable|in:en_calle,en_refugio,en_adopcion,adoptado,fallecido,en_tratamiento',
            'estado_salud' => 'nullable|in:critico,grave,estable,bueno,excelente',
            'observaciones' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $animal = Animal::findOrFail($id);

            $animal->update(array_merge(
                $request->all(),
                ['updated_by' => auth()->id() ?? 1]  // ⚠️ Usar 1 por defecto si no hay autenticación
            ));

            $animal->load(['historialClinico', 'creador']);

            return $this->successResponse($animal, 'Animal actualizado exitosamente');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Animal no encontrado');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al actualizar animal: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar animal (soft delete).
     * DELETE /api/v1/animals/{id}
     */
    public function destroy(string $id)
    {
        try {
            $animal = Animal::findOrFail($id);
            $animal->delete();

            return $this->successResponse(null, 'Animal eliminado exitosamente');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Animal no encontrado');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al eliminar animal: ' . $e->getMessage());
        }
    }

    /**
     * Obtener estadísticas de animales.
     * GET /api/v1/animals/statistics
     */
    public function statistics()
    {
        try {
            $stats = [
                'total' => Animal::count(),
                'por_estado' => [
                    'en_calle' => Animal::where('estado', 'en_calle')->count(),
                    'en_refugio' => Animal::where('estado', 'en_refugio')->count(),
                    'en_adopcion' => Animal::where('estado', 'en_adopcion')->count(),
                    'adoptados' => Animal::where('estado', 'adoptado')->count(),
                ],
                'por_especie' => Animal::select('especie', DB::raw('count(*) as total'))
                    ->groupBy('especie')
                    ->get(),
                'por_estado_salud' => Animal::select('estado_salud', DB::raw('count(*) as total'))
                    ->groupBy('estado_salud')
                    ->get(),
            ];

            return $this->successResponse($stats, 'Estadísticas obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener estadísticas: ' . $e->getMessage());
        }
    }
}