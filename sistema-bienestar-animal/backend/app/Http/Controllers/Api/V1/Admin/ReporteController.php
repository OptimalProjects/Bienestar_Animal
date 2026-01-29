<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\BaseController;
use App\Services\ReporteService;
use App\Exports\AnimalesExport;
use App\Exports\AdopcionesExport;
use App\Exports\VacunasExport;
use App\Exports\CirugiasExport;
use App\Exports\DenunciasExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ReporteController extends BaseController
{
    public function __construct(
        protected ReporteService $reporteService
    ) {}

    /**
     * Obtener dashboard principal.
     * GET /api/v1/reportes/dashboard
     */
    public function dashboard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $data = $this->reporteService->getDashboard(
                $request->fecha_inicio,
                $request->fecha_fin
            );
            return $this->successResponse($data);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener dashboard: ' . $e->getMessage());
        }
    }

    /**
     * Obtener indicadores KPI.
     * GET /api/v1/reportes/indicadores
     */
    public function indicadores()
    {
        try {
            $indicadores = $this->reporteService->getIndicadores();
            return $this->successResponse($indicadores);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener indicadores');
        }
    }

    /**
     * Registrar valor de indicador.
     * POST /api/v1/reportes/indicadores/{indicadorId}/punto
     */
    public function registrarIndicador(Request $request, string $indicadorId)
    {
        $validator = Validator::make($request->all(), [
            'valor' => 'required|numeric',
            'dimensiones' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $punto = $this->reporteService->registrarIndicador(
                $indicadorId,
                $request->valor,
                $request->dimensiones
            );

            return $this->createdResponse($punto, 'Indicador registrado exitosamente');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al registrar indicador');
        }
    }

    /**
     * Generar reporte de periodo.
     * GET /api/v1/reportes/periodo
     */
    public function reportePeriodo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $reporte = $this->reporteService->generarReportePeriodo(
                $request->fecha_inicio,
                $request->fecha_fin
            );

            return $this->successResponse($reporte);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al generar reporte');
        }
    }

    /**
     * Exportar reporte en formato especifico.
     * GET /api/v1/reportes/exportar/{tipo}
     */
    public function exportar(Request $request, string $tipo)
    {
        $validator = Validator::make(array_merge($request->all(), ['tipo' => $tipo]), [
            'tipo' => 'required|in:animales,adopciones,denuncias,vacunas,cirugias,veterinaria,general',
            'formato' => 'nullable|in:xlsx,csv',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $fechaInicio = $request->fecha_inicio ?? now()->subMonth()->toDateString();
            $fechaFin = $request->fecha_fin ?? now()->toDateString();
            $formato = $request->formato ?? 'xlsx';
            $filters = $request->only(['estado', 'especie', 'prioridad', 'tipo_vacuna_id', 'tipo_cirugia']);

            // Generar nombre de archivo
            $fechaArchivo = Carbon::now()->format('Y-m-d_His');
            $nombreArchivo = "reporte_{$tipo}_{$fechaArchivo}";

            // Seleccionar la clase de exportacion adecuada
            $export = match($tipo) {
                'animales' => new AnimalesExport($fechaInicio, $fechaFin, $filters),
                'adopciones' => new AdopcionesExport($fechaInicio, $fechaFin, $filters),
                'vacunas' => new VacunasExport($fechaInicio, $fechaFin, $filters),
                'cirugias' => new CirugiasExport($fechaInicio, $fechaFin, $filters),
                'denuncias' => new DenunciasExport($fechaInicio, $fechaFin, $filters),
                default => throw new \InvalidArgumentException("Tipo de reporte no soportado: {$tipo}")
            };

            // Determinar extension y tipo MIME
            $extension = $formato === 'csv' ? 'csv' : 'xlsx';
            $writerType = $formato === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;

            return Excel::download($export, "{$nombreArchivo}.{$extension}", $writerType);

        } catch (\InvalidArgumentException $e) {
            return $this->validationErrorResponse(['tipo' => [$e->getMessage()]]);
        } catch (\Exception $e) {
            \Log::error('Error al exportar reporte: ' . $e->getMessage(), [
                'tipo' => $tipo,
                'trace' => $e->getTraceAsString()
            ]);
            return $this->serverErrorResponse('Error al exportar reporte: ' . $e->getMessage());
        }
    }

    /**
     * Obtener tipos de reportes disponibles.
     * GET /api/v1/reportes/tipos
     */
    public function tiposReporte()
    {
        return $this->successResponse([
            [
                'id' => 'animales',
                'nombre' => 'Animales Registrados',
                'descripcion' => 'Listado de animales registrados en el sistema',
                'color' => '#FFAB00',
            ],
            [
                'id' => 'adopciones',
                'nombre' => 'Adopciones',
                'descripcion' => 'Solicitudes y procesos de adopcion',
                'color' => '#068460',
            ],
            [
                'id' => 'vacunas',
                'nombre' => 'Vacunacion',
                'descripcion' => 'Registro de vacunas aplicadas',
                'color' => '#004884',
            ],
            [
                'id' => 'cirugias',
                'nombre' => 'Cirugias',
                'descripcion' => 'Procedimientos quirurgicos realizados',
                'color' => '#3366CC',
            ],
            [
                'id' => 'denuncias',
                'nombre' => 'Denuncias',
                'descripcion' => 'Casos de maltrato animal reportados',
                'color' => '#A80521',
            ],
        ]);
    }

    /**
     * Vista previa de datos del reporte (paginado).
     * GET /api/v1/reportes/preview/{tipo}
     */
    public function preview(Request $request, string $tipo)
    {
        $validator = Validator::make(array_merge($request->all(), ['tipo' => $tipo]), [
            'tipo' => 'required|in:animales,adopciones,denuncias,vacunas,cirugias',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'per_page' => 'nullable|integer|min:5|max:50',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $fechaInicio = $request->fecha_inicio ? Carbon::parse($request->fecha_inicio)->startOfDay() : now()->subMonth()->startOfDay();
            $fechaFin = $request->fecha_fin ? Carbon::parse($request->fecha_fin)->endOfDay() : now()->endOfDay();
            $perPage = $request->per_page ?? 10;
            $filters = $request->only(['estado', 'especie', 'prioridad', 'tipo_vacuna_id', 'tipo_cirugia']);

            $data = $this->getPreviewData($tipo, $fechaInicio, $fechaFin, $perPage, $filters);

            return $this->successResponse($data);

        } catch (\Exception $e) {
            \Log::error('Error al generar preview: ' . $e->getMessage());
            return $this->serverErrorResponse('Error al generar vista previa');
        }
    }

    /**
     * Obtener datos de vista previa segun tipo.
     */
    private function getPreviewData(string $tipo, $fechaInicio, $fechaFin, int $perPage, array $filters)
    {
        switch ($tipo) {
            case 'animales':
                $query = \App\Models\Animal\Animal::query()
                    ->whereBetween('created_at', [$fechaInicio, $fechaFin]);
                if (!empty($filters['especie'])) {
                    $especie = strtolower($filters['especie']);
                    $especieSinonimos = [
                        'canino' => ['canino', 'perro'],
                        'perro' => ['canino', 'perro'],
                        'felino' => ['felino', 'gato'],
                        'gato' => ['felino', 'gato'],
                        'equino' => ['equino', 'caballo'],
                        'caballo' => ['equino', 'caballo'],
                    ];
                    if (isset($especieSinonimos[$especie])) {
                        $query->whereIn('especie', $especieSinonimos[$especie]);
                    } else {
                        $query->where('especie', $especie);
                    }
                }
                if (!empty($filters['estado'])) $query->where('estado', $filters['estado']);
                return $query->orderBy('created_at', 'desc')->paginate($perPage);

            case 'adopciones':
                $query = \App\Models\Adopcion\Adopcion::with(['animal', 'adoptante'])
                    ->whereBetween('created_at', [$fechaInicio, $fechaFin]);
                if (!empty($filters['estado'])) $query->where('estado', $filters['estado']);
                return $query->orderBy('created_at', 'desc')->paginate($perPage);

            case 'vacunas':
                $query = \App\Models\Veterinaria\Vacuna::with(['historialClinico.animal', 'tipoVacuna'])
                    ->whereBetween('fecha_aplicacion', [$fechaInicio, $fechaFin]);
                if (!empty($filters['estado'])) $query->where('estado', $filters['estado']);
                return $query->orderBy('fecha_aplicacion', 'desc')->paginate($perPage);

            case 'cirugias':
                $query = \App\Models\Veterinaria\Cirugia::with(['historialClinico.animal', 'veterinario'])
                    ->whereBetween('fecha_realizacion', [$fechaInicio, $fechaFin]);
                if (!empty($filters['estado'])) $query->where('estado', $filters['estado']);
                return $query->orderBy('fecha_realizacion', 'desc')->paginate($perPage);

            case 'denuncias':
                $query = \App\Models\Denuncia\Denuncia::with(['denunciante', 'animal'])
                    ->whereBetween('created_at', [$fechaInicio, $fechaFin]);
                if (!empty($filters['estado'])) $query->where('estado', $filters['estado']);
                if (!empty($filters['prioridad'])) $query->where('prioridad', $filters['prioridad']);
                return $query->orderBy('created_at', 'desc')->paginate($perPage);

            default:
                throw new \InvalidArgumentException("Tipo no soportado: {$tipo}");
        }
    }
}
