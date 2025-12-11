<?php

namespace App\Events;

use App\Models\Denuncias\Rescate;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RescateRegistrado
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Rescate $rescate;
    public ?string $userId;
    public ?string $traceId;

    public function __construct(Rescate $rescate, ?string $userId = null, ?string $traceId = null)
    {
        $this->rescate = $rescate;
        $this->userId = $userId;
        $this->traceId = $traceId;
    }
}
