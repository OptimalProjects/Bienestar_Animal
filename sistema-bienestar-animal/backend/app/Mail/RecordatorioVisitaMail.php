<?php

namespace App\Mail;

use App\Models\Adopcion\VisitaDomiciliaria;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Correo de recordatorio de visita de seguimiento próxima.
 */
class RecordatorioVisitaMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * La visita programada
     */
    public VisitaDomiciliaria $visita;

    /**
     * Días restantes para la visita
     */
    public int $diasRestantes;

    /**
     * Create a new message instance.
     */
    public function __construct(VisitaDomiciliaria $visita, int $diasRestantes = 0)
    {
        $this->visita = $visita;
        $this->diasRestantes = $diasRestantes;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $nombreAnimal = $this->visita->adopcion->animal->nombre ?? $this->visita->adopcion->animal->codigo_unico;
        $tipoVisita = $this->getTipoVisitaTexto($this->visita->tipo_visita);

        $asunto = $this->diasRestantes === 0
            ? "Recordatorio: Visita de Seguimiento HOY - {$nombreAnimal}"
            : "Recordatorio: Visita de Seguimiento en {$this->diasRestantes} día(s) - {$nombreAnimal}";

        return $this->subject($asunto)
            ->view('emails.recordatorio-visita')
            ->with([
                'visita' => $this->visita,
                'adopcion' => $this->visita->adopcion,
                'adoptante' => $this->visita->adopcion->adoptante,
                'animal' => $this->visita->adopcion->animal,
                'tipoVisitaTexto' => $tipoVisita,
                'diasRestantes' => $this->diasRestantes,
            ]);
    }

    /**
     * Obtener texto legible del tipo de visita.
     */
    protected function getTipoVisitaTexto(string $tipo): string
    {
        return match($tipo) {
            'pre_adopcion' => 'Pre-adopción',
            'seguimiento_1mes' => 'Seguimiento 1 mes',
            'seguimiento_3meses' => 'Seguimiento 3 meses',
            'seguimiento_6meses' => 'Seguimiento 6 meses',
            'extraordinaria' => 'Extraordinaria',
            default => ucfirst(str_replace('_', ' ', $tipo)),
        };
    }
}
