<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Da de baja la FK única `tipo_vehiculo_sugerido_id`. La relación
     * tipo_servicio ↔ tipo_vehiculo pasó a ser N:M vía el pivot
     * `tipo_servicio_tipo_vehiculo` (varios vehículos sugeridos por servicio).
     */
    public function up(): void
    {
        Schema::table('tipos_servicio', function (Blueprint $table) {
            $table->dropForeign(['tipo_vehiculo_sugerido_id']);
            $table->dropColumn('tipo_vehiculo_sugerido_id');
        });
    }

    public function down(): void
    {
        Schema::table('tipos_servicio', function (Blueprint $table) {
            $table->foreignId('tipo_vehiculo_sugerido_id')
                ->nullable()
                ->after('nombre')
                ->constrained('tipos_vehiculo')
                ->nullOnDelete();
        });
    }
};
