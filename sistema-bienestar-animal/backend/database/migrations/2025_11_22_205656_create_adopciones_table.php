<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adopciones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('animal_id');
            $table->uuid('adoptante_id');
            $table->dateTime('fecha_solicitud')->useCurrent();
            $table->dateTime('fecha_aprobacion')->nullable();
            $table->dateTime('fecha_entrega')->nullable();
            $table->enum('estado', ['solicitada', 'en_evaluacion', 'aprobada', 'rechazada', 'completada', 'revocada'])->default('solicitada');
            $table->uuid('evaluador_id')->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('contrato_firmado')->default(false);
            $table->string('contrato_url')->nullable();
            $table->text('motivo_rechazo')->nullable();
            $table->text('motivo_revocacion')->nullable();
            $table->timestamps();
            
            $table->foreign('animal_id')->references('id')->on('animals')->onDelete('restrict');
            $table->foreign('adoptante_id')->references('id')->on('adoptantes')->onDelete('restrict');
            $table->foreign('evaluador_id')->references('id')->on('usuarios')->onDelete('set null');
            
            $table->index('animal_id');
            $table->index('adoptante_id');
            $table->index('estado');
            $table->index('fecha_solicitud');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adopciones');
    }
};