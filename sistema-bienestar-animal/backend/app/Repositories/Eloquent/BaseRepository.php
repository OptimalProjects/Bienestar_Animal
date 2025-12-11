<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    /**
     * Constructor.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Obtener todos los registros.
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Obtener todos los registros paginados.
     */
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->query();
        $query = $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    /**
     * Encontrar un registro por ID.
     */
    public function findById(string $id)
    {
        return $this->model->find($id);
    }

    /**
     * Encontrar un registro por ID o lanzar excepcion.
     */
    public function findByIdOrFail(string $id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Crear un nuevo registro.
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Actualizar un registro existente.
     */
    public function update(string $id, array $data)
    {
        $record = $this->findByIdOrFail($id);
        $record->update($data);

        return $record->fresh();
    }

    /**
     * Eliminar un registro (soft delete).
     */
    public function delete(string $id): bool
    {
        $record = $this->findByIdOrFail($id);

        return $record->delete();
    }

    /**
     * Eliminar un registro permanentemente.
     */
    public function forceDelete(string $id): bool
    {
        $record = $this->model->withTrashed()->findOrFail($id);

        return $record->forceDelete();
    }

    /**
     * Restaurar un registro eliminado.
     */
    public function restore(string $id)
    {
        $record = $this->model->withTrashed()->findOrFail($id);
        $record->restore();

        return $record;
    }

    /**
     * Aplicar filtros a la consulta.
     */
    protected function applyFilters($query, array $filters)
    {
        foreach ($filters as $field => $value) {
            if ($value !== null && $value !== '') {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }
        }

        return $query;
    }

    /**
     * Buscar registros por campo.
     */
    public function findBy(string $field, $value): Collection
    {
        return $this->model->where($field, $value)->get();
    }

    /**
     * Buscar primer registro por campo.
     */
    public function findFirstBy(string $field, $value)
    {
        return $this->model->where($field, $value)->first();
    }

    /**
     * Contar registros.
     */
    public function count(array $filters = []): int
    {
        $query = $this->model->query();
        $query = $this->applyFilters($query, $filters);

        return $query->count();
    }
}
