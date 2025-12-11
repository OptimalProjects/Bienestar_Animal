<?php

namespace App\Events;

use App\Models\Veterinaria\Cirugia;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CirugiaRealizada
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Cirugia $cirugia;
    public ?string $userId;
    public ?string $traceId;

    public function __construct(Cirugia $cirugia, ?string $userId = null, ?string $traceId = null)
    {
        $this->cirugia = $cirugia;
        $this->userId = $userId;
        $this->traceId = $traceId;
    }
}
