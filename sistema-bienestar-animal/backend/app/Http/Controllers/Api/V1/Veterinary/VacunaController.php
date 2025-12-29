<?php

namespace App\Http\Controllers\Api\V1\Veterinary;

use App\Http\Controllers\Api\V1\BaseController;
use App\Services\VeterinariaService;
use App\Models\Veterinaria\Vacuna;
use App\Models\Veterinaria\TipoVacuna;
use App\Models\Veterinaria\Veterinario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VacunaController extends BaseController
{
    public function __construct(
        protected VeterinariaService $veterinariaService
    ) {}

    /**
     * Listar tipos de vacunas disponibles.
     * GET /api/v1/vacunas/tipos
     */
    public function tiposVacuna()
    {
        try {
            $tipos = TipoVacuna::activos()
                ->orderBy('nombre')
                ->get(['id', 'codigo', 'nombre', 'especie_aplicable', 'intervalo_dosis']);

            return $this->successResponse($tipos);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener tipos de vacuna: ' . $e->getMessage());
        }
    }

    /**
     * Listar vacunas aplicadas.
     * GET /api/v1/vacunas
     */
    public function index(Request $request)
    {
        try {
            $query = Vacuna::with(['tipoVacuna', 'veterinario.usuario', 'historialClinico.animal']);

            if ($request->has('animal_id')) {
                $query->whereHas('historialClinico', function ($q) use ($request) {
                    $q->where('animal_id', $request->animal_id);
                });
            }

            if ($request->has('fecha_desde')) {
                $query->whereDate('fecha_aplicacion', '>=', $request->fecha_desde);
            }

            if ($request->has('fecha_hasta')) {
                $query->whereDate('fecha_aplicacion', '<=', $request->fecha_hasta);
            }

            $vacunas = $query->orderBy('fecha_aplicacion', 'desc')
                ->paginate($request->get('per_page', 15));

            return $this->successResponse($vacunas);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al listar vacunas');
        }
    }

    /**
     * Obtener vacunas de un animal.
     * GET /api/v1/vacunas/animal/{animalId}
     */
    public function vacunasAnimal(string $animalId)
    {
        try {
            $vacunas = $this->veterinariaService->getVacunasAnimal($animalId);
            return $this->successResponse($vacunas);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener vacunas del animal');
        }
    }

    /**
     * Registrar vacuna.
     * POST /api/v1/vacunas
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'historial_clinico_id' => 'required|exists:historiales_clinicos,id',
            'tipo_vacuna_id' => 'required|exists:tipos_vacunas,id',
            'veterinario_id' => 'required|exists:veterinarios,id',
            'fecha_aplicacion' => 'nullable|date',
            'fecha_proxima' => 'nullable|date|after:fecha_aplicacion',
            'nombre_vacuna' => 'required|string|max:100',
            'lote' => 'required|string|max:100',
            'fabricante' => 'required|string|max:100',
            'fecha_vencimiento' => 'nullable|date|after:today',
            'dosis' => 'required|numeric|min:0.1|max:50',
            'via_administracion' => 'required|in:subcutanea,intramuscular,intranasal,oral,intravenosa',
            'sitio_aplicacion' => 'nullable|string|max:100',
            'numero_dosis' => 'required|in:1,2,3,refuerzo',
            'observaciones' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $vacuna = $this->veterinariaService->registrarVacuna($request->all());
            return $this->createdResponse($vacuna, 'Vacuna registrada exitosamente');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al registrar vacuna: ' . $e->getMessage());
        }
    }

    /**
     * Obtener vacuna especifica.
     * GET /api/v1/vacunas/{id}
     */
    public function show(string $id)
    {
        try {
            $vacuna = Vacuna::with(['tipoVacuna', 'veterinario.usuario', 'historialClinico.animal'])
                ->findOrFail($id);

            return $this->successResponse($vacuna);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Vacuna no encontrada');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener vacuna');
        }
    }

    /**
     * Obtener vacunas proximas a vencer.
     * GET /api/v1/vacunas/proximas
     */
    public function proximasVacunas(Request $request)
    {
        try {
            $dias = $request->get('dias', 30);

            $vacunas = Vacuna::with(['tipoVacuna', 'historialClinico.animal'])
                ->whereNotNull('fecha_proxima')
                ->whereBetween('fecha_proxima', [now(), now()->addDays($dias)])
                ->orderBy('fecha_proxima')
                ->get();

            return $this->successResponse($vacunas);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener vacunas proximas');
        }
    }

    /**
     * Listar veterinarios disponibles.
     * GET /api/v1/vacunas/veterinarios
     */
    public function veterinarios()
    {
        try {
            $veterinarios = Veterinario::activos()
                ->orderBy('nombres')
                ->get(['id', 'nombres', 'apellidos', 'numero_tarjeta_profesional', 'especialidad']);

            return $this->successResponse($veterinarios);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener veterinarios: ' . $e->getMessage());
        }
    }
}
