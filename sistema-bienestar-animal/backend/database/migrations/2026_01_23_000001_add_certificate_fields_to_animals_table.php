<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Agrega campos para almacenar certificados de esterilización
     */
    public function up()
    {
        Schema::table('animals', function (Blueprint $table) {
            // Campos para certificado de esterilización
            $table->string('certificado_esterilizacion')
                ->nullable()
                ->after('esterilizacion')
                ->comment('Ruta del archivo de certificado en storage');
            
            $table->timestamp('fecha_adjuncion_certificado')
                ->nullable()
                ->after('certificado_esterilizacion')
                ->comment('Fecha cuando se adjuntó el certificado');
            
            $table->text('notas_certificado')
                ->nullable()
                ->after('fecha_adjuncion_certificado')
                ->comment('Notas sobre el certificado');

            // Campos adicionales para esterilización
            $table->date('fecha_esterilizacion')
                ->nullable()
                ->after('esterilizacion')
                ->comment('Fecha de la cirugía de esterilización');
            
            $table->string('veterinario_esterilizacion')
                ->nullable()
                ->after('fecha_esterilizacion')
                ->comment('Veterinario que realizó la esterilización');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->dropColumn([
                'certificado_esterilizacion',
                'fecha_adjuncion_certificado',
                'notas_certificado',
                'fecha_esterilizacion',
                'veterinario_esterilizacion'
            ]);
        });
    }
};
