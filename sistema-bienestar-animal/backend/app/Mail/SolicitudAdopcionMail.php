<?php

namespace App\Mail;

use App\Models\Adopcion\Adopcion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * En desarrollo: No implementa ShouldQueue para envío síncrono inmediato.
 * En producción: Descomentar "implements ShouldQueue" para envío asíncrono.
 */
class SolicitudAdopcionMail extends Mailable // implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * La adopcion que se está notificando
     */
    public Adopcion $adopcion;

    /**
     * Tipo de notificación: 'nueva', 'aprobada', 'rechazada', 'completada'
     */
    public string $tipo;

    /**
     * Create a new message instance.
     */
    public function __construct(Adopcion $adopcion, string $tipo = 'nueva')
    {
        $this->adopcion = $adopcion;
        $this->tipo = $tipo;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subjects = [
            'nueva' => 'Nueva Solicitud de Adopción - ' . $this->adopcion->animal->nombre,
            'aprobada' => 'Solicitud de Adopción Aprobada - ' . $this->adopcion->animal->nombre,
            'rechazada' => 'Solicitud de Adopción Rechazada',
            'completada' => 'Adopción Completada - ' . $this->adopcion->animal->nombre,
        ];

        return new Envelope(
            subject: $subjects[$this->tipo] ?? 'Notificación de Adopción',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.solicitud-adopcion',
            with: [
                'adopcion' => $this->adopcion,
                'tipo' => $this->tipo,
                'animal' => $this->adopcion->animal,
                'adoptante' => $this->adopcion->adoptante,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
