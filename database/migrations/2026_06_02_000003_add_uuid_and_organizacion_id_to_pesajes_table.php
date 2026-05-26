<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // nullable() es requerido por SQLite al agregar columnas a tablas existentes.
        // El modelo siempre asigna uuid y organizacion_id antes de insertar.
        Schema::table('pesajes', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
            $table->foreignId('organizacion_id')
                ->nullable()
                ->after('uuid')
                ->constrained('organizaciones')
                ->noActionOnDelete();
        });

        // Backfill rows existentes antes de aplicar el índice único
        DB::table('pesajes')->whereNull('uuid')->orderBy('id')->each(function ($pesaje) {
            DB::table('pesajes')
                ->where('id', $pesaje->id)
                ->update(['uuid' => (string) Str::uuid()]);
        });

        Schema::table('pesajes', function (Blueprint $table) {
            $table->unique('uuid');
        });
    }

    public function down(): void
    {
        Schema::table('pesajes', function (Blueprint $table) {
            $table->dropForeign(['organizacion_id']);
            $table->dropUnique(['uuid']);
            $table->dropColumn(['uuid', 'organizacion_id']);
        });
    }
};
