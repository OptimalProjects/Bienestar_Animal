<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla para registrar devoluciones de animales adoptados.
     */
    public function up(): void
    {
        Schema::create('devoluciones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('adopcion_id');
            $table->uuid('animal_id');
            $table->uuid('registrado_por')->nullable();

            // Información de la devolución
            $table->date('fecha_devolucion');
            $table->enum('motivo', [
                'incompatibilidad',
                'cambio_situacion',
                'problemas_comportamiento',
                'enfermedad_animal',
                'enfermedad_adoptante',
                'mudanza',
                'economico',
                'fallecimiento_adoptante',
                'alergias',
                'otro'
            ]);
            $table->text('descripcion_motivo');
            $table->enum('estado_animal_devolucion', [
                'bueno',
                'regular',
                'malo',
                'critico'
            ])->default('bueno');
            $table->text('observaciones_estado')->nullable();

            // Control de revisión veterinaria obligatoria
            $table->uuid('consulta_revision_id')->nullable();
            $table->boolean('revision_veterinaria_completada')->default(false);
            $table->date('fecha_revision_programada')->nullable();

            // Estado del proceso de devolución
            $table->enum('estado_proceso', [
                'recibido',
                'en_revision',
                'aprobado_readopcion',
                'en_tratamiento',
                'finalizado'
            ])->default('recibido');

            $table->timestamps();

            // Foreign keys
            $table->foreign('adopcion_id')->references('id')->on('adopciones')->onDelete('cascade');
            $table->foreign('animal_id')->references('id')->on('animals')->onDelete('cascade');
            $table->foreign('registrado_por')->references('id')->on('usuarios')->onDelete('set null');
            $table->foreign('consulta_revision_id')->references('id')->on('consultas')->onDelete('set null');

            // Índices
            $table->index('fecha_devolucion');
            $table->index('motivo');
            $table->index('estado_proceso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devoluciones');
    }
};
