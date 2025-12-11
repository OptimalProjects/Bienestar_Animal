<?php

namespace App\Events;

use App\Models\Animal;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnimalUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Animal $animal;
    public array $changes;
    public ?string $userId;
    public ?string $traceId;

    public function __construct(Animal $animal, array $changes = [], ?string $userId = null, ?string $traceId = null)
    {
        $this->animal = $animal;
        $this->changes = $changes;
        $this->userId = $userId;
        $this->traceId = $traceId;
    }
}
