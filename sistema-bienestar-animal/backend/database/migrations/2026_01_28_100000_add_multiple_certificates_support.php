<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Agrega campos para almacenar MÚLTIPLES certificados en cirugías y vacunas
     */
    public function up()
    {
        // ===================================================
        // CIRUGÍAS - Soportar múltiples certificados
        // ===================================================
        
        if (!Schema::hasColumn('cirugias', 'certificados')) {
            Schema::table('cirugias', function (Blueprint $table) {
                // JSON array para múltiples certificados
                $table->json('certificados')
                    ->nullable()
                    ->comment('Array de certificados: [{path, fecha, notas}]');
            });
        }

        // Migrar datos existentes si hay certificado único
        if (Schema::hasColumn('cirugias', 'certificado')) {
            DB::statement("
                UPDATE cirugias 
                SET certificados = JSON_ARRAY(
                    JSON_OBJECT(
                        'path', certificado,
                        'fecha_adjuncion', fecha_adjuncion_certificado,
                        'notas', notas_certificado
                    )
                )
                WHERE certificado IS NOT NULL
            ");
            
            // Opcional: Eliminar columnas antiguas después de migrar
            // Schema::table('cirugias', function (Blueprint $table) {
            //     $table->dropColumn(['certificado', 'fecha_adjuncion_certificado', 'notas_certificado']);
            // });
        }

        // ===================================================
        // VACUNAS - Soportar múltiples certificados
        // ===================================================
        
        if (!Schema::hasColumn('vacunas', 'certificados')) {
            Schema::table('vacunas', function (Blueprint $table) {
                // JSON array para múltiples certificados
                $table->json('certificados')
                    ->nullable()
                    ->comment('Array de certificados: [{path, fecha, notas}]');
            });
        }

        // Migrar datos existentes si hay certificado único
        if (Schema::hasColumn('vacunas', 'certificado')) {
            DB::statement("
                UPDATE vacunas 
                SET certificados = JSON_ARRAY(
                    JSON_OBJECT(
                        'path', certificado,
                        'fecha_adjuncion', fecha_adjuncion_certificado,
                        'notas', notas_certificado
                    )
                )
                WHERE certificado IS NOT NULL
            ");
            
            // Opcional: Eliminar columnas antiguas después de migrar
            // Schema::table('vacunas', function (Blueprint $table) {
            //     $table->dropColumn(['certificado', 'fecha_adjuncion_certificado', 'notas_certificado']);
            // });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('cirugias', function (Blueprint $table) {
            if (Schema::hasColumn('cirugias', 'certificados')) {
                $table->dropColumn('certificados');
            }
        });

        Schema::table('vacunas', function (Blueprint $table) {
            if (Schema::hasColumn('vacunas', 'certificados')) {
                $table->dropColumn('certificados');
            }
        });
    }
};