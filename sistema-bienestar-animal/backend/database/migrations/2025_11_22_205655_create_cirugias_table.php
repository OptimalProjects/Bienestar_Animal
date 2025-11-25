<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cirugias', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('historial_clinico_id');
            $table->string('tipo_cirugia', 100);
            $table->text('descripcion')->nullable();
            $table->date('fecha_programada');
            $table->dateTime('fecha_realizacion')->nullable();
            $table->uuid('cirujano_id');
            $table->uuid('anestesiologo_id')->nullable();
            $table->json('asistentes')->nullable();
            $table->integer('duracion')->nullable()->comment('Duración en minutos');
            $table->string('tipo_anestesia', 100)->nullable();
            $table->text('complicaciones')->nullable();
            $table->text('resultado')->nullable();
            $table->text('postoperatorio')->nullable();
            $table->boolean('seguimiento_requerido')->default(true);
            $table->enum('estado', ['programada', 'realizada', 'cancelada', 'postergada'])->default('programada');
            $table->timestamps();
            
            $table->foreign('historial_clinico_id')->references('id')->on('historiales_clinicos')->onDelete('cascade');
            $table->foreign('cirujano_id')->references('id')->on('veterinarios')->onDelete('restrict');
            $table->foreign('anestesiologo_id')->references('id')->on('veterinarios')->onDelete('restrict');
            
            $table->index('historial_clinico_id');
            $table->index('cirujano_id');
            $table->index('fecha_programada');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cirugias');
    }
};