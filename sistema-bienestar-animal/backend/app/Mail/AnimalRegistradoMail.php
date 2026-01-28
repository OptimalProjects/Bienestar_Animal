<?php

namespace App\Mail;

use App\Models\Animal\Animal;
use App\Models\Veterinaria\Veterinario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Correo de notificación a veterinarios cuando se registra un nuevo animal.
 */
class AnimalRegistradoMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * El animal registrado
     */
    public Animal $animal;

    /**
     * El veterinario destinatario
     */
    public ?Veterinario $veterinario;

    /**
     * Create a new message instance.
     */
    public function __construct(Animal $animal, ?Veterinario $veterinario = null)
    {
        $this->animal = $animal;
        $this->veterinario = $veterinario;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $nombreAnimal = $this->animal->nombre ?? $this->animal->codigo_unico;

        return $this->subject("Nuevo Animal Registrado - {$nombreAnimal}")
            ->view('emails.animal-registrado')
            ->with([
                'animal' => $this->animal,
                'veterinario' => $this->veterinario,
            ]);
    }
}
