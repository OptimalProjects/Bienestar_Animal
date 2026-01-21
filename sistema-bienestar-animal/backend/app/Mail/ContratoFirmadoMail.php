<?php

namespace App\Mail;

use App\Models\Adopcion\Adopcion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Correo con el contrato de adopción firmado adjunto.
 */
class ContratoFirmadoMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * La adopción con el contrato firmado
     */
    public Adopcion $adopcion;

    /**
     * Ruta del contrato para adjuntar
     */
    protected ?string $contratoPath;

    /**
     * Create a new message instance.
     */
    public function __construct(Adopcion $adopcion)
    {
        $this->adopcion = $adopcion;
        // Guardar la ruta del contrato en el constructor para evitar problemas de serialización
        $this->contratoPath = $adopcion->contrato_url;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $animalNombre = $this->adopcion->animal->nombre ?? $this->adopcion->animal->codigo_unico;

        $mail = $this->subject("Contrato de Adopción Firmado - {$animalNombre}")
            ->view('emails.contrato-firmado')
            ->with([
                'adopcion' => $this->adopcion,
                'animal' => $this->adopcion->animal,
                'adoptante' => $this->adopcion->adoptante,
            ]);

        // Adjuntar el contrato firmado si existe
        $contratoUrl = $this->contratoPath ?? $this->adopcion->contrato_url;

        Log::info('ContratoFirmadoMail::build - Verificando adjunto', [
            'contrato_url' => $contratoUrl,
            'existe' => $contratoUrl ? Storage::disk('public')->exists($contratoUrl) : false,
        ]);

        if ($contratoUrl && Storage::disk('public')->exists($contratoUrl)) {
            // Limpiar nombre de archivo para evitar caracteres problemáticos
            $nombreLimpio = preg_replace('/[^a-zA-Z0-9_-]/', '_', $animalNombre);
            $fileName = "Contrato_Adopcion_{$nombreLimpio}.pdf";

            // Obtener la ruta absoluta del archivo
            $fullPath = Storage::disk('public')->path($contratoUrl);

            Log::info('ContratoFirmadoMail::build - Adjuntando contrato', [
                'fileName' => $fileName,
                'fullPath' => $fullPath,
                'fileExists' => file_exists($fullPath),
                'fileSize' => file_exists($fullPath) ? filesize($fullPath) : 0,
            ]);

            if (file_exists($fullPath)) {
                $mail->attach($fullPath, [
                    'as' => $fileName,
                    'mime' => 'application/pdf',
                ]);
            }
        } else {
            Log::warning('ContratoFirmadoMail::build - No se encontró contrato para adjuntar', [
                'contrato_url' => $contratoUrl,
            ]);
        }

        return $mail;
    }
}
