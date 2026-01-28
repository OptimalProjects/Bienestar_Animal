<?php

namespace App\Services;

use App\Repositories\Contracts\ConsultaRepositoryInterface;
use App\Models\Veterinaria\Consulta;
use App\Models\Veterinaria\Vacuna;
use App\Models\Veterinaria\Cirugia;
use App\Models\Veterinaria\Tratamiento;
use App\Models\Animal\HistorialClinico;
use App\Models\Animal\Animal;
use App\Models\Adopcion\Adoptante;
use App\Mail\CirugiaMail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
     * Registrar nueva consulta.
     */
    public function registrarConsulta(array $data): Consulta
    {
        return DB::transaction(function () use ($data) {
            // Verificar que el historial clinico exista
            $historial = HistorialClinico::findOrFail($data['historial_clinico_id']);

            // Crear consulta
            $consulta = Consulta::create([
                'historial_clinico_id' => $data['historial_clinico_id'],
                'veterinario_id' => $data['veterinario_id'],
                'fecha_consulta' => $data['fecha_consulta'] ?? now(),
                'tipo_consulta' => $data['tipo_consulta'],
                'motivo_consulta' => $data['motivo_consulta'],
                'sintomas' => $data['sintomas'] ?? null,
                'diagnostico' => $data['diagnostico'] ?? null,
                'observaciones' => $data['observaciones'] ?? null,
                'peso' => $data['peso'] ?? null,
                'temperatura' => $data['temperatura'] ?? null,
                'frecuencia_cardiaca' => $data['frecuencia_cardiaca'] ?? null,
                'frecuencia_respiratoria' => $data['frecuencia_respiratoria'] ?? null,
                'estado' => 'realizada',
            ]);

            // Registrar tratamientos si existen
            if (!empty($data['tratamientos'])) {
                foreach ($data['tratamientos'] as $tratamiento) {
                    Tratamiento::create([
                        'consulta_id' => $consulta->id,
                        'medicamento_id' => $tratamiento['medicamento_id'] ?? null,
                        'descripcion' => $tratamiento['descripcion'],
                        'dosis' => $tratamiento['dosis'] ?? null,
                        'frecuencia' => $tratamiento['frecuencia'] ?? null,
                        'duracion_dias' => $tratamiento['duracion_dias'] ?? null,
                        'fecha_inicio' => $tratamiento['fecha_inicio'] ?? now(),
                        'estado' => 'activo',
                    ]);
                }
            }

            // Actualizar estado de salud del animal si se proporciona
            if (!empty($data['estado_salud'])) {
                $historial->update(['estado_general' => $data['estado_salud']]);
            }

            return $consulta->fresh(['tratamientos', 'historialClinico.animal']);
        });
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
     *
     * @param array $data
     * @return Cirugia
     * @throws \Exception
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

            // Cargar relaciones
            $cirugia->load([
                'cirujano.usuario',
                'anestesiologo.usuario',
                'historialClinico.animal'
            ]);

            Log::info('Cirugía registrada exitosamente', [
                'cirugia_id' => $cirugia->id,
                'tipo' => $cirugia->tipo_cirugia,
                'animal_id' => $historialClinico->animal_id
            ]);

            DB::commit();

            // Notificar al adoptante si el animal tiene uno
            $this->notificarAdoptanteCirugia($cirugia, $historialClinico->animal);

            return $cirugia;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al registrar cirugía', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);

            throw new \Exception('Error al registrar la cirugía: ' . $e->getMessage());
        }
    }

    /**
     * Obtener cirugías de un animal.
     *
     * @param string $animalId
     * @return Collection
     */
    public function getCirugiasAnimal(string $animalId): Collection
    {
        try {
            $historial = HistorialClinico::where('animal_id', $animalId)->firstOrFail();

            $cirugias = Cirugia::where('historial_clinico_id', $historial->id)
                ->with([
                    'cirujano.usuario',
                    'anestesiologo.usuario',
                    'historialClinico'
                ])
                ->orderBy('fecha_programada', 'desc')
                ->get();

            return $cirugias;

        } catch (\Exception $e) {
            Log::error('Error al obtener cirugías del animal', [
                'animal_id' => $animalId,
                'error' => $e->getMessage()
            ]);

            throw new \Exception('Error al obtener cirugías del animal');
        }
    }

    /**
     * Actualizar cirugía.
     *
     * @param string $cirugiaId
     * @param array $data
     * @return Cirugia
     * @throws \Exception
     */
    public function actualizarCirugia(string $cirugiaId, array $data): Cirugia
    {
        DB::beginTransaction();

        try {
            $cirugia = Cirugia::findOrFail($cirugiaId);

            // Validar que la cirugía puede ser editada
            if (!$cirugia->puedeSerEditada() && !isset($data['resultado'])) {
                throw new \Exception('Esta cirugía no puede ser editada porque ya fue realizada');
            }

            // Si se está cambiando a estado "realizada"
            if (isset($data['estado']) && $data['estado'] === 'realizada') {
                if (empty($data['fecha_realizacion']) && empty($cirugia->fecha_realizacion)) {
                    $data['fecha_realizacion'] = now();
                }
            }

            // Actualizar la cirugía
            $cirugia->update($data);

            // Cargar relaciones
            $cirugia->load([
                'cirujano.usuario',
                'anestesiologo.usuario',
                'historialClinico.animal'
            ]);

            Log::info('Cirugía actualizada exitosamente', [
                'cirugia_id' => $cirugia->id
            ]);

            DB::commit();

            return $cirugia;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al actualizar cirugía', [
                'cirugia_id' => $cirugiaId,
                'error' => $e->getMessage()
            ]);

            throw new \Exception('Error al actualizar la cirugía: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar cirugía.
     *
     * @param string $cirugiaId
     * @param string|null $motivo
     * @return Cirugia
     * @throws \Exception
     */
    public function cancelarCirugia(string $cirugiaId, ?string $motivo = null): Cirugia
    {
        DB::beginTransaction();

        try {
            $cirugia = Cirugia::findOrFail($cirugiaId);

            if ($cirugia->estado === 'realizada') {
                throw new \Exception('No se puede cancelar una cirugía ya realizada');
            }

            $cirugia->cancelar($motivo);

            Log::info('Cirugía cancelada', [
                'cirugia_id' => $cirugia->id,
                'motivo' => $motivo
            ]);

            DB::commit();

            return $cirugia;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al cancelar cirugía', [
                'cirugia_id' => $cirugiaId,
                'error' => $e->getMessage()
            ]);

            throw new \Exception('Error al cancelar la cirugía: ' . $e->getMessage());
        }
    }

    /**
     * Obtener cirugías programadas para hoy.
     *
     * @return Collection
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
     *
     * @return Collection
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

    /**
     * Obtener cirugías que requieren seguimiento.
     *
     * @return Collection
     */
    public function getCirugiasRequierenSeguimiento(): Collection
    {
        return Cirugia::with([
            'cirujano.usuario',
            'anestesiologo.usuario',
            'historialClinico.animal'
        ])
        ->requierenSeguimiento()
        ->orderBy('fecha_realizacion', 'desc')
        ->get();
    }

    /**
     * Estadísticas de cirugías.
     *
     * @param string|null $fechaInicio
     * @param string|null $fechaFin
     * @return array
     */
    public function getEstadisticasCirugias($fechaInicio = null, $fechaFin = null): array
    {
        $fechaInicio = $fechaInicio ?? now()->startOfMonth();
        $fechaFin = $fechaFin ?? now()->endOfMonth();

        $total = Cirugia::entreFechas($fechaInicio, $fechaFin)->count();
        $realizadas = Cirugia::realizadas()->entreFechas($fechaInicio, $fechaFin)->count();
        $programadas = Cirugia::programadas()->entreFechas($fechaInicio, $fechaFin)->count();
        $exitosas = Cirugia::exitosas()->entreFechas($fechaInicio, $fechaFin)->count();
        $conComplicaciones = Cirugia::conComplicaciones()->entreFechas($fechaInicio, $fechaFin)->count();

        $tasaExito = $realizadas > 0 ? round(($exitosas / $realizadas) * 100, 1) : 0;

        return [
            'total' => $total,
            'realizadas' => $realizadas,
            'programadas' => $programadas,
            'exitosas' => $exitosas,
            'con_complicaciones' => $conComplicaciones,
            'tasa_exito' => $tasaExito,
            'duracion_promedio' => Cirugia::realizadas()
                ->entreFechas($fechaInicio, $fechaFin)
                ->avg('duracion'),
        ];
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
                // 'examenes' => fn($q) => $q->orderBy('fecha_examen', 'desc'), // TODO: Agregar relación cuando esté disponible
            ])
            ->firstOrFail();

        return [
            'id' => $historial->id, // ID del historial clínico (necesario para crear consultas)
            'animal' => $historial->animal,
            'animal_id' => $historial->animal_id,
            'estado_general' => $historial->estado_general,
            'consultas' => $historial->consultas,
            'vacunas' => $historial->vacunas,
            'cirugias' => $historial->cirugias,
            // 'examenes' => $historial->examenes, // TODO: Agregar cuando esté disponible
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

    // ============================================
    // NOTIFICACIONES
    // ============================================

    /**
     * Notificar al adoptante sobre una cirugía realizada a su mascota.
     *
     * @param Cirugia $cirugia
     * @param Animal $animal
     */
    protected function notificarAdoptanteCirugia(Cirugia $cirugia, Animal $animal): void
    {
        try {
            // Obtener la adopción activa/completada del animal
            $adopcion = $animal->adopciones()
                ->whereIn('estado', ['aprobada', 'completada'])
                ->orderByDesc('fecha_entrega')
                ->orderByDesc('fecha_aprobacion')
                ->first();

            if (!$adopcion || !$adopcion->adoptante) {
                Log::info('No se encontró adoptante para notificar sobre cirugía', [
                    'cirugia_id' => $cirugia->id,
                    'animal_id' => $animal->id,
                    'animal_nombre' => $animal->nombre,
                ]);
                return;
            }

            $adoptante = $adopcion->adoptante;

            // Validar email del adoptante
            if (!$adoptante->email || !filter_var($adoptante->email, FILTER_VALIDATE_EMAIL)) {
                Log::warning('Adoptante sin email válido para notificación de cirugía', [
                    'cirugia_id' => $cirugia->id,
                    'adoptante_id' => $adoptante->id,
                ]);
                return;
            }

            // Enviar correo
            Mail::to($adoptante->email)->send(new CirugiaMail($cirugia, $adoptante, $animal));

            Log::info('Notificación de cirugía enviada al adoptante', [
                'cirugia_id' => $cirugia->id,
                'tipo_cirugia' => $cirugia->tipo_cirugia,
                'animal_id' => $animal->id,
                'animal_nombre' => $animal->nombre,
                'adoptante_id' => $adoptante->id,
                'adoptante_email' => $adoptante->email,
                'adoptante_nombre' => $adoptante->nombre_completo ?? $adoptante->nombres,
            ]);

        } catch (\Exception $e) {
            // No interrumpir el flujo si falla el envío de correo
            Log::error('Error enviando notificación de cirugía al adoptante', [
                'cirugia_id' => $cirugia->id,
                'animal_id' => $animal->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}