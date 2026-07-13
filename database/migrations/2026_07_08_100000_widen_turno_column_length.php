<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Los turnos pasan a ser texto libre por zona (sin catálogo): el operador
     * escribe el nombre en el modal, así que el límite de 10 caracteres
     * (pensado solo para "Diurna"/"Nocturna") se amplía a 20.
     */
    public function up(): void
    {
        Schema::table('zona_turnos', function (Blueprint $table) {
            $table->string('turno', 20)->change();
        });

        Schema::table('pesajes', function (Blueprint $table) {
            $table->string('turno', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('zona_turnos', function (Blueprint $table) {
            $table->string('turno', 10)->change();
        });

        Schema::table('pesajes', function (Blueprint $table) {
            $table->string('turno', 10)->nullable()->change();
        });
    }
};
