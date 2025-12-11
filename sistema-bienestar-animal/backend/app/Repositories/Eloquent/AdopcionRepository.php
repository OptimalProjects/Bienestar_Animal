<?php

namespace App\Repositories\Eloquent;

use App\Models\Adopcion\Adopcion;
use App\Repositories\Contracts\AdopcionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AdopcionRepository extends BaseRepository implements AdopcionRepositoryInterface
{
    /**
     * Constructor.
     */
    public function __construct(Adopcion $model)
    {
        parent::__construct($model);
    }

    /**
     * Obtener adopciones por adoptante.
     */
    public function findByAdoptante(string $adoptanteId): Collection
    {
        return $this->model
            ->where('adoptante_id', $adoptanteId)
            ->with(['animal', 'visitas'])
            ->orderBy('fecha_solicitud', 'desc')
            ->get();
    }

    /**
     * Obtener adopciones por animal.
     */
    public function findByAnimal(string $animalId): Collection
    {
        return $this->model
            ->where('animal_id', $animalId)
            ->with(['adoptante', 'visitas'])
            ->orderBy('fecha_solicitud', 'desc')
            ->get();
    }

    /**
     * Obtener adopciones por estado.
     */
    public function findByEstado(string $estado): Collection
    {
        return $this->model
            ->where('estado', $estado)
            ->with(['animal', 'adoptante'])
            ->orderBy('fecha_solicitud', 'desc')
            ->get();
    }

    /**
     * Obtener adopciones pendientes de evaluacion.
     */
    public function getPendientesEvaluacion(): Collection
    {
        return $this->model
            ->pendientes()
            ->with(['animal', 'adoptante'])
            ->orderBy('fecha_solicitud', 'asc')
            ->get();
    }

    /**
     * Obtener adopciones que requieren visita de seguimiento.
     */
    public function getRequierenVisita(): Collection
    {
        return $this->model
            ->where('estado', 'aprobada')
            ->where('requiere_visita_seguimiento', true)
            ->whereDoesntHave('visitas', function ($query) {
                $query->where('created_at', '>=', now()->subMonths(3));
            })
            ->with(['animal', 'adoptante'])
            ->get();
    }

    /**
     * Obtener adopcion con visitas de seguimiento.
     */
    public function getWithVisitas(string $id)
    {
        return $this->model
            ->with([
                'animal',
                'adoptante',
                'visitas' => function ($query) {
                    $query->orderBy('fecha_programada', 'desc');
                },
                'visitas.funcionario',
                'evaluador',
            ])
            ->findOrFail($id);
    }

    /**
     * Obtener estadisticas de adopciones.
     */
    public function getEstadisticas(): array
    {
        $inicioMes = now()->startOfMonth();
        $finMes = now()->endOfMonth();
        $inicioAnio = now()->startOfYear();

        return [
            'pendientes' => $this->model->pendientes()->count(),
            'aprobadas_mes' => $this->model
                ->where('estado', 'aprobada')
                ->whereBetween('fecha_aprobacion', [$inicioMes, $finMes])
                ->count(),
            'aprobadas_anio' => $this->model
                ->where('estado', 'aprobada')
                ->where('fecha_aprobacion', '>=', $inicioAnio)
                ->count(),
            'rechazadas_mes' => $this->model
                ->where('estado', 'rechazada')
                ->whereBetween('updated_at', [$inicioMes, $finMes])
                ->count(),
            'por_estado' => $this->model
                ->selectRaw('estado, count(*) as cantidad')
                ->groupBy('estado')
                ->pluck('cantidad', 'estado')
                ->toArray(),
            'tiempo_promedio_aprobacion' => $this->calcularTiempoPromedioAprobacion(),
        ];
    }

    /**
     * Calcular tiempo promedio de aprobacion en dias.
     */
    protected function calcularTiempoPromedioAprobacion(): ?float
    {
        $adopciones = $this->model
            ->where('estado', 'aprobada')
            ->whereNotNull('fecha_aprobacion')
            ->where('fecha_aprobacion', '>=', now()->subMonths(6))
            ->get();

        if ($adopciones->isEmpty()) {
            return null;
        }

        $totalDias = $adopciones->sum(function ($adopcion) {
            return $adopcion->fecha_solicitud->diffInDays($adopcion->fecha_aprobacion);
        });

        return round($totalDias / $adopciones->count(), 1);
    }

    /**
     * Obtener adopciones con paginacion y filtros.
     */
    public function paginateWithFilters(int $perPage, array $filters = [])
    {
        $query = $this->model->query()
            ->with(['animal', 'adoptante']);

        if (!empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (!empty($filters['adoptante_id'])) {
            $query->where('adoptante_id', $filters['adoptante_id']);
        }

        if (!empty($filters['animal_id'])) {
            $query->where('animal_id', $filters['animal_id']);
        }

        if (!empty($filters['fecha_desde'])) {
            $query->whereDate('fecha_solicitud', '>=', $filters['fecha_desde']);
        }

        if (!empty($filters['fecha_hasta'])) {
            $query->whereDate('fecha_solicitud', '<=', $filters['fecha_hasta']);
        }

        if (!empty($filters['busqueda'])) {
            $busqueda = $filters['busqueda'];
            $query->where(function ($q) use ($busqueda) {
                $q->whereHas('adoptante', function ($sub) use ($busqueda) {
                    $sub->where('nombre_completo', 'like', "%{$busqueda}%")
                        ->orWhere('documento_identidad', 'like', "%{$busqueda}%");
                })
                ->orWhereHas('animal', function ($sub) use ($busqueda) {
                    $sub->where('nombre', 'like', "%{$busqueda}%")
                        ->orWhere('codigo_unico', 'like', "%{$busqueda}%");
                });
            });
        }

        $orderBy = $filters['order_by'] ?? 'fecha_solicitud';
        $orderDir = $filters['order_dir'] ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($perPage);
    }
}
