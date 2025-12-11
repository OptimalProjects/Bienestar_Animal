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
     */
    public function run(): void
    {
        $this->call([
            // 1. Configuracion base del sistema
            RolSeeder::class,
            PermisoSeeder::class,
            UsuarioSeeder::class,

            // 2. Catalogos de veterinaria
            TipoVacunaSeeder::class,

            // 3. Datos de prueba (animales basicos)
            AnimalSeeder::class,

            // Nota: Los siguientes seeders requieren ajustes adicionales
            // para coincidir con el esquema de migraciones actual:
            // - ConsultaSeeder
            // - DenunciaSeeder
            // - AdopcionSeeder
        ]);

        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('  Seeders ejecutados exitosamente!');
        $this->command->info('========================================');
        $this->command->info('');
        $this->command->info('Usuarios de prueba creados:');
        $this->command->info('  admin / Cali2025* (Administrador)');
        $this->command->info('  director / Cali2025* (Director)');
        $this->command->info('  coordinador / Cali2025* (Coordinador)');
        $this->command->info('  vet.garcia / Cali2025* (Veterinario)');
        $this->command->info('  vet.ramirez / Cali2025* (Veterinario)');
        $this->command->info('  aux.lopez / Cali2025* (Auxiliar Vet)');
        $this->command->info('  op.torres / Cali2025* (Operador)');
        $this->command->info('  op.moreno / Cali2025* (Operador)');
        $this->command->info('  eval.castro / Cali2025* (Evaluador)');
        $this->command->info('');
    }
}