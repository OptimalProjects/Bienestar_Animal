<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cirugias', function (Blueprint $table) {
            // Verificar y agregar columnas faltantes
            
            // Si no existe anestesiologo_id
            if (!Schema::hasColumn('cirugias', 'anestesiologo_id')) {
                $table->uuid('anestesiologo_id')->nullable()->after('cirujano_id');
                $table->foreign('anestesiologo_id')
                      ->references('id')
                      ->on('veterinarios')
                      ->onDelete('set null');
            }

            // Si no existe tipo_anestesia
            if (!Schema::hasColumn('cirugias', 'tipo_anestesia')) {
                $table->text('tipo_anestesia')->nullable()->after('duracion');
            }

            // Si no existe postoperatorio
            if (!Schema::hasColumn('cirugias', 'postoperatorio')) {
                $table->text('postoperatorio')->nullable()->after('complicaciones');
            }

            // Si no existe estado_animal
            if (!Schema::hasColumn('cirugias', 'estado_animal')) {
                $table->enum('estado_animal', [
                    'en_tratamiento',
                    'en_recuperacion',
                    'estable'
                ])->nullable()->after('resultado');
            }

            // Modificar columnas existentes si es necesario
            
            // Asegurar que estado tenga los valores correctos
            if (Schema::hasColumn('cirugias', 'estado')) {
                $table->enum('estado', ['programada', 'realizada', 'cancelada'])
                      ->default('programada')
                      ->change();
            } else {
                $table->enum('estado', ['programada', 'realizada', 'cancelada'])
                      ->default('programada')
                      ->after('seguimiento_requerido');
            }

            // Asegurar que tipo_cirugia tenga todos los valores
            if (Schema::hasColumn('cirugias', 'tipo_cirugia')) {
                $table->enum('tipo_cirugia', [
                    'esterilizacion',
                    'castracion',
                    'ortopedica',
                    'abdominal',
                    'oftalmologica',
                    'dental',
                    'oncologica',
                    'emergencia',
                    'otra'
                ])->change();
            }

            // Asegurar que resultado tenga los valores correctos
            if (Schema::hasColumn('cirugias', 'resultado')) {
                $table->enum('resultado', [
                    'exitosa',
                    'con_complicaciones',
                    'fallida'
                ])->nullable()->change();
            }

            // Soft deletes si no existe
            if (!Schema::hasColumn('cirugias', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cirugias', function (Blueprint $table) {
            // Eliminar foreign key y columna de anestesiologo
            if (Schema::hasColumn('cirugias', 'anestesiologo_id')) {
                $table->dropForeign(['anestesiologo_id']);
                $table->dropColumn('anestesiologo_id');
            }

            // Eliminar columnas agregadas
            $columnsToDrop = [
                'tipo_anestesia',
                'postoperatorio',
                'estado_animal',
                'deleted_at'
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('cirugias', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};