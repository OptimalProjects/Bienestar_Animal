<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rescates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('denuncia_id')->nullable();
            $table->date('fecha_programada');
            $table->dateTime('fecha_ejecucion')->nullable();
            $table->json('equipo_rescate')->nullable();
            $table->uuid('animal_rescatado_id')->nullable();
            $table->boolean('exitoso')->default(false);
            $table->text('observaciones')->nullable();
            $table->text('motivo_fallo')->nullable();
            $table->timestamps();
            
            $table->foreign('denuncia_id')->references('id')->on('denuncias')->onDelete('set null');
            $table->foreign('animal_rescatado_id')->references('id')->on('animals')->onDelete('set null');
            
            $table->index('denuncia_id');
            $table->index('animal_rescatado_id');
            $table->index('fecha_programada');
            $table->index('exitoso');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rescates');
    }
};