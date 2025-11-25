<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historiales_clinicos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('animal_id')->unique();
            $table->dateTime('fecha_apertura')->useCurrent();
            $table->enum('estado', ['activo', 'cerrado', 'archivado'])->default('activo');
            $table->timestamps();
            
            $table->foreign('animal_id')->references('id')->on('animals')->onDelete('cascade');
            
            $table->index('animal_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historiales_clinicos');
    }
};