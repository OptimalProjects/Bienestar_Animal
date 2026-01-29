<?php

namespace App\Repositories\Eloquent;

use App\Models\Animal\Animal;
use App\Repositories\Contracts\AnimalRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AnimalRepository extends BaseRepository implements AnimalRepositoryInterface
{
    /**
     * Constructor.
     */
    public function __construct(Animal $model)
    {
        parent::__construct($model);
    }

    /**
     * Buscar animales por especie.
     */
    public function findByEspecie(string $especie): Collection
    {
        return $this->model->porEspecie($especie)->get();
    }

    /**
     * Buscar animales por estado.
     */
    public function findByEstado(string $estado): Collection
    {
        return $this->model->where('estado', $estado)->get();
    }

    /**
     * Obtener animales disponibles para adopcion.
     */
    public function getDisponiblesAdopcion(): Collection
    {
        return $this->model
            ->disponiblesAdopcion()
            ->saludable()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Buscar animal por numero de chip.
     */
    public function findByChip(string $chip)
    {
        return $this->model
            ->whereHas('historialClinico', function ($query) use ($chip) {
                $query->where('chip_id', $chip);
            })
            ->first();
    }

    /**
     * Obtener animales con historial clinico.
     */
    public function getWithHistorialClinico(string $id)
    {
        return $this->model
            ->with([
                'historialClinico',
                'historialClinico.consultas',
                'historialClinico.vacunas',
                'historialClinico.cirugias',
            ])
            ->findOrFail($id);
    }

    /**
     * Obtener estadisticas de animales.
     */
    public function getEstadisticas(): array
    {
        $total = $this->model->count();
        $porEstado = $this->model
            ->selectRaw('estado, count(*) as cantidad')
            ->groupBy('estado')
            ->pluck('cantidad', 'estado')
            ->toArray();

        $porEspecie = $this->model
            ->selectRaw('especie, count(*) as cantidad')
            ->groupBy('especie')
            ->pluck('cantidad', 'especie')
            ->toArray();

        $ingresosUltimoMes = $this->model
            ->where('created_at', '>=', now()->subMonth())
            ->count();

        $adopcionesUltimoMes = $this->model
            ->where('estado', 'adoptado')
            ->where('updated_at', '>=', now()->subMonth())
            ->count();

        return [
            'total' => $total,
            'por_estado' => $porEstado,
            'por_especie' => $porEspecie,
            'ingresos_ultimo_mes' => $ingresosUltimoMes,
            'adopciones_ultimo_mes' => $adopcionesUltimoMes,
        ];
    }

    /**
     * Buscar animales por ubicacion.
     */
    public function findByUbicacion(string $ubicacion): Collection
    {
        return $this->model
            ->where('ubicacion_rescate', 'like', "%{$ubicacion}%")
            ->get();
    }

    /**
     * Obtener animales con paginacion y filtros avanzados.
     */
    public function paginateWithFilters(int $perPage, array $filters = [])
    {
        $query = $this->model->query();

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

        if (!empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (!empty($filters['estado_salud'])) {
            $query->where('estado_salud', $filters['estado_salud']);
        }

        if (!empty($filters['sexo'])) {
            $query->where('sexo', $filters['sexo']);
        }

        if (!empty($filters['tamanio'])) {
            $query->where('tamanio', $filters['tamanio']);
        }

        if (!empty($filters['busqueda'])) {
            $busqueda = $filters['busqueda'];
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                  ->orWhere('codigo_unico', 'like', "%{$busqueda}%")
                  ->orWhere('raza', 'like', "%{$busqueda}%");
            });
        }

        if (!empty($filters['fecha_desde'])) {
            $query->whereDate('fecha_rescate', '>=', $filters['fecha_desde']);
        }

        if (!empty($filters['fecha_hasta'])) {
            $query->whereDate('fecha_rescate', '<=', $filters['fecha_hasta']);
        }

        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDir = $filters['order_dir'] ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($perPage);
    }
}
