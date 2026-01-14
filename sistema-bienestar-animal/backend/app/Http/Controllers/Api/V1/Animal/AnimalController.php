<?php

namespace App\Http\Controllers\Api\V1\Animal;

use App\Http\Controllers\Api\V1\BaseController;
use App\Models\Animal\Animal;
use App\Models\Animal\HistorialClinico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\AnimalService;

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

        // Filtro por especie
        if ($request->has('especie') && $request->especie) {
            $query->where('especie', $request->especie);
        }

        // Filtro por estado
        if ($request->has('estado') && $request->estado) {
            $query->where('estado', $request->estado);
        }

        // Filtro por estado de salud
        if ($request->has('estado_salud') && $request->estado_salud) {
            $query->where('estado_salud', $request->estado_salud);
        }

        // ✅ Filtro por sexo
        if ($request->has('sexo') && $request->sexo) {
            $query->where('sexo', $request->sexo);
        }

        // ✅ Filtro por color
        if ($request->has('color') && $request->color) {
            $query->where('color', 'like', "%{$request->color}%");
        }

        // ✅ Filtro por esterilización
        if ($request->has('esterilizado') && $request->esterilizado) {
            $query->where('esterilizacion', true);
        }

        // ✅ Filtro por rango de fechas
        if ($request->has('fecha_desde') && $request->fecha_desde) {
            $query->whereDate('fecha_rescate', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta') && $request->fecha_hasta) {
            $query->whereDate('fecha_rescate', '<=', $request->fecha_hasta);
        }

        // Búsqueda general (código único, nombre, raza)
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('codigo_unico', 'like', "%{$search}%")
                  ->orWhere('nombre', 'like', "%{$search}%")
                  ->orWhere('raza', 'like', "%{$search}%")
                  ->orWhere('color', 'like', "%{$search}%");
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

    public function __construct(
        protected AnimalService $animalService
    ) {}
    public function store(Request $request)
    {
        // Preprocesar esterilizacion para convertir strings a boolean
        $data = $request->all();
        if (isset($data['esterilizacion'])) {
            $data['esterilizacion'] = filter_var($data['esterilizacion'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        $validator = Validator::make($data, [
            'nombre' => 'nullable|string|max:100',
            'especie' => 'required|string|max:50',
            'raza' => 'nullable|string|max:100',
            'sexo' => 'nullable|in:macho,hembra,desconocido',
            'edad_aproximada' => 'nullable|integer|min:0',
            'peso_actual' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|max:100',
            'tamanio' => 'nullable|in:pequenio,mediano,grande,muy_grande',
            'esterilizacion' => 'nullable|boolean',
            'senias_particulares' => 'nullable|string',
            'foto_principal' => 'nullable|file|image|max:10240',
            'galeria_fotos' => 'nullable|array',
            'galeria_fotos.*' => 'file|image|max:10240',
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
            // Agregar foto principal si existe
            if ($request->hasFile('foto_principal')) {
                $data['foto_principal'] = $request->file('foto_principal');
            }

            // Agregar galería de fotos si existe
            if ($request->hasFile('galeria_fotos')) {
                $data['galeria_fotos'] = $request->file('galeria_fotos');
            }

            $animal = $this->animalService->registrar(
                $data,
                auth()->id()
            );

            return $this->createdResponse($animal, 'Animal registrado exitosamente');
        } catch (\Exception $e) {
            return $this->serverErrorResponse(
                'Error al crear animal: ' . $e->getMessage()
            );
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
     * Obtener animal público para compartir en redes sociales.
     * Este endpoint es público y no requiere autenticación.
     * GET /api/v1/animals/public/{idOrCode}
     */
    public function showPublic(string $idOrCode)
    {
        try {
            // Buscar por ID o por código único
            $animal = Animal::where('id', $idOrCode)
                ->orWhere('codigo_unico', $idOrCode)
                ->with(['historialClinico'])
                ->first();

            if (!$animal) {
                return $this->notFoundResponse('Animal no encontrado');
            }

            // Verificar que el animal esté disponible para adopción (no mostrar datos sensibles)
            $estadosPublicos = ['en_adopcion', 'en_refugio', 'adoptado'];
            if (!in_array($animal->estado, $estadosPublicos)) {
                return $this->notFoundResponse('Este animal no está disponible públicamente');
            }

            // Retornar solo datos públicos relevantes
            $datosPublicos = [
                'id' => $animal->id,
                'codigo_unico' => $animal->codigo_unico,
                'nombre' => $animal->nombre,
                'especie' => $animal->especie,
                'raza' => $animal->raza,
                'sexo' => $animal->sexo,
                'edad_aproximada' => $animal->edad_aproximada,
                'edad_formateada' => $animal->edad_formateada,
                'color' => $animal->color,
                'tamanio' => $animal->tamanio,
                'esterilizado' => $animal->esterilizacion,
                'vacunado' => $animal->historialClinico && $animal->historialClinico->vacunas && count($animal->historialClinico->vacunas) > 0,
                'senias_particulares' => $animal->senias_particulares,
                'observaciones' => $animal->observaciones,
                'estado_adopcion' => $animal->estado === 'adoptado' ? 'Adoptado' : 'Disponible',
                'foto_url' => $animal->foto_url,
                'url_foto_principal' => $animal->url_foto_principal,
                'galeria_urls' => $animal->galeria_urls,
                'galeria_fotos' => $animal->galeria_fotos,
            ];

            return $this->successResponse($datosPublicos, 'Animal obtenido exitosamente');
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
            'esterilizacion' => 'nullable|boolean',
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
     * Catálogo de adopción (público).
     * GET /api/v1/animals/catalogo-adopcion
     */
    public function catalogoAdopcion(Request $request)
    {
        try {
            $query = Animal::disponiblesAdopcion()
                ->saludable()
                ->with(['historialClinico']);

            // Filtro por especie
            if ($request->has('especie') && $request->especie) {
                $query->where('especie', $request->especie);
            }

            // Filtro por tamaño
            if ($request->has('tamanio') && $request->tamanio) {
                $query->where('tamanio', $request->tamanio);
            }

            // Filtro por sexo
            if ($request->has('sexo') && $request->sexo) {
                $query->where('sexo', $request->sexo);
            }

            // Filtro por rango de edad (en meses)
            if ($request->has('edad_min') && $request->edad_min) {
                $query->where('edad_aproximada', '>=', (int)$request->edad_min);
            }
            if ($request->has('edad_max') && $request->edad_max) {
                $query->where('edad_aproximada', '<=', (int)$request->edad_max);
            }

            // Ordenamiento por defecto: más recientes primero
            $query->orderBy('created_at', 'desc');

            // Sin paginación para catálogo público (o paginación grande)
            $perPage = $request->get('per_page', 50);
            $animals = $query->paginate($perPage);

            return $this->successResponse($animals, 'Catálogo de adopción obtenido exitosamente');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener catálogo: ' . $e->getMessage());
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