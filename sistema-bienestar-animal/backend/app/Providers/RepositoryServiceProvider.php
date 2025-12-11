<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Repository Contracts
use App\Repositories\Contracts\AnimalRepositoryInterface;
use App\Repositories\Contracts\ConsultaRepositoryInterface;
use App\Repositories\Contracts\AdopcionRepositoryInterface;
use App\Repositories\Contracts\DenunciaRepositoryInterface;

// Repository Implementations
use App\Repositories\Eloquent\AnimalRepository;
use App\Repositories\Eloquent\ConsultaRepository;
use App\Repositories\Eloquent\AdopcionRepository;
use App\Repositories\Eloquent\DenunciaRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Todos los bindings de repositorios.
     */
    protected array $repositories = [
        AnimalRepositoryInterface::class => AnimalRepository::class,
        ConsultaRepositoryInterface::class => ConsultaRepository::class,
        AdopcionRepositoryInterface::class => AdopcionRepository::class,
        DenunciaRepositoryInterface::class => DenunciaRepository::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        foreach ($this->repositories as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
