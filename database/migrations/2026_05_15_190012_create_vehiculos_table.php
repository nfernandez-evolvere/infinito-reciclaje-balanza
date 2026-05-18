<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();
            $table->string('patente', 20)->unique();
            $table->string('numero_interno', 20)->unique();
            $table->integer('tara_kg');
            $table->foreignId('tipo_vehiculo_id')->constrained('tipos_vehiculo')->restrictOnDelete();
            $table->string('titular', 200);
            $table->integer('capacidad_kg')->nullable();
            $table->string('observaciones', 500)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
