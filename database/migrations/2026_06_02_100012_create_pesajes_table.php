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
            // datetime2(3): SQL Server `datetime` redondea .999 al segundo siguiente
            // (desvía la atribución por fecha de dashboard/reportes) y parsea YYYY-MM-DD
            // según DATEFORMAT. datetime2 guarda el ms exacto y siempre interpreta ISO.
            $table->timestamp('hora_salida', 3)->nullable();
            $table->integer('bruto_salida_kg')->nullable();
            $table->boolean('editado')->default(false);
            $table->string('motivo_cancelacion', 500)->nullable();
            $table->foreignId('cancelado_por_id')->nullable()->constrained('users')->noActionOnDelete();
            $table->dateTime('cancelado_at', 3)->nullable();
            $table->timestamps(3);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesajes');
    }
};
