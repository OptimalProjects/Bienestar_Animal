<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recordatorios_vacunas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('vacuna_id');
            $table->uuid('animal_id');
            $table->date('fecha_recordatorio');
            $table->enum('tipo', ['proxima_dosis', 'refuerzo', 'vencimiento']);
            $table->enum('estado', ['pendiente', 'enviado', 'completado', 'cancelado'])->default('pendiente');
            $table->dateTime('fecha_envio')->nullable();
            $table->enum('canal', ['email', 'sms', 'whatsapp', 'sistema'])->default('sistema');
            $table->string('destinatario')->nullable();
            $table->timestamps();
            
            $table->foreign('vacuna_id')->references('id')->on('vacunas')->onDelete('cascade');
            $table->foreign('animal_id')->references('id')->on('animals')->onDelete('cascade');
            
            $table->index('vacuna_id');
            $table->index('animal_id');
            $table->index('fecha_recordatorio');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recordatorios_vacunas');
    }
};