<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Añade 'devuelta' como estado válido en adopciones.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE adopciones MODIFY COLUMN estado ENUM('solicitada', 'en_evaluacion', 'aprobada', 'completada', 'rechazada', 'revocada', 'devuelta') NOT NULL DEFAULT 'solicitada'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Primero actualizar registros 'devuelta' a 'revocada'
        DB::table('adopciones')->where('estado', 'devuelta')->update(['estado' => 'revocada']);

        DB::statement("ALTER TABLE adopciones MODIFY COLUMN estado ENUM('solicitada', 'en_evaluacion', 'aprobada', 'completada', 'rechazada', 'revocada') NOT NULL DEFAULT 'solicitada'");
    }
};
