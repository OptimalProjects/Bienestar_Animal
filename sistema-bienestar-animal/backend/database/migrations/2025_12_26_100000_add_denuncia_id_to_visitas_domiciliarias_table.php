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
        Schema::table('visitas_domiciliarias', function (Blueprint $table) {
            $table->uuid('denuncia_id')->nullable()->after('fotos_respaldo');
            $table->foreign('denuncia_id')
                  ->references('id')
                  ->on('denuncias')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitas_domiciliarias', function (Blueprint $table) {
            $table->dropForeign(['denuncia_id']);
            $table->dropColumn('denuncia_id');
        });
    }
};
