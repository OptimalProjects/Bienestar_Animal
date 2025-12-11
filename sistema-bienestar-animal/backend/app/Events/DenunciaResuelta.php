<?php

namespace App\Events;

use App\Models\Denuncias\Denuncia;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DenunciaResuelta
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Denuncia $denuncia;
    public string $resolucion;
    public ?string $userId;
    public ?string $traceId;

    public function __construct(Denuncia $denuncia, string $resolucion, ?string $userId = null, ?string $traceId = null)
    {
        $this->denuncia = $denuncia;
        $this->resolucion = $resolucion;
        $this->userId = $userId;
        $this->traceId = $traceId;
    }
}
