<?php

namespace App\Mail;

use App\Models\Adopcion\Adopcion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * Correo para notificar al adoptante las fechas de visitas de seguimiento programadas.
 */
class VisitasProgramadasMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * La adopción
     */
    public Adopcion $adopcion;

    /**
     * Las visitas programadas
     */
    public Collection $visitas;

    /**
     * Create a new message instance.
     */
    public function __construct(Adopcion $adopcion, Collection $visitas)
    {
        $this->adopcion = $adopcion;
        $this->visitas = $visitas;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $nombreAnimal = $this->adopcion->animal->nombre ?? $this->adopcion->animal->codigo_unico;

        return $this->subject("Visitas de Seguimiento Programadas - {$nombreAnimal}")
            ->view('emails.visitas-programadas')
            ->with([
                'adopcion' => $this->adopcion,
                'adoptante' => $this->adopcion->adoptante,
                'animal' => $this->adopcion->animal,
                'visitas' => $this->visitas,
            ]);
    }
}
