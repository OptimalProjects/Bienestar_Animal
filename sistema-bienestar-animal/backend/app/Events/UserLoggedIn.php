<?php

namespace App\Events;

use App\Models\User\Usuario;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserLoggedIn
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Usuario $user;
    public string $ipAddress;
    public string $userAgent;
    public ?string $traceId;

    public function __construct(Usuario $user, string $ipAddress, string $userAgent, ?string $traceId = null)
    {
        $this->user = $user;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->traceId = $traceId;
    }
}
