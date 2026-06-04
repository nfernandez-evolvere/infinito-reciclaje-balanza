<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('config_alertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizacion_id')->constrained('organizaciones')->cascadeOnDelete();
            $table->string('tipo', 50);
            $table->boolean('activo')->default(true);
            // umbral_valor: significado varía por tipo
            // peso_fuera_rango       → null (usa rangos de tipos_vehiculo)
            // volumen_diario_atipico → % de desviación del promedio (default 20)
            // gap_registro           → minutos sin actividad en horario operativo (default 120)
            // frecuencia_zona_atipica → % de desviación del promedio por zona (default 30)
            $table->decimal('umbral_valor', 8, 2)->nullable();
            $table->timestamps();

            $table->unique(['organizacion_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('config_alertas');
    }
};
