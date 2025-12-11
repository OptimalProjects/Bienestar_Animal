<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface ConsultaRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Obtener consultas por animal.
     */
    public function findByAnimal(string $animalId): Collection;

    /**
     * Obtener consultas por veterinario.
     */
    public function findByVeterinario(string $veterinarioId): Collection;

    /**
     * Obtener consultas del dia.
     */
    public function getConsultasDelDia(): Collection;

    /**
     * Obtener consultas pendientes.
     */
    public function getPendientes(): Collection;

    /**
     * Obtener consultas por rango de fechas.
     */
    public function findByRangoFechas(string $fechaInicio, string $fechaFin): Collection;

    /**
     * Obtener consulta con tratamientos.
     */
    public function getWithTratamientos(string $id);
}
