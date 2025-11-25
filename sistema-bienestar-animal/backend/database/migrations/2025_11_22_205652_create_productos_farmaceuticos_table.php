<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos_farmaceuticos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('codigo', 50)->unique();
            $table->string('nombre_comercial', 150);
            $table->string('nombre_generico', 150)->nullable();
            $table->string('principio_activo', 150)->nullable();
            $table->string('presentacion', 100)->nullable();
            $table->string('concentracion', 50)->nullable();
            $table->string('laboratorio', 100)->nullable();
            $table->string('registro_sanitario', 50)->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->integer('stock_actual')->default(0);
            $table->integer('stock_minimo')->default(0);
            $table->decimal('precio_unitario', 10, 2)->nullable();
            $table->boolean('requiere_receta')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index('codigo');
            $table->index('nombre_comercial');
            $table->index('activo');
            $table->index('stock_actual');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos_farmaceuticos');
    }
};