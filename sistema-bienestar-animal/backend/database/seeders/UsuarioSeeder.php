<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User\Usuario::create([
            'username' => 'admin',
            'nombres' => 'Admin',
            'apellidos' => 'Sistema',
            'email' => 'admin@sistema.com',
            'password_hash' => bcrypt('password'),  // Contraseña por defecto
            'activo' => true,
            'origen_autenticacion' => 'local',
        ]);
    }
}
