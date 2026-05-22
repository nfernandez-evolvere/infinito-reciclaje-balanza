<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_servicio_tipo_vehiculo', function (Blueprint $table) {
            $table->dropForeign(['tipo_vehiculo_id']);
            $table->foreign('tipo_vehiculo_id')
                ->references('id')->on('tipos_vehiculo')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tipo_servicio_tipo_vehiculo', function (Blueprint $table) {
            $table->dropForeign(['tipo_vehiculo_id']);
            $table->foreign('tipo_vehiculo_id')
                ->references('id')->on('tipos_vehiculo')
                ->noActionOnDelete();
        });
    }
};
