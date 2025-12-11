<?php

namespace App\Events;

use App\Models\Denuncias\Denuncia;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DenunciaAsignada
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Denuncia $denuncia;
    public string $funcionarioId;
    public ?string $userId;
    public ?string $traceId;

    public function __construct(Denuncia $denuncia, string $funcionarioId, ?string $userId = null, ?string $traceId = null)
    {
        $this->denuncia = $denuncia;
        $this->funcionarioId = $funcionarioId;
        $this->userId = $userId;
        $this->traceId = $traceId;
    }
}
