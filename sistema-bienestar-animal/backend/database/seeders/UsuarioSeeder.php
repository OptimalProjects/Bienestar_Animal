<?php

namespace Database\Seeders;

use App\Models\User\Rol;
use App\Models\User\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usuarios = [
            // Administrador del sistema
            [
                'username' => 'admin',
                'nombres' => 'Carlos',
                'apellidos' => 'Rodriguez Gomez',
                'email' => 'admin@bienestaranimal.gov.co',
                'rol_codigo' => 'ADMIN',
            ],
            // Director del programa
            [
                'username' => 'director',
                'nombres' => 'Maria Elena',
                'apellidos' => 'Ospina Restrepo',
                'email' => 'director@bienestaranimal.gov.co',
                'rol_codigo' => 'DIRECTOR',
            ],
            // Coordinador
            [
                'username' => 'coordinador',
                'nombres' => 'Juan Pablo',
                'apellidos' => 'Martinez Lopez',
                'email' => 'coordinador@bienestaranimal.gov.co',
                'rol_codigo' => 'COORDINADOR',
            ],
            // Veterinarios
            [
                'username' => 'vet.garcia',
                'nombres' => 'Ana Maria',
                'apellidos' => 'Garcia Sanchez',
                'email' => 'ana.garcia@bienestaranimal.gov.co',
                'rol_codigo' => 'VETERINARIO',
            ],
            [
                'username' => 'vet.ramirez',
                'nombres' => 'Pedro',
                'apellidos' => 'Ramirez Castillo',
                'email' => 'pedro.ramirez@bienestaranimal.gov.co',
                'rol_codigo' => 'VETERINARIO',
            ],
            // Auxiliar Veterinario
            [
                'username' => 'aux.lopez',
                'nombres' => 'Laura',
                'apellidos' => 'Lopez Vargas',
                'email' => 'laura.lopez@bienestaranimal.gov.co',
                'rol_codigo' => 'AUXILIAR_VET',
            ],
            // Operadores de rescate
            [
                'username' => 'op.torres',
                'nombres' => 'Diego',
                'apellidos' => 'Torres Mendez',
                'email' => 'diego.torres@bienestaranimal.gov.co',
                'rol_codigo' => 'OPERADOR',
            ],
            [
                'username' => 'op.moreno',
                'nombres' => 'Sandra',
                'apellidos' => 'Moreno Rios',
                'email' => 'sandra.moreno@bienestaranimal.gov.co',
                'rol_codigo' => 'OPERADOR',
            ],
            // Evaluador de adopciones
            [
                'username' => 'eval.castro',
                'nombres' => 'Patricia',
                'apellidos' => 'Castro Herrera',
                'email' => 'patricia.castro@bienestaranimal.gov.co',
                'rol_codigo' => 'EVALUADOR',
            ],
        ];

        foreach ($usuarios as $userData) {
            $rol = Rol::where('codigo', $userData['rol_codigo'])->first();

            $usuario = Usuario::updateOrCreate(
                ['username' => $userData['username']],
                [
                    'id' => (string) Str::uuid(),
                    'username' => $userData['username'],
                    'nombres' => $userData['nombres'],
                    'apellidos' => $userData['apellidos'],
                    'email' => $userData['email'],
                    'password_hash' => bcrypt('Cali2025*'), // Password por defecto
                    'activo' => true,
                    'origen_autenticacion' => 'local',
                ]
            );

            // Asignar rol usando la tabla pivot con UUID
            if ($rol) {
                $usuario->roles()->detach();
                $usuario->roles()->attach($rol->id, [
                    'id' => (string) Str::uuid(),
                    'fecha_asignacion' => now(),
                    'activo' => true,
                ]);
            }
        }
    }
}
