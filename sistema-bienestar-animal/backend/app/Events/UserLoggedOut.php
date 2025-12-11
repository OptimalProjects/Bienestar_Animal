<?php

namespace App\Events;

use App\Models\User\Usuario;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserLoggedOut
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Usuario $user;
    public ?string $traceId;

    public function __construct(Usuario $user, ?string $traceId = null)
    {
        $this->user = $user;
        $this->traceId = $traceId;
    }
}
