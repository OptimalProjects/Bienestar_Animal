<?php

namespace App\Providers;

use App\Events\AdopcionAprobada;
use App\Events\AdopcionRechazada;
use App\Events\AnimalCreated;
use App\Events\AnimalUpdated;
use App\Events\CirugiaRealizada;
use App\Events\ConsultaCreada;
use App\Events\DenunciaAsignada;
use App\Events\DenunciaRecibida;
use App\Events\DenunciaResuelta;
use App\Events\RescateRegistrado;
use App\Events\UserLoggedIn;
use App\Events\UserLoggedOut;
use App\Events\VacunaAplicada;
use App\Listeners\LogAuditEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Animal events
        AnimalCreated::class => [
            [LogAuditEvent::class, 'handleAnimalCreated'],
        ],
        AnimalUpdated::class => [
            [LogAuditEvent::class, 'handleAnimalUpdated'],
        ],

        // Adopcion events
        AdopcionAprobada::class => [
            [LogAuditEvent::class, 'handleAdopcionAprobada'],
        ],
        AdopcionRechazada::class => [
            [LogAuditEvent::class, 'handleAdopcionRechazada'],
        ],

        // Denuncia events
        DenunciaRecibida::class => [
            [LogAuditEvent::class, 'handleDenunciaRecibida'],
        ],
        DenunciaAsignada::class => [
            [LogAuditEvent::class, 'handleDenunciaAsignada'],
        ],
        DenunciaResuelta::class => [
            [LogAuditEvent::class, 'handleDenunciaResuelta'],
        ],

        // User events
        UserLoggedIn::class => [
            [LogAuditEvent::class, 'handleUserLoggedIn'],
        ],
        UserLoggedOut::class => [
            [LogAuditEvent::class, 'handleUserLoggedOut'],
        ],

        // Veterinaria events
        ConsultaCreada::class => [
            [LogAuditEvent::class, 'handleConsultaCreada'],
        ],
        VacunaAplicada::class => [
            [LogAuditEvent::class, 'handleVacunaAplicada'],
        ],
        CirugiaRealizada::class => [
            [LogAuditEvent::class, 'handleCirugiaRealizada'],
        ],

        // Rescate events
        RescateRegistrado::class => [
            [LogAuditEvent::class, 'handleRescateRegistrado'],
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
