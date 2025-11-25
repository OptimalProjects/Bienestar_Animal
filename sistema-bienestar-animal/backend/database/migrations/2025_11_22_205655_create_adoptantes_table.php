<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adoptantes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->enum('tipo_documento', ['CC', 'CE', 'TI', 'PA', 'PEP']);
            $table->string('numero_documento', 20)->unique();
            $table->date('fecha_nacimiento');
            $table->string('telefono', 20);
            $table->string('email');
            $table->string('direccion');
            $table->enum('tipo_vivienda', ['casa', 'apartamento', 'finca', 'otro']);
            $table->boolean('tiene_patio')->default(false);
            $table->text('experiencia_animales')->nullable();
            $table->integer('num_personas_hogar')->nullable();
            $table->enum('estado', ['activo', 'inactivo', 'bloqueado'])->default('activo');
            $table->timestamps();
            
            $table->index('numero_documento');
            $table->index('email');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adoptantes');
    }
};