<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tratamientos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('consulta_id')->nullable();
            $table->uuid('historial_clinico_id');
            $table->string('tipo_tratamiento', 100);
            $table->text('descripcion')->nullable();
            $table->text('objetivo')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->integer('duracion_estimada')->nullable()->comment('Duración en días');
            $table->enum('estado', ['activo', 'completado', 'suspendido', 'cancelado'])->default('activo');
            $table->enum('efectividad', ['excelente', 'buena', 'regular', 'pobre', 'sin_evaluar'])->default('sin_evaluar');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->foreign('consulta_id')->references('id')->on('consultas')->onDelete('set null');
            $table->foreign('historial_clinico_id')->references('id')->on('historiales_clinicos')->onDelete('cascade');
            
            $table->index('consulta_id');
            $table->index('historial_clinico_id');
            $table->index('estado');
            $table->index('fecha_inicio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tratamientos');
    }
};