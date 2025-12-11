<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    /**
     * Obtener todos los registros.
     */
    public function all(): Collection;

    /**
     * Obtener todos los registros paginados.
     */
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Encontrar un registro por ID.
     */
    public function findById(string $id);

    /**
     * Crear un nuevo registro.
     */
    public function create(array $data);

    /**
     * Actualizar un registro existente.
     */
    public function update(string $id, array $data);

    /**
     * Eliminar un registro (soft delete).
     */
    public function delete(string $id): bool;

    /**
     * Eliminar un registro permanentemente.
     */
    public function forceDelete(string $id): bool;
}
