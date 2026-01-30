<?php

namespace App\Mail;

use App\Models\Denuncia\Rescate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Correo de notificación al denunciante sobre el resultado del operativo.
 * Solo se envía si la denuncia NO fue anónima y el denunciante tiene email.
 */
class ResultadoOperativoMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * El rescate/operativo ejecutado
     */
    public Rescate $rescate;

    /**
     * Create a new message instance.
     */
    public function __construct(Rescate $rescate)
    {
        $this->rescate = $rescate;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        // Cargar relaciones necesarias
        $this->rescate->load(['denuncia.denunciante', 'animalRescatado']);

        $denuncia = $this->rescate->denuncia;
        $denunciante = $denuncia->denunciante;
        $exitoso = $this->rescate->exitoso;

        // Determinar el asunto según el resultado
        $resultado = $exitoso ? 'Exitoso' : 'Concluido';
        $subject = "Resultado del Operativo - {$denuncia->numero_ticket} - {$resultado}";

        return $this->subject($subject)
            ->view('emails.resultado-operativo')
            ->with([
                'rescate' => $this->rescate,
                'denuncia' => $denuncia,
                'denunciante' => $denunciante,
                'animalRescatado' => $this->rescate->animalRescatado,
                'exitoso' => $exitoso,
            ]);
    }
}
