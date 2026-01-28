<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('medicamento_id')->nullable();
            $table->uuid('consulta_id')->nullable();
            $table->enum('tipo_movimiento', ['entrada', 'salida']);
            $table->decimal('cantidad', 10, 2);
            $table->string('motivo')->nullable();
            $table->text('descripcion')->nullable();
            $table->uuid('usuario_id')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('medicamento_id')
                  ->references('id')
                  ->on('inventarios')
                  ->onDelete('set null');
            
            $table->foreign('consulta_id')
                  ->references('id')
                  ->on('consultas')
                  ->onDelete('set null');
            
            $table->foreign('usuario_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
            
            // Índices
            $table->index('medicamento_id');
            $table->index('consulta_id');
            $table->index('tipo_movimiento');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
