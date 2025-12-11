<?php

namespace App\Repositories\Eloquent;

use App\Models\Veterinaria\Consulta;
use App\Repositories\Contracts\ConsultaRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ConsultaRepository extends BaseRepository implements ConsultaRepositoryInterface
{
    /**
     * Constructor.
     */
    public function __construct(Consulta $model)
    {
        parent::__construct($model);
    }

    /**
     * Obtener consultas por animal.
     */
    public function findByAnimal(string $animalId): Collection
    {
        return $this->model
            ->whereHas('historialClinico', function ($query) use ($animalId) {
                $query->where('animal_id', $animalId);
            })
            ->orderBy('fecha_consulta', 'desc')
            ->get();
    }

    /**
     * Obtener consultas por veterinario.
     */
    public function findByVeterinario(string $veterinarioId): Collection
    {
        return $this->model
            ->where('veterinario_id', $veterinarioId)
            ->orderBy('fecha_consulta', 'desc')
            ->get();
    }

    /**
     * Obtener consultas del dia.
     */
    public function getConsultasDelDia(): Collection
    {
        return $this->model
            ->delDia()
            ->with(['historialClinico.animal', 'veterinario.usuario'])
            ->orderBy('fecha_consulta', 'desc')
            ->get();
    }

    /**
     * Obtener consultas pendientes.
     */
    public function getPendientes(): Collection
    {
        return $this->model
            ->pendientes()
            ->with(['historialClinico.animal', 'veterinario.usuario'])
            ->orderBy('fecha_consulta', 'asc')
            ->get();
    }

    /**
     * Obtener consultas por rango de fechas.
     */
    public function findByRangoFechas(string $fechaInicio, string $fechaFin): Collection
    {
        return $this->model
            ->whereBetween('fecha_consulta', [$fechaInicio, $fechaFin])
            ->with(['historialClinico.animal', 'veterinario.usuario'])
            ->orderBy('fecha_consulta', 'desc')
            ->get();
    }

    /**
     * Obtener consulta con tratamientos.
     */
    public function getWithTratamientos(string $id)
    {
        return $this->model
            ->with([
                'tratamientos',
                'tratamientos.medicamento',
                'historialClinico.animal',
                'veterinario.usuario',
            ])
            ->findOrFail($id);
    }

    /**
     * Obtener estadisticas de consultas.
     */
    public function getEstadisticas(): array
    {
        $hoy = now()->toDateString();
        $inicioMes = now()->startOfMonth()->toDateString();
        $finMes = now()->endOfMonth()->toDateString();

        return [
            'consultas_hoy' => $this->model->delDia()->count(),
            'consultas_mes' => $this->model
                ->whereBetween('fecha_consulta', [$inicioMes, $finMes])
                ->count(),
            'pendientes' => $this->model->pendientes()->count(),
            'por_tipo' => $this->model
                ->selectRaw('tipo_consulta, count(*) as cantidad')
                ->groupBy('tipo_consulta')
                ->pluck('cantidad', 'tipo_consulta')
                ->toArray(),
        ];
    }

    /**
     * Obtener consultas con paginacion y filtros.
     */
    public function paginateWithFilters(int $perPage, array $filters = [])
    {
        $query = $this->model->query()
            ->with(['historialClinico.animal', 'veterinario.usuario']);

        if (!empty($filters['veterinario_id'])) {
            $query->where('veterinario_id', $filters['veterinario_id']);
        }

        if (!empty($filters['tipo_consulta'])) {
            $query->where('tipo_consulta', $filters['tipo_consulta']);
        }

        if (!empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (!empty($filters['fecha_desde'])) {
            $query->whereDate('fecha_consulta', '>=', $filters['fecha_desde']);
        }

        if (!empty($filters['fecha_hasta'])) {
            $query->whereDate('fecha_consulta', '<=', $filters['fecha_hasta']);
        }

        if (!empty($filters['animal_id'])) {
            $query->whereHas('historialClinico', function ($q) use ($filters) {
                $q->where('animal_id', $filters['animal_id']);
            });
        }

        $orderBy = $filters['order_by'] ?? 'fecha_consulta';
        $orderDir = $filters['order_dir'] ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($perPage);
    }
}
