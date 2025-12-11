<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProcedimientoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Nota: La tabla 'procedimientos' almacena registros de procedimientos realizados
     * en tratamientos, no un catalogo. Los datos se crean cuando se registran
     * tratamientos reales.
     */
    public function run(): void
    {
        // Esta tabla no requiere datos de semilla
        // Los procedimientos se registran cuando se realizan tratamientos
    }
}
