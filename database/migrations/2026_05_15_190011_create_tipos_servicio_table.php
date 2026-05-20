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
            $table->foreignId('tipo_vehiculo_sugerido_id')
                ->nullable()
                ->constrained('tipos_vehiculo')
                ->noActionOnDelete();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['organizacion_id', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_servicio');
    }
};
