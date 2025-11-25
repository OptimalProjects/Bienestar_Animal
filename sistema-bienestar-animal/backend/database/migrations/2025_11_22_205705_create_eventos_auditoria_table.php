<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos_auditoria', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('trace_id');
            $table->dateTime('timestamp')->useCurrent();
            $table->uuid('usuario_id')->nullable();
            $table->string('accion', 100);
            $table->string('recurso', 100);
            $table->string('modulo', 50);
            $table->json('detalles')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->enum('resultado', ['exitoso', 'fallido', 'denegado']);
            
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('set null');
            
            $table->index('trace_id');
            $table->index('usuario_id');
            $table->index('timestamp');
            $table->index('modulo');
            $table->index('accion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos_auditoria');
    }
};