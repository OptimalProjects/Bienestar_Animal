<?php

namespace Database\Seeders;

use App\Models\User\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'id' => (string) Str::uuid(),
                'codigo' => 'ADMIN',
                'nombre' => 'Administrador',
                'descripcion' => 'Acceso total al sistema',
                'modulo' => 'general',
                'requiere_mfa' => true,
                'activo' => true,
            ],
            [
                'id' => (string) Str::uuid(),
                'codigo' => 'DIRECTOR',
                'nombre' => 'Director',
                'descripcion' => 'Director del programa de bienestar animal',
                'modulo' => 'general',
                'requiere_mfa' => true,
                'activo' => true,
            ],
            [
                'id' => (string) Str::uuid(),
                'codigo' => 'COORDINADOR',
                'nombre' => 'Coordinador',
                'descripcion' => 'Coordinador de operaciones',
                'modulo' => 'general',
                'requiere_mfa' => false,
                'activo' => true,
            ],
            [
                'id' => (string) Str::uuid(),
                'codigo' => 'VETERINARIO',
                'nombre' => 'Veterinario',
                'descripcion' => 'Gestion de atencion veterinaria',
                'modulo' => 'veterinaria',
                'requiere_mfa' => false,
                'activo' => true,
            ],
            [
                'id' => (string) Str::uuid(),
                'codigo' => 'AUXILIAR_VET',
                'nombre' => 'Auxiliar Veterinario',
                'descripcion' => 'Apoyo en atencion veterinaria',
                'modulo' => 'veterinaria',
                'requiere_mfa' => false,
                'activo' => true,
            ],
            [
                'id' => (string) Str::uuid(),
                'codigo' => 'OPERADOR',
                'nombre' => 'Operador de Rescate',
                'descripcion' => 'Gestion de rescates y denuncias',
                'modulo' => 'denuncias',
                'requiere_mfa' => false,
                'activo' => true,
            ],
            [
                'id' => (string) Str::uuid(),
                'codigo' => 'EVALUADOR',
                'nombre' => 'Evaluador de Adopciones',
                'descripcion' => 'Evaluacion de solicitudes de adopcion',
                'modulo' => 'adopciones',
                'requiere_mfa' => false,
                'activo' => true,
            ],
        ];

        foreach ($roles as $rol) {
            Rol::updateOrCreate(
                ['codigo' => $rol['codigo']],
                $rol
            );
        }
    }
}