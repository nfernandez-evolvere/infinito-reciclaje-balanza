<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_servicio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizacion_id')->constrained('organizaciones')->cascadeOnDelete();
            $table->string('nombre', 100);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['organizacion_id', 'nombre']);
        });

        Schema::create('tipo_servicio_tipo_vehiculo', function (Blueprint $table) {
            $table->foreignId('tipo_servicio_id')->constrained('tipos_servicio')->cascadeOnDelete();
            $table->foreignId('tipo_vehiculo_id')->constrained('tipos_vehiculo')->noActionOnDelete();
            $table->primary(['tipo_servicio_id', 'tipo_vehiculo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_servicio_tipo_vehiculo');
        Schema::dropIfExists('tipos_servicio');
    }
};
