<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('historial_clinico_id');
            $table->uuid('veterinario_id');
            $table->dateTime('fecha_consulta');
            $table->text('motivo')->nullable();
            $table->text('sintomas')->nullable();
            $table->json('signos_vitales')->nullable();
            $table->decimal('temperatura', 4, 2)->nullable()->comment('Temperatura en °C');
            $table->integer('frecuencia_cardiaca')->nullable()->comment('Latidos por minuto');
            $table->integer('frecuencia_respiratoria')->nullable()->comment('Respiraciones por minuto');
            $table->decimal('peso', 6, 2)->nullable()->comment('Peso en kg');
            $table->text('diagnostico')->nullable();
            $table->text('diagnostico_diferencial')->nullable();
            $table->text('plan_diagnostico')->nullable();
            $table->text('observaciones')->nullable();
            $table->text('recomendaciones')->nullable();
            $table->date('proxima_cita')->nullable();
            $table->enum('tipo_consulta', ['inicial', 'control', 'emergencia', 'seguimiento'])->default('inicial');
            $table->timestamps();
            
            $table->foreign('historial_clinico_id')->references('id')->on('historiales_clinicos')->onDelete('cascade');
            $table->foreign('veterinario_id')->references('id')->on('veterinarios')->onDelete('restrict');
            
            $table->index('historial_clinico_id');
            $table->index('veterinario_id');
            $table->index('fecha_consulta');
            $table->index('tipo_consulta');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultas');
    }
};