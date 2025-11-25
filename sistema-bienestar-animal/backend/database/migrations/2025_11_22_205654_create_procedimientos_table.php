<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procedimientos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tratamiento_id');
            $table->string('tipo_procedimiento', 100);
            $table->text('descripcion')->nullable();
            $table->dateTime('fecha_realizacion');
            $table->uuid('veterinario_id');
            $table->uuid('asistente_id')->nullable();
            $table->integer('duracion')->nullable()->comment('Duración en minutos');
            $table->text('resultado')->nullable();
            $table->text('complicaciones')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->foreign('tratamiento_id')->references('id')->on('tratamientos')->onDelete('cascade');
            $table->foreign('veterinario_id')->references('id')->on('veterinarios')->onDelete('restrict');
            $table->foreign('asistente_id')->references('id')->on('usuarios')->onDelete('set null');
            
            $table->index('tratamiento_id');
            $table->index('veterinario_id');
            $table->index('fecha_realizacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedimientos');
    }
};