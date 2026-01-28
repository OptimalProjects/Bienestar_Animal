<?php

namespace App\Services;

use App\Repositories\Contracts\ConsultaRepositoryInterface;
use App\Models\Veterinaria\Consulta;
use App\Models\Veterinaria\Vacuna;
use App\Models\Veterinaria\Cirugia;
use App\Models\Veterinaria\Tratamiento;
use App\Models\Animal\HistorialClinico;
use App\Models\Administracion\Inventario;
use App\Models\Veterinaria\MovimientoInventario;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VeterinariaService
{
    public function __construct(
        protected ConsultaRepositoryInterface $consultaRepository
    ) {}

    // ============================================
    // CONSULTAS
    // ============================================

    /**
     * Listar consultas con paginacion.
     */
    public function listarConsultas(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->consultaRepository->paginateWithFilters($perPage, $filters);
    }

    /**
     * Obtener consultas del dia.
     */
    public function getConsultasDelDia(): Collection
    {
        return $this->consultaRepository->getConsultasDelDia();
    }

    /**
     * Obtener consultas pendientes.
     */
    public function getConsultasPendientes(): Collection
    {
        return $this->consultaRepository->getPendientes();
    }

    /**
     * Registrar nueva consulta con tratamientos y registro de inventario.
     */
    public function registrarConsulta(array $datos): Consulta
    {
        DB::beginTransaction();
        
        try {
            // Verificar que el historial clinico exista
            $historial = HistorialClinico::findOrFail($datos['historial_clinico_id']);

            // Crear consulta
            $consulta = Consulta::create([
                'historial_clinico_id' => $datos['historial_clinico_id'],
                'veterinario_id' => $datos['veterinario_id'],
                'fecha_consulta' => $datos['fecha_consulta'] ?? now(),
                'tipo_consulta' => $datos['tipo_consulta'],
                'motivo_consulta' => $datos['motivo_consulta'],
                'sintomas' => $datos['sintomas'] ?? null,
                'diagnostico' => $datos['diagnostico'] ?? null,
                'observaciones' => $datos['observaciones'] ?? null,
                'peso' => $datos['peso'] ?? null,
                'temperatura' => $datos['temperatura'] ?? null,
                'frecuencia_cardiaca' => $datos['frecuencia_cardiaca'] ?? null,
                'frecuencia_respiratoria' => $datos['frecuencia_respiratoria'] ?? null,
                'estado' => 'realizada',
            ]);

            // Registrar tratamientos si existen
            if (!empty($datos['tratamientos']) && is_array($datos['tratamientos'])) {
                foreach ($datos['tratamientos'] as $tratamiento) {
                    // Guardar medicamento_id en variable temporal (no va en la tabla)
                    $medicamentoId = $tratamiento['medicamento_id'] ?? null;
                    
                    // Construir descripción completa del tratamiento
                    $descripcionCompleta = $tratamiento['descripcion'] ?? 'Tratamiento';
                    
                    // Si hay dosis y frecuencia, agregarlas a la descripción
                    if (!empty($tratamiento['dosis'])) {
                        $descripcionCompleta .= " - Dosis: " . $tratamiento['dosis'];
                    }
                    if (!empty($tratamiento['frecuencia'])) {
                        $descripcionCompleta .= " - Frecuencia: " . $tratamiento['frecuencia'];
                    }
                    
                    // Crear el tratamiento usando solo las columnas que existen en la tabla
                    $nuevoTratamiento = Tratamiento::create([
                        'historial_clinico_id' => $datos['historial_clinico_id'],
                        'consulta_id' => $consulta->id,
                        'tipo_tratamiento' => $tratamiento['tipo_tratamiento'] ?? 'medicamento',
                        'descripcion' => $descripcionCompleta,
                        'objetivo' => $tratamiento['objetivo'] ?? null,
                        'duracion_estimada' => $tratamiento['duracion_dias'] ?? $tratamiento['duracion_estimada'] ?? null,
                        'fecha_inicio' => $tratamiento['fecha_inicio'] ?? now(),
                        'estado' => 'activo',
                        'efectividad' => $tratamiento['efectividad'] ?? 'sin_evaluar',
                    ]);
                    
                    // Si tiene medicamento, registrar salida de inventario
                    if (!empty($medicamentoId)) {
                        // Calcular cantidad total usada
                        $cantidadTotal = $this->calcularCantidadMedicamento($tratamiento);
                        
                        if ($cantidadTotal > 0) {
                            // Obtener el medicamento del inventario
                            $medicamento = Inventario::find($medicamentoId);
                            
                            if (!$medicamento) {
                                throw new \Exception('Medicamento no encontrado en inventario');
                            }
                            
                            // Verificar stock disponible
                            if ($medicamento->cantidad_actual < $cantidadTotal) {
                                throw new \Exception(
                                    "Stock insuficiente de {$medicamento->nombre}. " .
                                    "Disponible: {$medicamento->cantidad_actual}, " .
                                    "Requerido: {$cantidadTotal}"
                                );
                            }
                            
                            // Registrar salida de inventario
                            $medicamento->cantidad_actual -= $cantidadTotal;
                            $medicamento->save();
                            
                            // Crear registro de movimiento
                            MovimientoInventario::create([
                                'medicamento_id' => $medicamentoId,
                                'consulta_id' => $consulta->id,
                                'tipo_movimiento' => 'salida',
                                'cantidad' => $cantidadTotal,
                                'motivo' => 'Uso en consulta veterinaria',
                                'descripcion' => $tratamiento['descripcion'] ?? "Tratamiento para consulta",
                                'usuario_id' => auth()->id(),
                            ]);

                            Log::info('Movimiento de inventario registrado', [
                                'medicamento_id' => $medicamentoId,
                                'consulta_id' => $consulta->id,
                                'cantidad' => $cantidadTotal,
                            ]);
                        }
                    }
                }
            }

            // Actualizar estado de salud del animal si se proporciona
            if (!empty($datos['estado_salud'])) {
                $historial->update(['estado_general' => $datos['estado_salud']]);
            }

            DB::commit();
            
            Log::info('Consulta registrada exitosamente', [
                'consulta_id' => $consulta->id,
                'historial_clinico_id' => $consulta->historial_clinico_id,
            ]);

            return $consulta->fresh([
                'tratamientos',
                'historialClinico.animal',
                'veterinario'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al registrar consulta', [
                'error' => $e->getMessage(),
                'datos' => $datos,
            ]);
            
            throw $e;
        }
    }

    /**
     * Calcular la cantidad total de medicamento necesaria para el tratamiento.
     * 
     * @param array $tratamiento
     * @return float
     */
    private function calcularCantidadMedicamento(array $tratamiento): float
    {
        // Si viene cantidad directa
        if (isset($tratamiento['cantidad_total'])) {
            return (float) $tratamiento['cantidad_total'];
        }
        
        // Si no, intentar calcular desde dosis, frecuencia y duración
        $dosis = $tratamiento['dosis'] ?? '';
        $duracionDias = (int) ($tratamiento['duracion_dias'] ?? 0);
        $frecuencia = $tratamiento['frecuencia'] ?? '';
        
        // Extraer número de la dosis (ej: "5mg" -> 5, "1 tableta" -> 1)
        preg_match('/(\d+\.?\d*)/', $dosis, $matches);
        $dosisNumero = isset($matches[1]) ? (float) $matches[1] : 1;
        
        // Extraer veces por día de la frecuencia
        $vecesPorDia = 1;
        
        // Casos comunes:
        // "cada 6 horas" -> 4 veces/día
        // "cada 8 horas" -> 3 veces/día
        // "cada 12 horas" -> 2 veces/día
        // "cada 24 horas" o "1 vez al día" -> 1 vez/día
        // "2 veces al día" -> 2 veces/día
        // "3 veces al día" -> 3 veces/día
        
        if (preg_match('/(\d+)\s*(veces?|vez)/i', $frecuencia, $matches)) {
            $vecesPorDia = (int) $matches[1];
        } elseif (preg_match('/cada\s*(\d+)\s*horas?/i', $frecuencia, $matches)) {
            $horas = (int) $matches[1];
            if ($horas > 0) {
                $vecesPorDia = (int) (24 / $horas);
            }
        }
        
        // Cantidad total = dosis × veces_por_día × días
        $cantidadTotal = $dosisNumero * $vecesPorDia * $duracionDias;
        
        return $cantidadTotal > 0 ? $cantidadTotal : 1;
    }

    /**
     * Obtener consulta con detalles.
     */
    public function obtenerConsulta(string $id)
    {
        return $this->consultaRepository->getWithTratamientos($id);
    }

    // ============================================
    // VACUNAS
    // ============================================

    /**
     * Registrar vacuna.
     */
    public function registrarVacuna(array $data): Vacuna
    {
        DB::beginTransaction();
        
        try {
            $vacuna = Vacuna::create([
                'historial_clinico_id' => $data['historial_clinico_id'],
                'tipo_vacuna_id' => $data['tipo_vacuna_id'],
                'veterinario_id' => $data['veterinario_id'],
                'fecha_aplicacion' => $data['fecha_aplicacion'] ?? now(),
                'fecha_proxima_dosis' => $data['fecha_proxima'] ?? null, 
                'nombre_vacuna' => $data['nombre_vacuna'],
                'lote_vacuna' => $data['lote'], 
                'fabricante' => $data['fabricante'],
                'fecha_vencimiento' => $data['fecha_vencimiento'] ?? null,
                'dosis' => $data['dosis'],
                'via_administracion' => $data['via_administracion'],
                'sitio_aplicacion' => $data['sitio_aplicacion'] ?? null,
                'numero_dosis' => $data['numero_dosis'],
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            DB::commit();
            
            return $vacuna->load(['tipoVacuna', 'veterinario', 'historialClinico.animal']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtener vacunas de un animal.
     */
    public function getVacunasAnimal(string $animalId): Collection
    {
        $historial = HistorialClinico::where('animal_id', $animalId)->firstOrFail();

        return Vacuna::where('historial_clinico_id', $historial->id)
            ->with(['tipoVacuna', 'veterinario.usuario'])
            ->orderBy('fecha_aplicacion', 'desc')
            ->get();
    }

    // ============================================
    // CIRUGÍAS
    // ============================================

    /**
     * Registrar una cirugía.
     */
    public function registrarCirugia(array $data): Cirugia
    {
        DB::beginTransaction();

        try {
            // Validar que el historial clínico existe
            $historialClinico = HistorialClinico::findOrFail($data['historial_clinico_id']);

            // Validar que el cirujano existe si se proporciona
            if (isset($data['cirujano_id'])) {
                $cirujano = \App\Models\Veterinaria\Veterinario::findOrFail($data['cirujano_id']);
            }

            // Validar que el anestesiólogo existe si se proporciona
            if (isset($data['anestesiologo_id']) && !empty($data['anestesiologo_id'])) {
                $anestesiologo = \App\Models\Veterinaria\Veterinario::findOrFail($data['anestesiologo_id']);
            }

            // Preparar datos para la cirugía
            $cirugiaData = [
                'historial_clinico_id' => $data['historial_clinico_id'],
                'cirujano_id' => $data['cirujano_id'] ?? $data['veterinario_id'] ?? null,
                'anestesiologo_id' => $data['anestesiologo_id'] ?? null,
                'tipo_cirugia' => $data['tipo_cirugia'],
                'descripcion' => $data['descripcion'] ?? null,
                'fecha_programada' => $data['fecha_programada'] ?? $data['fecha_cirugia'] ?? now(),
                'fecha_realizacion' => $data['fecha_realizacion'] ?? null,
                'duracion' => $data['duracion'] ?? $data['duracion_minutos'] ?? null,
                'tipo_anestesia' => $data['tipo_anestesia'] ?? $data['anestesia_utilizada'] ?? null,
                'asistentes' => $data['asistentes'] ?? [],
                'estado' => $data['estado'] ?? 'programada',
                'resultado' => $data['resultado'] ?? null,
                'complicaciones' => $data['complicaciones'] ?? null,
                'postoperatorio' => $data['postoperatorio'] ?? $data['notas_postoperatorias'] ?? null,
                'seguimiento_requerido' => $data['seguimiento_requerido'] ?? false,
                'estado_animal' => $data['estado_animal'] ?? null,
            ];

            // Si el estado es "realizada" y no se proporcionó fecha_realizacion
            if ($cirugiaData['estado'] === 'realizada' && empty($cirugiaData['fecha_realizacion'])) {
                $cirugiaData['fecha_realizacion'] = now();
            }

            // Crear la cirugía
            $cirugia = Cirugia::create($cirugiaData);

            // Actualizar estado del animal si se especifica
            if (!empty($data['estado_animal'])) {
                if ($historialClinico && $historialClinico->animal) {
                    $historialClinico->animal->update(['estado' => $data['estado_animal']]);
                }
            }

            // Cargar relaciones
            $cirugia->load([
                'cirujano.usuario',
                'anestesiologo.usuario',
                'historialClinico.animal'
            ]);

            Log::info('Cirugía registrada exitosamente', [
                'cirugia_id' => $cirugia->id
            ]);

            DB::commit();

            return $cirugia;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al registrar cirugía', [
                'error' => $e->getMessage()
            ]);

            throw new \Exception('Error al registrar la cirugía: ' . $e->getMessage());
        }
    }

    /**
     * Obtener cirugías programadas para hoy.
     */
    public function getCirugiasHoy(): Collection
    {
        return Cirugia::with([
            'cirujano.usuario',
            'anestesiologo.usuario',
            'historialClinico.animal'
        ])
        ->deHoy()
        ->programadas()
        ->orderBy('fecha_programada')
        ->get();
    }

    /**
     * Obtener cirugías pendientes.
     */
    public function getCirugiasPendientes(): Collection
    {
        return Cirugia::with([
            'cirujano.usuario',
            'anestesiologo.usuario',
            'historialClinico.animal'
        ])
        ->pendientes()
        ->orderBy('fecha_programada')
        ->get();
    }

    // ============================================
    // ESTADÍSTICAS GENERALES
    // ============================================

    /**
     * Obtener estadisticas de veterinaria.
     */
    public function getEstadisticas(): array
    {
        $consultaStats = $this->consultaRepository->getEstadisticas();

        $vacunasHoy = Vacuna::whereDate('fecha_aplicacion', now())->count();
        $cirugiasHoy = Cirugia::whereDate('fecha_realizacion', now())->count();

        return array_merge($consultaStats, [
            'vacunas_hoy' => $vacunasHoy,
            'cirugias_hoy' => $cirugiasHoy,
        ]);
    }

    // ============================================
    // HISTORIAL CLÍNICO
    // ============================================

    /**
     * Obtener historial clinico completo de un animal.
     */
    public function getHistorialCompleto(string $animalId): array
    {
        $historial = HistorialClinico::where('animal_id', $animalId)
            ->with([
                'animal',
                'consultas' => fn($q) => $q->orderBy('fecha_consulta', 'desc'),
                'consultas.veterinario.usuario',
                'consultas.tratamientos',
                'vacunas' => fn($q) => $q->orderBy('fecha_aplicacion', 'desc'),
                'vacunas.tipoVacuna',
                'cirugias' => fn($q) => $q->orderBy('fecha_programada', 'desc'),
                'cirugias.cirujano.usuario',
                'cirugias.anestesiologo.usuario',
            ])
            ->firstOrFail();

        return [
            'id' => $historial->id,
            'animal' => $historial->animal,
            'animal_id' => $historial->animal_id,
            'estado_general' => $historial->estado_general,
            'consultas' => $historial->consultas,
            'vacunas' => $historial->vacunas,
            'cirugias' => $historial->cirugias,
            'resumen' => [
                'total_consultas' => $historial->consultas->count(),
                'total_vacunas' => $historial->vacunas->count(),
                'total_cirugias' => $historial->cirugias->count(),
                'ultima_consulta' => $historial->consultas->first()?->fecha_consulta,
                'ultima_vacuna' => $historial->vacunas->first()?->fecha_aplicacion,
                'ultima_cirugia' => $historial->cirugias->first()?->fecha_programada,
            ],
        ];
    }
}