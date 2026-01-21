<?php

namespace App\Mail;

use App\Models\Denuncia\Denuncia;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Correo de notificación para denuncias.
 * Tipos: 'nueva', 'asignada', 'en_atencion', 'resuelta', 'cerrada'
 */
class DenunciaMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * La denuncia que se está notificando
     */
    public Denuncia $denuncia;

    /**
     * Tipo de notificación
     */
    public string $tipo;

    /**
     * Create a new message instance.
     */
    public function __construct(Denuncia $denuncia, string $tipo = 'nueva')
    {
        $this->denuncia = $denuncia;
        $this->tipo = $tipo;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subjects = [
            'nueva' => "Nueva Denuncia Asignada - {$this->denuncia->numero_ticket}",
            'asignada' => "Denuncia Asignada - {$this->denuncia->numero_ticket}",
            'en_atencion' => "Denuncia en Atención - {$this->denuncia->numero_ticket}",
            'resuelta' => "Denuncia Resuelta - {$this->denuncia->numero_ticket}",
            'cerrada' => "Denuncia Cerrada - {$this->denuncia->numero_ticket}",
        ];

        $subject = $subjects[$this->tipo] ?? "Actualización de Denuncia - {$this->denuncia->numero_ticket}";

        return $this->subject($subject)
            ->view('emails.denuncia')
            ->with([
                'denuncia' => $this->denuncia,
                'denunciante' => $this->denuncia->denunciante,
                'responsable' => $this->denuncia->responsable,
                'tipo' => $this->tipo,
            ]);
    }
}
