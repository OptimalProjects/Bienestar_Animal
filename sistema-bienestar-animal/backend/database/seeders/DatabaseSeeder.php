<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Ejecutar con: php artisan db:seed
     * Ejecutar seeder especifico: php artisan db:seed --class=NombreSeeder
     * Resetear y poblar: php artisan migrate:fresh --seed
     */
    public function run(): void
    {
        $this->call([
            // 1. Configuracion base del sistema
            RolSeeder::class,
            PermisoSeeder::class,
            UsuarioSeeder::class,

            // 2. Catalogos
            TipoVacunaSeeder::class,
            VeterinariosSeeder::class,

            // 3. Datos de animales (~50 registros con historial clinico)
            AnimalSeeder::class,

            // 4. Consultas veterinarias (~30) y vacunas
            ConsultaSeeder::class,

            // 5. Denuncias (~15) con denunciantes
            DenunciaSeeder::class,

            // 6. Adopciones (~20) con adoptantes y visitas
            AdopcionSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('  Seeders ejecutados exitosamente!');
        $this->command->info('========================================');
        $this->command->info('');
        $this->command->info('Datos creados:');
        $this->command->info('  ~50 animales (perros, gatos, otros)');
        $this->command->info('  ~30 consultas veterinarias + vacunas');
        $this->command->info('  ~15 denuncias con denunciantes');
        $this->command->info('  ~20 adopciones con adoptantes y visitas');
        $this->command->info('  2 veterinarios vinculados a usuarios');
        $this->command->info('');
        $this->command->info('Usuarios de prueba (password: Cali2025*):');
        $this->command->info('  admin        (Administrador)');
        $this->command->info('  director     (Director)');
        $this->command->info('  coordinador  (Coordinador)');
        $this->command->info('  vet.garcia   (Veterinario)');
        $this->command->info('  vet.ramirez  (Veterinario)');
        $this->command->info('  aux.lopez    (Auxiliar Vet)');
        $this->command->info('  op.torres    (Operador)');
        $this->command->info('  op.moreno    (Operador)');
        $this->command->info('  eval.castro  (Evaluador)');
        $this->command->info('');
    }
}
