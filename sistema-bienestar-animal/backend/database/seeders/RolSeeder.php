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
                'codigo' => 'VETERINARIO',
                'nombre' => 'Veterinario',
                'descripcion' => 'Gestión de atención veterinaria',
                'modulo' => 'veterinaria',
                'requiere_mfa' => false,
                'activo' => true,
            ],
            [
                'id' => (string) Str::uuid(),
                'codigo' => 'ADOPTANTE',
                'nombre' => 'Adoptante',
                'descripcion' => 'Usuario que solicita adopciones',
                'modulo' => 'adopciones',
                'requiere_mfa' => false,
                'activo' => true,
            ],
            [
                'id' => (string) Str::uuid(),
                'codigo' => 'OPERADOR',
                'nombre' => 'Operador de Rescate',
                'descripcion' => 'Gestión de rescates y denuncias',
                'modulo' => 'denuncias',
                'requiere_mfa' => false,
                'activo' => true,
            ],
        ];

        foreach ($roles as $rol) {
            Rol::create($rol);
        }
    }
}