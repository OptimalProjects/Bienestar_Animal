<?php

namespace App\Http\Controllers\Api\V1\Veterinary;

use App\Http\Controllers\Api\V1\BaseController;
use App\Services\VeterinariaService;
use App\Models\Veterinaria\Cirugia;
use App\Models\Veterinaria\Procedimiento;
use App\Models\Animal\Animal;
use App\Models\Animal\HistorialClinico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            $query = Cirugia::with([
                'cirujano.usuario', 
                'anestesiologo.usuario',
                'historialClinico.animal'
            ]);

            if ($request->has('animal_id')) {
                $query->whereHas('historialClinico', function ($q) use ($request) {
                    $q->where('animal_id', $request->animal_id);
                });
            }

            if ($request->has('tipo_cirugia')) {
                $query->where('tipo_cirugia', $request->tipo_cirugia);
            }

            if ($request->has('estado')) {
                $query->where('estado', $request->estado);
            }

            if ($request->has('fecha_desde')) {
                $query->whereDate('fecha_programada', '>=', $request->fecha_desde);
            }

            if ($request->has('fecha_hasta')) {
                $query->whereDate('fecha_programada', '<=', $request->fecha_hasta);
            }

            $cirugias = $query->orderBy('fecha_programada', 'desc')
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
            // Relaciones (IDs)
            'historial_clinico_id' => 'required|exists:historiales_clinicos,id',
            'cirujano_id' => 'required|exists:veterinarios,id',
            'anestesiologo_id' => 'nullable|exists:veterinarios,id',
            
            // Tipo y descripción
            'tipo_cirugia' => 'required|in:esterilizacion,castracion,ortopedica,abdominal,oftalmologica,dental,oncologica,emergencia,otra',
            'descripcion' => 'required|string',
            
            // Fechas
            'fecha_programada' => 'required|date',
            'fecha_realizacion' => 'nullable|date|after_or_equal:fecha_programada',
            
            // Detalles quirúrgicos
            'duracion' => 'required|integer|min:1|max:1440',
            'tipo_anestesia' => 'nullable|string|max:500',
            'asistentes' => 'nullable|array',
            'asistentes.*' => 'string|max:200',
            
            // Estado y resultado
            'estado' => 'required|in:programada,realizada,cancelada',
            'resultado' => [
                'nullable',
                'in:exitosa,con_complicaciones,fallida',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->estado === 'realizada' && empty($value)) {
                        $fail('El resultado es requerido cuando el estado es "realizada".');
                    }
                }
            ],
            'complicaciones' => 'nullable|string',
            'postoperatorio' => 'nullable|string',
            'seguimiento_requerido' => 'boolean',
            
            // Estado del animal (solo para cirugías realizadas)
            'estado_animal' => [
                'nullable',
                'in:en_tratamiento,en_recuperacion,estable',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->estado === 'realizada' && empty($value)) {
                        $fail('El estado del animal es requerido cuando la cirugía está realizada.');
                    }
                }
            ],
        ], [
            'historial_clinico_id.required' => 'El historial clínico es requerido',
            'historial_clinico_id.exists' => 'El historial clínico no existe',
            'cirujano_id.required' => 'El veterinario cirujano es requerido',
            'cirujano_id.exists' => 'El veterinario no existe',
            'anestesiologo_id.exists' => 'El anestesiólogo no existe',
            'tipo_cirugia.required' => 'El tipo de cirugía es requerido',
            'tipo_cirugia.in' => 'El tipo de cirugía no es válido',
            'descripcion.required' => 'La descripción es requerida',
            'fecha_programada.required' => 'La fecha programada es requerida',
            'fecha_realizacion.after_or_equal' => 'La fecha de realización no puede ser anterior a la fecha programada',
            'duracion.required' => 'La duración es requerida',
            'duracion.min' => 'La duración debe ser al menos 1 minuto',
            'duracion.max' => 'La duración no puede exceder 1440 minutos (24 horas)',
            'estado.required' => 'El estado es requerido',
            'estado.in' => 'El estado no es válido',
            'resultado.in' => 'El resultado no es válido',
            'estado_animal.in' => 'El estado del animal no es válido',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        DB::beginTransaction();

        try {
            $data = $request->all();
            
            // Si el estado es "realizada" y no se especificó fecha_realizacion,
            // usar la fecha actual
            if ($data['estado'] === 'realizada' && empty($data['fecha_realizacion'])) {
                $data['fecha_realizacion'] = now();
            }
            
            // Asegurar que seguimiento_requerido sea booleano
            $data['seguimiento_requerido'] = $request->boolean('seguimiento_requerido', false);
            
            // Crear la cirugía
            $cirugia = Cirugia::create($data);
            
            // ✅ NUEVA LÓGICA: Actualizar esterilización del animal si la cirugía fue exitosa
            if (
                $cirugia->estado === 'realizada' && 
                $cirugia->resultado === 'exitosa' &&
                in_array($cirugia->tipo_cirugia, ['esterilizacion', 'castracion'])
            ) {
                $this->marcarAnimalComoEsterilizado($cirugia->historial_clinico_id);
            }
            
            // Cargar relaciones
            $cirugia->load([
                'cirujano.usuario',
                'anestesiologo.usuario',
                'historialClinico.animal'
            ]);
            
            DB::commit();
            
            return $this->createdResponse($cirugia, 'Cirugía registrada exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar cirugía: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return $this->serverErrorResponse('Error al registrar cirugía: ' . $e->getMessage());
        }
    }

    /**
     * Obtener cirugia especifica.
     * GET /api/v1/cirugias/{id}
     */
    public function show(string $id)
    {
        try {
            $cirugia = Cirugia::with([
                'cirujano.usuario',
                'anestesiologo.usuario',
                'historialClinico.animal'
            ])->findOrFail($id);

            return $this->successResponse($cirugia);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Cirugía no encontrada');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener cirugía');
        }
    }

    /**
     * Actualizar cirugia.
     * PUT /api/v1/cirugias/{id}
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'fecha_programada' => 'nullable|date',
            'fecha_realizacion' => 'nullable|date',
            'duracion' => 'nullable|integer|min:1|max:1440',
            'tipo_anestesia' => 'nullable|string|max:500',
            'descripcion' => 'nullable|string',
            'complicaciones' => 'nullable|string',
            'resultado' => 'nullable|in:exitosa,con_complicaciones,fallida',
            'postoperatorio' => 'nullable|string',
            'estado' => 'nullable|in:programada,realizada,cancelada',
            'estado_animal' => 'nullable|in:en_tratamiento,en_recuperacion,estable',
            'seguimiento_requerido' => 'boolean',
            'asistentes' => 'nullable|array',
            'asistentes.*' => 'string|max:200',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        DB::beginTransaction();

        try {
            $cirugia = Cirugia::findOrFail($id);
            
            // Guardar valores anteriores
            $estadoAnterior = $cirugia->estado;
            $resultadoAnterior = $cirugia->resultado;
            
            // Si se está cambiando a estado "realizada" y no hay fecha_realizacion
            if ($request->estado === 'realizada' && 
                empty($request->fecha_realizacion) && 
                empty($cirugia->fecha_realizacion)) {
                $request->merge(['fecha_realizacion' => now()]);
            }
            
            $cirugia->update($request->all());
            
            // ✅ NUEVA LÓGICA: Si se cambió a realizada/exitosa y es esterilización
            $cambioAExitosa = (
                $cirugia->estado === 'realizada' && 
                $cirugia->resultado === 'exitosa' &&
                ($estadoAnterior !== 'realizada' || $resultadoAnterior !== 'exitosa') &&
                in_array($cirugia->tipo_cirugia, ['esterilizacion', 'castracion'])
            );
            
            if ($cambioAExitosa) {
                $this->marcarAnimalComoEsterilizado($cirugia->historial_clinico_id);
            }
            
            // Cargar relaciones
            $cirugia->load([
                'cirujano.usuario',
                'anestesiologo.usuario',
                'historialClinico.animal'
            ]);
            
            DB::commit();

            return $this->successResponse($cirugia, 'Cirugía actualizada exitosamente');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return $this->notFoundResponse('Cirugía no encontrada');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar cirugía: ' . $e->getMessage());
            return $this->serverErrorResponse('Error al actualizar cirugía');
        }
    }

    /**
     * Eliminar cirugia.
     * DELETE /api/v1/cirugias/{id}
     */
    public function destroy(string $id)
    {
        try {
            $cirugia = Cirugia::findOrFail($id);
            
            // Solo permitir eliminar cirugías programadas o canceladas
            if ($cirugia->estado === 'realizada') {
                return $this->errorResponse(
                    'No se puede eliminar una cirugía ya realizada',
                    400
                );
            }
            
            $cirugia->delete();

            return $this->successResponse(null, 'Cirugía eliminada exitosamente');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Cirugía no encontrada');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al eliminar cirugía');
        }
    }

    /**
     * ✅ Método helper para marcar animal como esterilizado
     */
    protected function marcarAnimalComoEsterilizado(string $historialClinicoId): void
    {
        try {
            $historial = HistorialClinico::with('animal')->find($historialClinicoId);
            
            if ($historial && $historial->animal) {
                $animal = $historial->animal;
                
                // Solo actualizar si aún no está marcado como esterilizado
                if (!$animal->esterilizacion) {
                    $animal->esterilizacion = true;
                    $animal->save();
                    
                    Log::info("✅ Animal {$animal->codigo_unico} marcado como esterilizado", [
                        'animal_id' => $animal->id,
                        'historial_clinico_id' => $historialClinicoId
                    ]);
                } else {
                    Log::info("ℹ️ Animal {$animal->codigo_unico} ya estaba marcado como esterilizado");
                }
            } else {
                Log::warning("⚠️ No se encontró el animal para el historial clínico {$historialClinicoId}");
            }
        } catch (\Exception $e) {
            Log::error('❌ Error al marcar animal como esterilizado: ' . $e->getMessage(), [
                'historial_clinico_id' => $historialClinicoId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // No lanzamos excepción para no afectar el flujo principal
        }
    }

    /**
     * Estadisticas de cirugias.
     * GET /api/v1/cirugias/estadisticas
     */
    public function estadisticas(Request $request)
    {
        try {
            $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth());
            $fechaFin = $request->get('fecha_fin', now()->endOfMonth());

            $stats = [
                'total_mes' => Cirugia::whereBetween('fecha_programada', [$fechaInicio, $fechaFin])->count(),
                
                'por_estado' => Cirugia::selectRaw('estado, count(*) as cantidad')
                    ->whereBetween('fecha_programada', [$fechaInicio, $fechaFin])
                    ->groupBy('estado')
                    ->pluck('cantidad', 'estado'),
                
                'por_tipo' => Cirugia::selectRaw('tipo_cirugia, count(*) as cantidad')
                    ->whereBetween('fecha_programada', [$fechaInicio, $fechaFin])
                    ->groupBy('tipo_cirugia')
                    ->pluck('cantidad', 'tipo_cirugia'),
                
                'por_resultado' => Cirugia::selectRaw('resultado, count(*) as cantidad')
                    ->where('estado', 'realizada')
                    ->whereBetween('fecha_realizacion', [$fechaInicio, $fechaFin])
                    ->whereNotNull('resultado')
                    ->groupBy('resultado')
                    ->pluck('cantidad', 'resultado'),
                
                'tasa_exito' => $this->calcularTasaExito($fechaInicio, $fechaFin),
                
                'duracion_promedio' => Cirugia::where('estado', 'realizada')
                    ->whereBetween('fecha_realizacion', [$fechaInicio, $fechaFin])
                    ->avg('duracion'),
                
                'con_complicaciones' => Cirugia::where('estado', 'realizada')
                    ->whereBetween('fecha_realizacion', [$fechaInicio, $fechaFin])
                    ->whereNotNull('complicaciones')
                    ->where('complicaciones', '!=', '')
                    ->count(),
                
                'requieren_seguimiento' => Cirugia::where('estado', 'realizada')
                    ->where('seguimiento_requerido', true)
                    ->whereBetween('fecha_realizacion', [$fechaInicio, $fechaFin])
                    ->count(),
            ];

            return $this->successResponse($stats);
        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas: ' . $e->getMessage());
            return $this->serverErrorResponse('Error al obtener estadísticas');
        }
    }

    /**
     * Calcular tasa de exito de cirugias.
     */
    protected function calcularTasaExito($fechaInicio = null, $fechaFin = null): float
    {
        $fechaInicio = $fechaInicio ?? now()->subMonths(3);
        $fechaFin = $fechaFin ?? now();

        $total = Cirugia::where('estado', 'realizada')
            ->whereBetween('fecha_realizacion', [$fechaInicio, $fechaFin])
            ->count();

        if ($total === 0) {
            return 100;
        }

        $exitosas = Cirugia::where('resultado', 'exitosa')
            ->where('estado', 'realizada')
            ->whereBetween('fecha_realizacion', [$fechaInicio, $fechaFin])
            ->count();

        return round(($exitosas / $total) * 100, 1);
    }

    /**
     * Cirugías programadas para hoy.
     * GET /api/v1/cirugias/hoy
     */
    public function hoy()
    {
        try {
            $cirugias = Cirugia::with([
                'cirujano.usuario',
                'anestesiologo.usuario',
                'historialClinico.animal'
            ])
            ->deHoy()
            ->programadas()
            ->orderBy('fecha_programada')
            ->get();

            return $this->successResponse($cirugias);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener cirugías de hoy');
        }
    }

    /**
     * Cirugías pendientes (programadas futuras).
     * GET /api/v1/cirugias/pendientes
     */
    public function pendientes()
    {
        try {
            $cirugias = Cirugia::with([
                'cirujano.usuario',
                'anestesiologo.usuario',
                'historialClinico.animal'
            ])
            ->pendientes()
            ->orderBy('fecha_programada')
            ->get();

            return $this->successResponse($cirugias);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al obtener cirugías pendientes');
        }
    }
}