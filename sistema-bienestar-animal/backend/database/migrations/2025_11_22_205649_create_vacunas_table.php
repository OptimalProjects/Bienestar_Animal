<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacunas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('historial_clinico_id');
            $table->uuid('tipo_vacuna_id');
            $table->date('fecha_aplicacion');
            $table->date('fecha_proxima_dosis')->nullable();
            $table->string('lote_vacuna', 50)->nullable();
            $table->string('fabricante', 100)->nullable();
            $table->uuid('veterinario_id');
            $table->text('observaciones')->nullable();
            $table->text('reacciones_adversas')->nullable();
            $table->enum('estado', ['aplicada', 'programada', 'vencida', 'cancelada'])->default('aplicada');
            $table->timestamps();
            
            $table->foreign('historial_clinico_id')->references('id')->on('historiales_clinicos')->onDelete('cascade');
            $table->foreign('tipo_vacuna_id')->references('id')->on('tipos_vacunas')->onDelete('restrict');
            $table->foreign('veterinario_id')->references('id')->on('veterinarios')->onDelete('restrict');
            
            $table->index('historial_clinico_id');
            $table->index('tipo_vacuna_id');
            $table->index('veterinario_id');
            $table->index('fecha_aplicacion');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacunas');
    }
};