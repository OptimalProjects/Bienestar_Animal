<?php

namespace Database\Seeders;

use App\Models\User\Usuario;
use App\Models\Veterinaria\Veterinario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VeterinariosSeeder extends Seeder
{
    public function run(): void
    {
        $veterinarios = [
            [
                'username' => 'vet.garcia',
                'nombres' => 'Ana Maria',
                'apellidos' => 'Garcia Sanchez',
                'numero_tarjeta_profesional' => 'VET-2024-0471',
                'especialidad' => 'Medicina General y Cirugia',
                'telefono' => '3154567890',
                'email' => 'ana.garcia@bienestaranimal.gov.co',
            ],
            [
                'username' => 'vet.ramirez',
                'nombres' => 'Pedro',
                'apellidos' => 'Ramirez Castillo',
                'numero_tarjeta_profesional' => 'VET-2023-0892',
                'especialidad' => 'Dermatologia y Oftalmologia',
                'telefono' => '3167890123',
                'email' => 'pedro.ramirez@bienestaranimal.gov.co',
            ],
        ];

        foreach ($veterinarios as $data) {
            $usuario = Usuario::where('username', $data['username'])->first();

            if (!$usuario) {
                continue;
            }

            Veterinario::updateOrCreate(
                ['usuario_id' => $usuario->id],
                [
                    'id' => (string) Str::uuid(),
                    'usuario_id' => $usuario->id,
                    'nombres' => $data['nombres'],
                    'apellidos' => $data['apellidos'],
                    'numero_tarjeta_profesional' => $data['numero_tarjeta_profesional'],
                    'especialidad' => $data['especialidad'],
                    'telefono' => $data['telefono'],
                    'email' => $data['email'],
                    'activo' => true,
                ]
            );
        }
    }
}
