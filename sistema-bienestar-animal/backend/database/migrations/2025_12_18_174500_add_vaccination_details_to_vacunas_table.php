<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacunas', function (Blueprint $table) {
            // Agregar nombre_vacuna (nombre comercial)
            if (!Schema::hasColumn('vacunas', 'nombre_vacuna')) {
                $table->string('nombre_vacuna', 100)->after('veterinario_id');
            }
            
            // Agregar fecha de vencimiento
            if (!Schema::hasColumn('vacunas', 'fecha_vencimiento')) {
                $table->date('fecha_vencimiento')->nullable()->after('fabricante');
            }
            
            // Agregar dosis en ml
            if (!Schema::hasColumn('vacunas', 'dosis')) {
                $table->decimal('dosis', 5, 2)->after('fecha_vencimiento')->comment('Dosis en ml');
            }
            
            // Agregar vía de administración
            if (!Schema::hasColumn('vacunas', 'via_administracion')) {
                $table->enum('via_administracion', [
                    'subcutanea', 
                    'intramuscular', 
                    'intranasal', 
                    'oral', 
                    'intravenosa'
                ])->after('dosis');
            }
            
            // Agregar sitio de aplicación
            if (!Schema::hasColumn('vacunas', 'sitio_aplicacion')) {
                $table->string('sitio_aplicacion', 100)->nullable()->after('via_administracion');
            }
            
            // Agregar número de dosis
            if (!Schema::hasColumn('vacunas', 'numero_dosis')) {
                $table->enum('numero_dosis', ['1', '2', '3', 'refuerzo'])->after('sitio_aplicacion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vacunas', function (Blueprint $table) {
            $table->dropColumn([
                'nombre_vacuna',
                'fecha_vencimiento',
                'dosis',
                'via_administracion',
                'sitio_aplicacion',
                'numero_dosis'
            ]);
        });
    }
};