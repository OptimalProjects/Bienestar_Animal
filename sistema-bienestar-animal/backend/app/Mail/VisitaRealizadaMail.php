<?php

namespace App\Mail;

use App\Models\Adopcion\VisitaDomiciliaria;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Correo de notificación de visita de seguimiento realizada con resumen.
 */
class VisitaRealizadaMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * La visita realizada
     */
    public VisitaDomiciliaria $visita;

    /**
     * Ruta del PDF del resumen (opcional)
     */
    public ?string $pdfPath;

    /**
     * Create a new message instance.
     */
    public function __construct(VisitaDomiciliaria $visita, ?string $pdfPath = null)
    {
        $this->visita = $visita;
        $this->pdfPath = $pdfPath;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $nombreAnimal = $this->visita->adopcion->animal->nombre ?? $this->visita->adopcion->animal->codigo_unico;
        $tipoVisita = $this->getTipoVisitaTexto($this->visita->tipo_visita);

        $mail = $this->subject("Resumen de Visita de Seguimiento - {$nombreAnimal}")
            ->view('emails.visita-realizada')
            ->with([
                'visita' => $this->visita,
                'adopcion' => $this->visita->adopcion,
                'adoptante' => $this->visita->adopcion->adoptante,
                'animal' => $this->visita->adopcion->animal,
                'tipoVisitaTexto' => $tipoVisita,
                'resultadoTexto' => $this->getResultadoTexto($this->visita->resultado),
            ]);

        // Adjuntar PDF si existe
        if ($this->pdfPath && Storage::disk('public')->exists($this->pdfPath)) {
            $mail->attach(Storage::disk('public')->path($this->pdfPath), [
                'as' => 'resumen_visita_' . $this->visita->id . '.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
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

    /**
     * Obtener texto legible del resultado.
     */
    protected function getResultadoTexto(?string $resultado): string
    {
        return match($resultado) {
            'satisfactoria' => 'Satisfactoria',
            'observaciones' => 'Con Observaciones',
            'critica' => 'Crítica',
            default => ucfirst($resultado ?? 'No especificado'),
        };
    }
}
