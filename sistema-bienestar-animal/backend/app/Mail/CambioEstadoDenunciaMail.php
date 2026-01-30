<?php

namespace App\Mail;

use App\Models\Denuncia\Denuncia;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Correo de notificación al denunciante sobre cambios de estado de su denuncia.
 * Solo se envía si la denuncia NO es anónima y el denunciante tiene email.
 *
 * Tipos de cambio: 'asignada', 'en_revision', 'en_atencion', 'resuelta', 'cerrada', 'desestimada'
 */
class CambioEstadoDenunciaMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * La denuncia que se está notificando
     */
    public Denuncia $denuncia;

    /**
     * El nuevo estado de la denuncia
     */
    public string $nuevoEstado;

    /**
     * El estado anterior (opcional)
     */
    public ?string $estadoAnterior;

    /**
     * Create a new message instance.
     */
    public function __construct(Denuncia $denuncia, string $nuevoEstado, ?string $estadoAnterior = null)
    {
        $this->denuncia = $denuncia;
        $this->nuevoEstado = $nuevoEstado;
        $this->estadoAnterior = $estadoAnterior;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        // Determinar asunto según el estado
        $subjects = [
            'recibida' => "Denuncia Recibida - {$this->denuncia->numero_ticket}",
            'en_revision' => "Su Denuncia está en Revisión - {$this->denuncia->numero_ticket}",
            'asignada' => "Se ha Asignado un Equipo a su Denuncia - {$this->denuncia->numero_ticket}",
            'en_atencion' => "Operativo en Curso - {$this->denuncia->numero_ticket}",
            'resuelta' => "Su Denuncia ha sido Resuelta - {$this->denuncia->numero_ticket}",
            'cerrada' => "Caso Cerrado - {$this->denuncia->numero_ticket}",
            'desestimada' => "Actualización de su Denuncia - {$this->denuncia->numero_ticket}",
        ];

        $subject = $subjects[$this->nuevoEstado] ?? "Actualización de su Denuncia - {$this->denuncia->numero_ticket}";

        // Cargar relaciones necesarias
        $this->denuncia->load(['denunciante', 'responsable', 'rescates']);

        return $this->subject($subject)
            ->view('emails.cambio-estado-denuncia')
            ->with([
                'denuncia' => $this->denuncia,
                'denunciante' => $this->denuncia->denunciante,
                'nuevoEstado' => $this->nuevoEstado,
                'estadoAnterior' => $this->estadoAnterior,
                'rescate' => $this->denuncia->rescates->last(),
            ]);
    }
}
