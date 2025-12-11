<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface AdopcionRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Obtener adopciones por adoptante.
     */
    public function findByAdoptante(string $adoptanteId): Collection;

    /**
     * Obtener adopciones por animal.
     */
    public function findByAnimal(string $animalId): Collection;

    /**
     * Obtener adopciones por estado.
     */
    public function findByEstado(string $estado): Collection;

    /**
     * Obtener adopciones pendientes de evaluacion.
     */
    public function getPendientesEvaluacion(): Collection;

    /**
     * Obtener adopciones que requieren visita de seguimiento.
     */
    public function getRequierenVisita(): Collection;

    /**
     * Obtener adopcion con visitas de seguimiento.
     */
    public function getWithVisitas(string $id);

    /**
     * Obtener estadisticas de adopciones.
     */
    public function getEstadisticas(): array;
}
