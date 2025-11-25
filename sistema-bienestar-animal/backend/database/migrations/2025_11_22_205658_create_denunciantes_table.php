<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('denunciantes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('telefono', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('direccion')->nullable();
            $table->timestamps();
            
            $table->index('telefono');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('denunciantes');
    }
};