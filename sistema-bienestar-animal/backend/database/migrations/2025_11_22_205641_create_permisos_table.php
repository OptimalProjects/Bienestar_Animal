<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permisos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('recurso', 100);
            $table->string('accion', 50);
            $table->text('descripcion')->nullable();
            $table->timestamps();
            
            $table->unique(['recurso', 'accion'], 'unique_recurso_accion');
            $table->index('recurso');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permisos');
    }
};