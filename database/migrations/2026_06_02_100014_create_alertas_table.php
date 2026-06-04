<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organizacion_id')->constrained('organizaciones')->cascadeOnDelete();
            // FK secundaria: users → organizaciones → cascada múltiple → noActionOnDelete
            $table->foreignId('user_id')->nullable()->constrained('users')->noActionOnDelete();
            $table->string('tipo', 50); // peso_fuera_rango | volumen_diario_atipico | gap_registro | frecuencia_zona_atipica
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            // FKs secundarias: noActionOnDelete para evitar cascadas múltiples a organizaciones
            $table->foreignId('pesaje_id')->nullable()->constrained('pesajes')->noActionOnDelete();
            $table->foreignId('zona_id')->nullable()->constrained('zonas')->noActionOnDelete();
            $table->date('fecha_deteccion');
            $table->boolean('leida')->default(false);
            $table->timestamp('leida_at')->nullable();
            $table->timestamps();

            $table->index(['organizacion_id', 'leida']);
            $table->index(['user_id', 'leida']);
            $table->index(['organizacion_id', 'tipo', 'fecha_deteccion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};
