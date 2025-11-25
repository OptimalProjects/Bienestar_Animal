<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('puntos_indicadores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('indicador_id');
            $table->date('fecha');
            $table->decimal('valor', 15, 4);
            $table->json('dimensiones')->nullable();
            $table->string('calidad', 50)->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('indicador_id')->references('id')->on('indicadores')->onDelete('cascade');
            
            $table->index('indicador_id');
            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('puntos_indicadores');
    }
};