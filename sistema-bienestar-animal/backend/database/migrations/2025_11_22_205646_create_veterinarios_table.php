<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('veterinarios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('usuario_id')->unique();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('numero_tarjeta_profesional', 50)->unique();
            $table->string('especialidad', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            
            $table->index('usuario_id');
            $table->index('numero_tarjeta_profesional');
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('veterinarios');
    }
};