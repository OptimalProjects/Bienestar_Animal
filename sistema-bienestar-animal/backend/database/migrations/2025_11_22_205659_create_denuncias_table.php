<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('denuncias', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('numero_ticket', 50)->unique();
            $table->uuid('denunciante_id')->nullable();
            $table->dateTime('fecha_denuncia')->useCurrent();
            $table->enum('canal_recepcion', ['web', 'telefono', 'presencial', 'email', 'whatsapp']);
            $table->enum('tipo_denuncia', ['maltrato', 'abandono', 'animal_herido', 'animal_peligroso', 'otro']);
            $table->text('descripcion');
            $table->string('ubicacion');
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();
            $table->json('evidencias')->nullable();
            $table->enum('prioridad', ['baja', 'media', 'alta', 'urgente'])->default('media');
            $table->enum('estado', ['recibida', 'en_revision', 'asignada', 'en_atencion', 'resuelta', 'cerrada', 'desestimada'])->default('recibida');
            $table->uuid('responsable_id')->nullable();
            $table->dateTime('fecha_asignacion')->nullable();
            $table->dateTime('fecha_resolucion')->nullable();
            $table->text('observaciones_resolucion')->nullable();
            $table->boolean('es_anonima')->default(false);
            $table->timestamps();
            
            $table->foreign('denunciante_id')->references('id')->on('denunciantes')->onDelete('set null');
            $table->foreign('responsable_id')->references('id')->on('usuarios')->onDelete('set null');
            
            $table->index('numero_ticket');
            $table->index('denunciante_id');
            $table->index('estado');
            $table->index('prioridad');
            $table->index('fecha_denuncia');
            $table->index('tipo_denuncia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('denuncias');
    }
};