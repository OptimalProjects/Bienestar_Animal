<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('adoptantes', function (Blueprint $table) {
            $table->string('copia_cedula')->nullable()->after('estado');
            $table->string('comprobante_domicilio')->nullable()->after('copia_cedula');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adoptantes', function (Blueprint $table) {
            $table->dropColumn(['copia_cedula', 'comprobante_domicilio']);
        });
    }
};
