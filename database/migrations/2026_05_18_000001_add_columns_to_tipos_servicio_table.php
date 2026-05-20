<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('tipos_servicio', 'nombre')) {
            return;
        }

        Schema::table('tipos_servicio', function (Blueprint $table) {
            $table->string('nombre', 100)->unique()->after('id');
            $table->foreignId('tipo_vehiculo_sugerido_id')
                ->nullable()
                ->constrained('tipos_vehiculo')
                ->nullOnDelete()
                ->after('nombre');
            $table->boolean('activo')->default(true)->after('tipo_vehiculo_sugerido_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('tipos_servicio', 'nombre')) {
            return;
        }

        Schema::table('tipos_servicio', function (Blueprint $table) {
            $table->dropForeign(['tipo_vehiculo_sugerido_id']);
            $table->dropColumn(['nombre', 'tipo_vehiculo_sugerido_id', 'activo']);
        });
    }
};
