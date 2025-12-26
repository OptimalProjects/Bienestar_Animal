<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Añade 'sistema' como canal de recepción válido para denuncias generadas automáticamente.
     */
    public function up(): void
    {
        // Modificar el ENUM para incluir 'sistema'
        DB::statement("ALTER TABLE denuncias MODIFY COLUMN canal_recepcion ENUM('web', 'telefono', 'presencial', 'email', 'whatsapp', 'sistema') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Primero actualizar registros con 'sistema' a 'web'
        DB::table('denuncias')->where('canal_recepcion', 'sistema')->update(['canal_recepcion' => 'web']);

        // Luego remover 'sistema' del ENUM
        DB::statement("ALTER TABLE denuncias MODIFY COLUMN canal_recepcion ENUM('web', 'telefono', 'presencial', 'email', 'whatsapp') NOT NULL");
    }
};
