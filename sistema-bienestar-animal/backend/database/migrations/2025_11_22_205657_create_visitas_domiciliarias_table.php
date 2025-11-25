<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitas_domiciliarias', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('adopcion_id');
            $table->date('fecha_programada');
            $table->dateTime('fecha_realizada')->nullable();
            $table->uuid('visitador_id');
            $table->enum('tipo_visita', ['pre_adopcion', 'seguimiento_1mes', 'seguimiento_3meses', 'seguimiento_6meses', 'extraordinaria']);
            $table->text('observaciones')->nullable();
            $table->json('condiciones_hogar')->nullable();
            $table->json('estado_animal')->nullable();
            $table->enum('resultado', ['satisfactoria', 'observaciones', 'critica'])->nullable();
            $table->text('recomendaciones')->nullable();
            $table->json('fotos_respaldo')->nullable();
            $table->timestamps();
            
            $table->foreign('adopcion_id')->references('id')->on('adopciones')->onDelete('cascade');
            $table->foreign('visitador_id')->references('id')->on('usuarios')->onDelete('restrict');
            
            $table->index('adopcion_id');
            $table->index('visitador_id');
            $table->index('fecha_programada');
            $table->index('tipo_visita');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitas_domiciliarias');
    }
};