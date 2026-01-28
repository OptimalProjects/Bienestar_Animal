<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Agrega campos para almacenar certificados en cirugías y vacunas
     */
    public function up()
    {
        // Certificados para CIRUGÍAS
        Schema::table('cirugias', function (Blueprint $table) {
            $table->string('certificado')
                ->nullable()
                ->after('seguimiento_requerido')
                ->comment('Ruta del archivo de certificado en storage');
            
            $table->timestamp('fecha_adjuncion_certificado')
                ->nullable()
                ->after('certificado')
                ->comment('Fecha cuando se adjuntó el certificado');
            
            $table->text('notas_certificado')
                ->nullable()
                ->after('fecha_adjuncion_certificado')
                ->comment('Notas sobre el certificado');
        });

        // Certificados para VACUNAS
        Schema::table('vacunas', function (Blueprint $table) {
            $table->string('certificado')
                ->nullable()
                ->after('estado')
                ->comment('Ruta del archivo de certificado en storage');
            
            $table->timestamp('fecha_adjuncion_certificado')
                ->nullable()
                ->after('certificado')
                ->comment('Fecha cuando se adjuntó el certificado');
            
            $table->text('notas_certificado')
                ->nullable()
                ->after('fecha_adjuncion_certificado')
                ->comment('Notas sobre el certificado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('cirugias', function (Blueprint $table) {
            $table->dropColumn([
                'certificado',
                'fecha_adjuncion_certificado',
                'notas_certificado'
            ]);
        });

        Schema::table('vacunas', function (Blueprint $table) {
            $table->dropColumn([
                'certificado',
                'fecha_adjuncion_certificado',
                'notas_certificado'
            ]);
        });
    }
};