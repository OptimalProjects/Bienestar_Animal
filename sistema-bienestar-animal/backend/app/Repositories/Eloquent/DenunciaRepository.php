<?php

namespace App\Repositories\Eloquent;

use App\Models\Denuncia\Denuncia;
use App\Repositories\Contracts\DenunciaRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class DenunciaRepository extends BaseRepository implements DenunciaRepositoryInterface
{
    /**
     * Constructor.
     */
    public function __construct(Denuncia $model)
    {
        parent::__construct($model);
    }

    /**
     * Buscar denuncia por numero de ticket.
     */
    public function findByTicket(string $ticket)
    {
        return $this->model
            ->where('numero_ticket', $ticket)
            ->with(['denunciante', 'rescates', 'responsable'])
            ->first();
    }

    /**
     * Obtener denuncias por prioridad.
     */
    public function findByPrioridad(string $prioridad): Collection
    {
        return $this->model
            ->where('prioridad', $prioridad)
            ->with(['denunciante', 'asignadoA'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Obtener denuncias por estado.
     */
    public function findByEstado(string $estado): Collection
    {
        return $this->model
            ->where('estado', $estado)
            ->with(['denunciante', 'asignadoA'])
            ->orderBy('prioridad_orden')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Obtener denuncias urgentes sin asignar.
     */
    public function getUrgentesSinAsignar(): Collection
    {
        return $this->model
            ->urgentes()
            ->sinAsignar()
            ->with('denunciante')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Obtener denuncias asignadas a un usuario.
     */
    public function findByAsignadoA(string $usuarioId): Collection
    {
        return $this->model
            ->where('asignado_a', $usuarioId)
            ->with(['denunciante', 'rescates'])
            ->orderBy('prioridad_orden')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Obtener denuncia con rescates.
     */
    public function getWithRescates(string $id)
    {
        return $this->model
            ->with([
                'denunciante',
                'rescates',
                'rescates.animalRescatado',
                'asignadoA',
            ])
            ->findOrFail($id);
    }

    /**
     * Obtener estadisticas de denuncias.
     */
    public function getEstadisticas(): array
    {
        $inicioMes = now()->startOfMonth();
        $finMes = now()->endOfMonth();

        return [
            'total_pendientes' => $this->model->whereIn('estado', ['recibida', 'en_proceso'])->count(),
            'urgentes_sin_asignar' => $this->model->urgentes()->sinAsignar()->count(),
            'recibidas_mes' => $this->model
                ->whereBetween('created_at', [$inicioMes, $finMes])
                ->count(),
            'resueltas_mes' => $this->model
                ->where('estado', 'resuelta')
                ->whereBetween('fecha_resolucion', [$inicioMes, $finMes])
                ->count(),
            'por_prioridad' => $this->model
                ->whereIn('estado', ['recibida', 'en_proceso'])
                ->selectRaw('prioridad, count(*) as cantidad')
                ->groupBy('prioridad')
                ->pluck('cantidad', 'prioridad')
                ->toArray(),
            'por_tipo' => $this->model
                ->selectRaw('tipo_denuncia, count(*) as cantidad')
                ->groupBy('tipo_denuncia')
                ->pluck('cantidad', 'tipo_denuncia')
                ->toArray(),
            'tiempo_promedio_resolucion' => $this->calcularTiempoPromedioResolucion(),
        ];
    }

    /**
     * Calcular tiempo promedio de resolucion en dias.
     */
    protected function calcularTiempoPromedioResolucion(): ?float
    {
        $denuncias = $this->model
            ->where('estado', 'resuelta')
            ->whereNotNull('fecha_resolucion')
            ->where('fecha_resolucion', '>=', now()->subMonths(3))
            ->get();

        if ($denuncias->isEmpty()) {
            return null;
        }

        $totalDias = $denuncias->sum(function ($denuncia) {
            return $denuncia->created_at->diffInDays($denuncia->fecha_resolucion);
        });

        return round($totalDias / $denuncias->count(), 1);
    }

    /**
     * Obtener denuncias por ubicacion (comuna/barrio).
     */
    public function findByUbicacion(string $comuna, ?string $barrio = null): Collection
    {
        $query = $this->model->where('comuna', $comuna);

        if ($barrio) {
            $query->where('barrio', $barrio);
        }

        return $query
            ->with(['denunciante', 'asignadoA'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtener denuncias con paginacion y filtros.
     */
    public function paginateWithFilters(int $perPage, array $filters = [])
    {
        $query = $this->model->query()
            ->with(['denunciante', 'asignadoA']);

        if (!empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (!empty($filters['prioridad'])) {
            $query->where('prioridad', $filters['prioridad']);
        }

        if (!empty($filters['tipo_denuncia'])) {
            $query->where('tipo_denuncia', $filters['tipo_denuncia']);
        }

        if (!empty($filters['asignado_a'])) {
            $query->where('asignado_a', $filters['asignado_a']);
        }

        if (!empty($filters['comuna'])) {
            $query->where('comuna', $filters['comuna']);
        }

        if (!empty($filters['fecha_desde'])) {
            $query->whereDate('created_at', '>=', $filters['fecha_desde']);
        }

        if (!empty($filters['fecha_hasta'])) {
            $query->whereDate('created_at', '<=', $filters['fecha_hasta']);
        }

        if (!empty($filters['busqueda'])) {
            $busqueda = $filters['busqueda'];
            $query->where(function ($q) use ($busqueda) {
                $q->where('numero_ticket', 'like', "%{$busqueda}%")
                  ->orWhere('descripcion', 'like', "%{$busqueda}%")
                  ->orWhere('ubicacion', 'like', "%{$busqueda}%");
            });
        }

        // Ordenar por prioridad y fecha
        $query->orderByRaw("FIELD(prioridad, 'urgente', 'alta', 'media', 'baja')")
              ->orderBy('created_at', 'asc');

        return $query->paginate($perPage);
    }

    /**
     * Obtener mapa de calor de denuncias por comuna.
     */
    public function getMapaCalorPorComuna(): array
    {
        return $this->model
            ->selectRaw('comuna, count(*) as cantidad')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('comuna')
            ->pluck('cantidad', 'comuna')
            ->toArray();
    }
}
