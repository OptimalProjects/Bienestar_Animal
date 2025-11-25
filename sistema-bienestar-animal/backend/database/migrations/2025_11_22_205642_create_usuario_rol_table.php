<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario_rol', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('usuario_id');
            $table->uuid('rol_id');
            $table->uuid('asignado_por')->nullable();
            $table->dateTime('fecha_asignacion')->useCurrent();
            $table->dateTime('fecha_expiracion')->nullable();
            $table->boolean('activo')->default(true);
            
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('rol_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('asignado_por')->references('id')->on('usuarios')->onDelete('set null');
            
            $table->unique(['usuario_id', 'rol_id'], 'unique_usuario_rol');
            $table->index('usuario_id');
            $table->index('rol_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_rol');
    }
};