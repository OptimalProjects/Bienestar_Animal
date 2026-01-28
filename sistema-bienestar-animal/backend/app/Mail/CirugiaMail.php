<?php

namespace App\Mail;

use App\Models\Veterinaria\Cirugia;
use App\Models\Adopcion\Adoptante;
use App\Models\Animal\Animal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Correo de notificación al adoptante sobre cirugía de su mascota.
 */
class CirugiaMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * La cirugía realizada
     */
    public Cirugia $cirugia;

    /**
     * El adoptante a notificar
     */
    public Adoptante $adoptante;

    /**
     * El animal operado
     */
    public Animal $animal;

    /**
     * Create a new message instance.
     */
    public function __construct(Cirugia $cirugia, Adoptante $adoptante, Animal $animal)
    {
        $this->cirugia = $cirugia;
        $this->adoptante = $adoptante;
        $this->animal = $animal;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $nombreAnimal = $this->animal->nombre ?? $this->animal->codigo_unico;
        $tipoCirugia = $this->getTipoCirugiaTexto($this->cirugia->tipo_cirugia);

        return $this->subject("Notificación de Cirugía - {$nombreAnimal}")
            ->view('emails.cirugia')
            ->with([
                'cirugia' => $this->cirugia,
                'adoptante' => $this->adoptante,
                'animal' => $this->animal,
                'tipoCirugiaTexto' => $tipoCirugia,
            ]);
    }

    /**
     * Obtener texto legible del tipo de cirugía.
     */
    protected function getTipoCirugiaTexto(?string $tipo): string
    {
        return match($tipo) {
            'esterilizacion' => 'Esterilización',
            'castracion' => 'Castración',
            'ortopedica' => 'Ortopédica',
            'abdominal' => 'Abdominal',
            'oftalmologica' => 'Oftalmológica',
            'dental' => 'Dental',
            'oncologica' => 'Oncológica',
            'emergencia' => 'Emergencia',
            'otra' => 'Otra',
            default => ucfirst($tipo ?? 'No especificada'),
        };
    }
}
