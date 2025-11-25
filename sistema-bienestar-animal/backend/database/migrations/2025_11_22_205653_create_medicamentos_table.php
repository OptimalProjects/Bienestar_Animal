<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicamentos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tratamiento_id');
            $table->uuid('producto_id');
            $table->string('dosis', 100);
            $table->string('frecuencia', 100);
            $table->integer('duracion_dias');
            $table->string('via_administracion', 50);
            $table->text('instrucciones_especiales')->nullable();
            $table->text('observaciones')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->timestamps();
            
            $table->foreign('tratamiento_id')->references('id')->on('tratamientos')->onDelete('cascade');
            $table->foreign('producto_id')->references('id')->on('productos_farmaceuticos')->onDelete('restrict');
            
            $table->index('tratamiento_id');
            $table->index('producto_id');
            $table->index('fecha_inicio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicamentos');
    }
};