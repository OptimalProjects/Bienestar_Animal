<?php

namespace App\Events;

use App\Models\Animal;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnimalCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Animal $animal;
    public ?string $userId;
    public ?string $traceId;

    public function __construct(Animal $animal, ?string $userId = null, ?string $traceId = null)
    {
        $this->animal = $animal;
        $this->userId = $userId;
        $this->traceId = $traceId;
    }
}
