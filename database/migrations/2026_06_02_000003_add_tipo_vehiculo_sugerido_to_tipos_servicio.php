<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipos_servicio', function (Blueprint $table) {
            $table->foreignId('tipo_vehiculo_sugerido_id')
                ->nullable()
                ->after('nombre')
                ->constrained('tipos_vehiculo')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tipos_servicio', function (Blueprint $table) {
            $table->dropForeign(['tipo_vehiculo_sugerido_id']);
            $table->dropColumn('tipo_vehiculo_sugerido_id');
        });
    }
};
