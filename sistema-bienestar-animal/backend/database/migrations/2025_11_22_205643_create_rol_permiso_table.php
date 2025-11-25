<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rol_permiso', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('rol_id');
            $table->uuid('permiso_id');
            $table->uuid('asignado_por')->nullable();
            $table->dateTime('fecha_asignacion')->useCurrent();
            
            $table->foreign('rol_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('permiso_id')->references('id')->on('permisos')->onDelete('cascade');
            $table->foreign('asignado_por')->references('id')->on('usuarios')->onDelete('set null');
            
            $table->unique(['rol_id', 'permiso_id'], 'unique_rol_permiso');
            $table->index('rol_id');
            $table->index('permiso_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rol_permiso');
    }
};