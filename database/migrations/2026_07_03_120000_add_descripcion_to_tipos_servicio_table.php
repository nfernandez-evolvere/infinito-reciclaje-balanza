<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega `descripcion` a `tipos_servicio` para el reporte v2 (página "¿Qué es cada
 * servicio?"). Migración aditiva y reversible: la tabla ya está en producción, así que
 * el cambio llega vía `php artisan migrate` (pendiente, seguro) — no se reedita el
 * create original. Sin `after()`: SQL Server no soporta ordenar columnas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipos_servicio', function (Blueprint $table) {
            $table->string('descripcion', 300)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tipos_servicio', function (Blueprint $table) {
            $table->dropColumn('descripcion');
        });
    }
};
