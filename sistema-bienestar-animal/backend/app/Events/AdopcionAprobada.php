<?php

namespace App\Events;

use App\Models\Adopciones\SolicitudAdopcion;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdopcionAprobada
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public SolicitudAdopcion $solicitud;
    public ?string $userId;
    public ?string $traceId;

    public function __construct(SolicitudAdopcion $solicitud, ?string $userId = null, ?string $traceId = null)
    {
        $this->solicitud = $solicitud;
        $this->userId = $userId;
        $this->traceId = $traceId;
    }
}
