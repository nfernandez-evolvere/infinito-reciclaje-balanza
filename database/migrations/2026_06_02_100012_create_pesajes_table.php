<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesajes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organizacion_id')->constrained('organizaciones')->noActionOnDelete();
            $table->foreignId('vehiculo_id')->constrained('vehiculos')->noActionOnDelete();
            $table->foreignId('operador_id')->constrained('users')->noActionOnDelete();
            $table->foreignId('tipo_servicio_id')->constrained('tipos_servicio')->noActionOnDelete();
            $table->foreignId('zona_id')->constrained('zonas')->noActionOnDelete();
            $table->string('turno', 10)->nullable();
            $table->integer('peso_bruto_kg');
            $table->integer('peso_tara_kg');
            $table->integer('peso_neto_kg');
            $table->boolean('alerta_peso')->default(false);
            $table->string('observaciones', 500)->nullable();
            $table->string('estado', 20)->default('En predio');
            $table->timestamp('hora_salida')->nullable();
            $table->integer('bruto_salida_kg')->nullable();
            $table->boolean('editado')->default(false);
            $table->string('motivo_cancelacion', 500)->nullable();
            $table->foreignId('cancelado_por_id')->nullable()->constrained('users')->noActionOnDelete();
            $table->datetime('cancelado_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesajes');
    }
};
