<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('animals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('codigo_unico', 50)->unique();
            $table->string('nombre', 100)->nullable();
            $table->string('especie', 50);
            $table->string('raza', 100)->nullable();
            $table->enum('sexo', ['macho', 'hembra', 'desconocido'])->nullable();
            $table->integer('edad_aproximada')->nullable()->comment('Edad en meses');
            $table->decimal('peso_actual', 6, 2)->nullable()->comment('Peso en kg');
            $table->string('color', 100)->nullable();
            $table->enum('tamanio', ['pequenio', 'mediano', 'grande', 'muy_grande'])->nullable();
            $table->text('senias_particulares')->nullable();
            $table->string('foto_principal')->nullable();
            $table->json('galeria_fotos')->nullable();
            $table->date('fecha_rescate')->nullable();
            $table->string('ubicacion_rescate')->nullable();
            $table->enum('estado', ['en_calle', 'en_refugio', 'en_adopcion', 'adoptado', 'fallecido', 'en_tratamiento'])->default('en_calle');
            $table->enum('estado_salud', ['critico', 'grave', 'estable', 'bueno', 'excelente'])->default('estable');
            $table->text('observaciones')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('created_by')->references('id')->on('usuarios')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('usuarios')->onDelete('set null');
            
            $table->index('codigo_unico');
            $table->index('especie');
            $table->index('estado');
            $table->index('estado_salud');
            $table->index('fecha_rescate');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('animals');
    }
};