<?php

namespace App\Events;

use App\Models\Veterinaria\Consulta;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConsultaCreada
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Consulta $consulta;
    public ?string $userId;
    public ?string $traceId;

    public function __construct(Consulta $consulta, ?string $userId = null, ?string $traceId = null)
    {
        $this->consulta = $consulta;
        $this->userId = $userId;
        $this->traceId = $traceId;
    }
}
