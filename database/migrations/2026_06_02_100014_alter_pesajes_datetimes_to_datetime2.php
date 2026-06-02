<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Convierte las columnas de fecha de `pesajes` de `datetime` a `datetime2(3)`.
 *
 * SQL Server `datetime` tiene precisión de ~3.33 ms y REDONDEA: 23:59:59.999 se
 * guarda como el segundo siguiente (rueda al día/mes siguiente), lo que desvía la
 * atribución de un pesaje creado en el último milisegundo de un período.
 * `datetime2(3)` guarda el milisegundo exacto y, además, parsea YYYY-MM-DD como ISO
 * sin importar el DATEFORMAT del servidor. `created_at` es la columna por la que se
 * filtran dashboard y reportes, así que esta es la tabla crítica.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesajes', function (Blueprint $table) {
            $table->dateTime('created_at', 3)->nullable()->change();
            $table->dateTime('updated_at', 3)->nullable()->change();
            $table->dateTime('hora_salida', 3)->nullable()->change();
            $table->dateTime('cancelado_at', 3)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pesajes', function (Blueprint $table) {
            $table->dateTime('created_at')->nullable()->change();
            $table->dateTime('updated_at')->nullable()->change();
            $table->timestamp('hora_salida')->nullable()->change();
            $table->dateTime('cancelado_at')->nullable()->change();
        });
    }
};
