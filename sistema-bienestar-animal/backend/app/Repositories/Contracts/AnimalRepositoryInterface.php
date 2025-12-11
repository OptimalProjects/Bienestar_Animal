<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface AnimalRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Buscar animales por especie.
     */
    public function findByEspecie(string $especie): Collection;

    /**
     * Buscar animales por estado.
     */
    public function findByEstado(string $estado): Collection;

    /**
     * Obtener animales disponibles para adopcion.
     */
    public function getDisponiblesAdopcion(): Collection;

    /**
     * Buscar animal por numero de chip.
     */
    public function findByChip(string $chip);

    /**
     * Obtener animales con historial clinico.
     */
    public function getWithHistorialClinico(string $id);

    /**
     * Obtener estadisticas de animales.
     */
    public function getEstadisticas(): array;

    /**
     * Buscar animales por ubicacion.
     */
    public function findByUbicacion(string $ubicacion): Collection;
}
