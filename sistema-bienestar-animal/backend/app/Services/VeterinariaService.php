<?php

namespace App\Services;

use App\Repositories\Contracts\ConsultaRepositoryInterface;
use App\Models\Veterinaria\Consulta;
use App\Models\Veterinaria\Vacuna;
use App\Models\Veterinaria\Cirugia;
use App\Models\Veterinaria\Tratamiento;
use App\Models\Animal\HistorialClinico;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class VeterinariaService
{
    public function __construct(
        protected ConsultaRepositoryInterface $consultaRepository
    ) {}

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

    /**
     * Registrar vacuna.
     */
    public function registrarVacuna(array $data): Vacuna
    {
        return DB::transaction(function () use ($data) {
            $vacuna = Vacuna::create([
                'historial_clinico_id' => $data['historial_clinico_id'],
                'tipo_vacuna_id' => $data['tipo_vacuna_id'],
                'veterinario_id' => $data['veterinario_id'],
                'fecha_aplicacion' => $data['fecha_aplicacion'] ?? now(),
                'fecha_proxima' => $data['fecha_proxima'] ?? null,
                'lote' => $data['lote'] ?? null,
                'fabricante' => $data['fabricante'] ?? null,
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            return $vacuna->fresh(['tipoVacuna', 'veterinario.usuario']);
        });
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

    /**
     * Registrar cirugia.
     */
    public function registrarCirugia(array $data): Cirugia
    {
        return DB::transaction(function () use ($data) {
            $cirugia = Cirugia::create([
                'historial_clinico_id' => $data['historial_clinico_id'],
                'procedimiento_id' => $data['procedimiento_id'] ?? null,
                'veterinario_id' => $data['veterinario_id'],
                'fecha_cirugia' => $data['fecha_cirugia'] ?? now(),
                'tipo_cirugia' => $data['tipo_cirugia'],
                'descripcion' => $data['descripcion'],
                'anestesia_utilizada' => $data['anestesia_utilizada'] ?? null,
                'duracion_minutos' => $data['duracion_minutos'] ?? null,
                'complicaciones' => $data['complicaciones'] ?? null,
                'resultado' => $data['resultado'] ?? 'exitosa',
                'notas_postoperatorias' => $data['notas_postoperatorias'] ?? null,
            ]);

            // Actualizar estado del animal si se especifica
            if (!empty($data['estado_animal'])) {
                $historial = HistorialClinico::find($data['historial_clinico_id']);
                $historial->animal->update(['estado' => $data['estado_animal']]);
            }

            return $cirugia->fresh(['procedimiento', 'veterinario.usuario']);
        });
    }

    /**
     * Obtener cirugias de un animal.
     */
    public function getCirugiasAnimal(string $animalId): Collection
    {
        $historial = HistorialClinico::where('animal_id', $animalId)->firstOrFail();

        return Cirugia::where('historial_clinico_id', $historial->id)
            ->with(['procedimiento', 'veterinario.usuario'])
            ->orderBy('fecha_cirugia', 'desc')
            ->get();
    }

    /**
     * Obtener estadisticas de veterinaria.
     */
    public function getEstadisticas(): array
    {
        $consultaStats = $this->consultaRepository->getEstadisticas();

        $vacunasHoy = Vacuna::whereDate('fecha_aplicacion', now())->count();
        $cirugiasHoy = Cirugia::whereDate('fecha_cirugia', now())->count();

        return array_merge($consultaStats, [
            'vacunas_hoy' => $vacunasHoy,
            'cirugias_hoy' => $cirugiasHoy,
        ]);
    }

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
                'cirugias' => fn($q) => $q->orderBy('fecha_cirugia', 'desc'),
                'cirugias.veterinario.usuario',
                'examenes' => fn($q) => $q->orderBy('fecha_examen', 'desc'),
            ])
            ->firstOrFail();

        return [
            'animal' => $historial->animal,
            'estado_general' => $historial->estado_general,
            'consultas' => $historial->consultas,
            'vacunas' => $historial->vacunas,
            'cirugias' => $historial->cirugias,
            'examenes' => $historial->examenes,
            'resumen' => [
                'total_consultas' => $historial->consultas->count(),
                'total_vacunas' => $historial->vacunas->count(),
                'total_cirugias' => $historial->cirugias->count(),
                'ultima_consulta' => $historial->consultas->first()?->fecha_consulta,
                'ultima_vacuna' => $historial->vacunas->first()?->fecha_aplicacion,
            ],
        ];
    }
}
