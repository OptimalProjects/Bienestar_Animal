<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Veterinaria\Veterinario;

class VeterinariosSeeder extends Seeder
{
    public function run(): void
    {
        Veterinario::firstOrCreate(
            ['email' => 'vet.demo@eligourmet.com'],
            [
                // usuario_id: si tu FK permite null en dev, déjalo null.
                // si NO permite null, debes crear un Usuario real y asignarlo.
                'usuario_id' => null,

                'nombres' => 'Ana María',
                'apellidos' => 'García',
                'numero_tarjeta_profesional' => 'VET-0001',
                'especialidad' => 'Medicina General',
                'telefono' => '3000000000',
                'email' => 'vet.demo@eligourmet.com',
                'activo' => true,
            ]
        );
    }
}
