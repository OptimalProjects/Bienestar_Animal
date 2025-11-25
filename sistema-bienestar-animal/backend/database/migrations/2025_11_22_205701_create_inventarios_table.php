<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventarios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 150);
            $table->string('categoria', 100);
            $table->string('tipo', 50);
            $table->string('unidad_medida', 20);
            $table->decimal('cantidad_actual', 10, 2)->default(0);
            $table->decimal('cantidad_minima', 10, 2)->default(0);
            $table->string('ubicacion', 100)->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->string('proveedor', 150)->nullable();
            $table->timestamps();
            
            $table->index('codigo');
            $table->index('categoria');
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventarios');
    }
};