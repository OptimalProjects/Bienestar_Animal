<?php

namespace App\Mail;

use App\Models\Adopcion\Devolucion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Correo para notificar al adoptante que la devolución ha sido registrada.
 */
class DevolucionRegistradaMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * La devolución registrada
     */
    public Devolucion $devolucion;

    /**
     * Ruta del PDF de resumen
     */
    protected ?string $pdfPath;

    /**
     * Create a new message instance.
     */
    public function __construct(Devolucion $devolucion, ?string $pdfPath = null)
    {
        $this->devolucion = $devolucion;
        $this->pdfPath = $pdfPath;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $this->devolucion->load(['adopcion.adoptante', 'animal', 'registradoPor']);

        $animalNombre = $this->devolucion->animal->nombre ?? $this->devolucion->animal->codigo_unico;
        $adoptante = $this->devolucion->adopcion->adoptante;

        $mail = $this->subject("Confirmación de Devolución - {$animalNombre}")
            ->view('emails.devolucion-registrada')
            ->with([
                'devolucion' => $this->devolucion,
                'animal' => $this->devolucion->animal,
                'adoptante' => $adoptante,
                'adopcion' => $this->devolucion->adopcion,
                'motivoTexto' => $this->devolucion->motivo_texto,
                'estadoAnimalTexto' => $this->devolucion->estado_animal_texto,
            ]);

        // Adjuntar PDF si existe
        if ($this->pdfPath && Storage::disk('public')->exists($this->pdfPath)) {
            $fullPath = Storage::disk('public')->path($this->pdfPath);
            $nombreLimpio = preg_replace('/[^a-zA-Z0-9_-]/', '_', $animalNombre);
            $fileName = "Resumen_Devolucion_{$nombreLimpio}.pdf";

            if (file_exists($fullPath)) {
                $mail->attach($fullPath, [
                    'as' => $fileName,
                    'mime' => 'application/pdf',
                ]);
            }
        }

        return $mail;
    }
}
