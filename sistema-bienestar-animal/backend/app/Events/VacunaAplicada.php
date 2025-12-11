<?php

namespace App\Events;

use App\Models\Veterinaria\Vacuna;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VacunaAplicada
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Vacuna $vacuna;
    public ?string $userId;
    public ?string $traceId;

    public function __construct(Vacuna $vacuna, ?string $userId = null, ?string $traceId = null)
    {
        $this->vacuna = $vacuna;
        $this->userId = $userId;
        $this->traceId = $traceId;
    }
}
