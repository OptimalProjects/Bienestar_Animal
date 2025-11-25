<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examenes_laboratorio', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('consulta_id');
            $table->string('tipo_examen', 100);
            $table->date('fecha_solicitud');
            $table->date('fecha_resultado')->nullable();
            $table->string('laboratorio', 150)->nullable();
            $table->json('resultados')->nullable();
            $table->text('interpretacion')->nullable();
            $table->json('archivos_adjuntos')->nullable();
            $table->enum('estado', ['solicitado', 'en_proceso', 'completado', 'cancelado'])->default('solicitado');
            $table->timestamps();
            
            $table->foreign('consulta_id')->references('id')->on('consultas')->onDelete('cascade');
            
            $table->index('consulta_id');
            $table->index('tipo_examen');
            $table->index('estado');
            $table->index('fecha_solicitud');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examenes_laboratorio');
    }
};