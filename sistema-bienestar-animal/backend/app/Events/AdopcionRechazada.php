<?php

namespace App\Events;

use App\Models\Adopciones\SolicitudAdopcion;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdopcionRechazada
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public SolicitudAdopcion $solicitud;
    public string $motivo;
    public ?string $userId;
    public ?string $traceId;

    public function __construct(SolicitudAdopcion $solicitud, string $motivo = '', ?string $userId = null, ?string $traceId = null)
    {
        $this->solicitud = $solicitud;
        $this->motivo = $motivo;
        $this->userId = $userId;
        $this->traceId = $traceId;
    }
}
