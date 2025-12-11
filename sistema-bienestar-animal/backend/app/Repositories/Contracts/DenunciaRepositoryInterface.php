<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface DenunciaRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Buscar denuncia por numero de ticket.
     */
    public function findByTicket(string $ticket);

    /**
     * Obtener denuncias por prioridad.
     */
    public function findByPrioridad(string $prioridad): Collection;

    /**
     * Obtener denuncias por estado.
     */
    public function findByEstado(string $estado): Collection;

    /**
     * Obtener denuncias urgentes sin asignar.
     */
    public function getUrgentesSinAsignar(): Collection;

    /**
     * Obtener denuncias asignadas a un usuario.
     */
    public function findByAsignadoA(string $usuarioId): Collection;

    /**
     * Obtener denuncia con rescates.
     */
    public function getWithRescates(string $id);

    /**
     * Obtener estadisticas de denuncias.
     */
    public function getEstadisticas(): array;

    /**
     * Obtener denuncias por ubicacion (comuna/barrio).
     */
    public function findByUbicacion(string $comuna, ?string $barrio = null): Collection;
}
