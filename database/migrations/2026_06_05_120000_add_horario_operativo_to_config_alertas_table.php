<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('config_alertas', function (Blueprint $table) {
            // Horario operativo configurable — solo lo usa el tipo gap_registro.
            // Formato 'H:i' (ej: '08:00'). null → se usa el default de ConfigAlerta::defaults().
            $table->string('hora_inicio', 5)->nullable();
            $table->string('hora_fin', 5)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('config_alertas', function (Blueprint $table) {
            $table->dropColumn(['hora_inicio', 'hora_fin']);
        });
    }
};
