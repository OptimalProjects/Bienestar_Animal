<?php

namespace App\Events;

use App\Models\Denuncias\Denuncia;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DenunciaRecibida
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Denuncia $denuncia;
    public ?string $traceId;

    public function __construct(Denuncia $denuncia, ?string $traceId = null)
    {
        $this->denuncia = $denuncia;
        $this->traceId = $traceId;
    }
}
