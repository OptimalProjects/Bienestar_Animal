<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_vacunas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->enum('especie_aplicable', ['perro', 'gato', 'ambos', 'otro']);
            $table->integer('edad_minima')->nullable()->comment('Edad mínima en meses');
            $table->integer('intervalo_dosis')->nullable()->comment('Días entre dosis');
            $table->integer('numero_dosis')->default(1);
            $table->boolean('es_obligatoria')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index('codigo');
            $table->index('especie_aplicable');
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_vacunas');
    }
};