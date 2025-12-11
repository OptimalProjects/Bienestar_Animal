<?php

namespace App\Http\Controllers\Api\V1\Veterinary;

use App\Http\Controllers\Api\V1\BaseController;
use App\Services\VeterinariaService;
use App\Models\Veterinaria\Cirugia;
use App\Models\Veterinaria\Procedimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CirugiaController extends BaseController
{
    public function __construct(
        protected VeterinariaService $veterinariaService
    ) {}

    /**
     * Listar procedimientos disponibles.
     * GET /api/v1/cirugias/procedimientos
     */
    public function procedimientos()
    {
        try {
            $procedimientos = Procedimiento::activos()
                ->orderBy('nombre')
                ->get(['id', 'codigo', 'nombre', 'categoria', 'duracion_estimada']);

            return $this->successResponse($procedimientos);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener procedimientos');
        }
    }

    /**
     * Listar cirugias.
     * GET /api/v1/cirugias
     */
    public function index(Request $request)
    {
        try {
            $query = Cirugia::with(['procedimiento', 'veterinario.usuario', 'historialClinico.animal']);

            if ($request->has('animal_id')) {
                $query->whereHas('historialClinico', function ($q) use ($request) {
                    $q->where('animal_id', $request->animal_id);
                });
            }

            if ($request->has('tipo_cirugia')) {
                $query->where('tipo_cirugia', $request->tipo_cirugia);
            }

            if ($request->has('fecha_desde')) {
                $query->whereDate('fecha_cirugia', '>=', $request->fecha_desde);
            }

            if ($request->has('fecha_hasta')) {
                $query->whereDate('fecha_cirugia', '<=', $request->fecha_hasta);
            }

            $cirugias = $query->orderBy('fecha_cirugia', 'desc')
                ->paginate($request->get('per_page', 15));

            return $this->successResponse($cirugias);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al listar cirugias');
        }
    }

    /**
     * Obtener cirugias de un animal.
     * GET /api/v1/cirugias/animal/{animalId}
     */
    public function cirugiasAnimal(string $animalId)
    {
        try {
            $cirugias = $this->veterinariaService->getCirugiasAnimal($animalId);
            return $this->successResponse($cirugias);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener cirugias del animal');
        }
    }

    /**
     * Registrar cirugia.
     * POST /api/v1/cirugias
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'historial_clinico_id' => 'required|exists:historiales_clinicos,id',
            'procedimiento_id' => 'nullable|exists:procedimientos,id',
            'veterinario_id' => 'required|exists:veterinarios,id',
            'fecha_cirugia' => 'nullable|date',
            'tipo_cirugia' => 'required|in:esterilizacion,castracion,ortopedica,abdominal,oftalmologica,dental,oncologica,emergencia,otra',
            'descripcion' => 'required|string',
            'anestesia_utilizada' => 'nullable|string|max:200',
            'duracion_minutos' => 'nullable|integer|min:1',
            'complicaciones' => 'nullable|string',
            'resultado' => 'nullable|in:exitosa,con_complicaciones,fallida',
            'notas_postoperatorias' => 'nullable|string',
            'estado_animal' => 'nullable|in:en_tratamiento,en_recuperacion,estable',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $cirugia = $this->veterinariaService->registrarCirugia($request->all());
            return $this->createdResponse($cirugia, 'Cirugia registrada exitosamente');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al registrar cirugia: ' . $e->getMessage());
        }
    }

    /**
     * Obtener cirugia especifica.
     * GET /api/v1/cirugias/{id}
     */
    public function show(string $id)
    {
        try {
            $cirugia = Cirugia::with(['procedimiento', 'veterinario.usuario', 'historialClinico.animal'])
                ->findOrFail($id);

            return $this->successResponse($cirugia);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Cirugia no encontrada');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener cirugia');
        }
    }

    /**
     * Actualizar cirugia.
     * PUT /api/v1/cirugias/{id}
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'complicaciones' => 'nullable|string',
            'resultado' => 'nullable|in:exitosa,con_complicaciones,fallida',
            'notas_postoperatorias' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $cirugia = Cirugia::findOrFail($id);
            $cirugia->update($request->only(['complicaciones', 'resultado', 'notas_postoperatorias']));

            return $this->successResponse($cirugia->fresh(), 'Cirugia actualizada exitosamente');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Cirugia no encontrada');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al actualizar cirugia');
        }
    }

    /**
     * Estadisticas de cirugias.
     * GET /api/v1/cirugias/estadisticas
     */
    public function estadisticas()
    {
        try {
            $stats = [
                'total_mes' => Cirugia::where('fecha_cirugia', '>=', now()->startOfMonth())->count(),
                'esterilizaciones_mes' => Cirugia::where('tipo_cirugia', 'esterilizacion')
                    ->where('fecha_cirugia', '>=', now()->startOfMonth())
                    ->count(),
                'por_tipo' => Cirugia::selectRaw('tipo_cirugia, count(*) as cantidad')
                    ->where('fecha_cirugia', '>=', now()->startOfMonth())
                    ->groupBy('tipo_cirugia')
                    ->pluck('cantidad', 'tipo_cirugia'),
                'tasa_exito' => $this->calcularTasaExito(),
            ];

            return $this->successResponse($stats);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener estadisticas');
        }
    }

    /**
     * Calcular tasa de exito de cirugias.
     */
    protected function calcularTasaExito(): float
    {
        $total = Cirugia::where('fecha_cirugia', '>=', now()->subMonths(3))->count();

        if ($total === 0) {
            return 100;
        }

        $exitosas = Cirugia::where('resultado', 'exitosa')
            ->where('fecha_cirugia', '>=', now()->subMonths(3))
            ->count();

        return round(($exitosas / $total) * 100, 1);
    }
}
