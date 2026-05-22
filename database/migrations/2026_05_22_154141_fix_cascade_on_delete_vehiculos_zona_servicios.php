<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->dropForeign(['tipo_vehiculo_id']);
            $table->foreign('tipo_vehiculo_id')
                ->references('id')->on('tipos_vehiculo')
                ->cascadeOnDelete();
        });

        Schema::table('zona_servicios', function (Blueprint $table) {
            $table->dropForeign(['tipo_servicio_id']);
            $table->foreign('tipo_servicio_id')
                ->references('id')->on('tipos_servicio')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->dropForeign(['tipo_vehiculo_id']);
            $table->foreign('tipo_vehiculo_id')
                ->references('id')->on('tipos_vehiculo')
                ->noActionOnDelete();
        });

        Schema::table('zona_servicios', function (Blueprint $table) {
            $table->dropForeign(['tipo_servicio_id']);
            $table->foreign('tipo_servicio_id')
                ->references('id')->on('tipos_servicio')
                ->noActionOnDelete();
        });
    }
};
