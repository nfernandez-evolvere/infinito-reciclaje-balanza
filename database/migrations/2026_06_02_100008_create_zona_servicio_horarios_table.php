<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zona_servicio_horarios', function (Blueprint $table) {
            $table->foreignId('zona_id');
            $table->foreignId('tipo_servicio_id');
            $table->unsignedTinyInteger('dia_semana'); // 1=Lunes … 7=Domingo
            $table->unsignedTinyInteger('franja');     // orden dentro del día
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->primary(['zona_id', 'tipo_servicio_id', 'dia_semana', 'franja']);
            $table->foreign(['zona_id', 'tipo_servicio_id'])
                ->references(['zona_id', 'tipo_servicio_id'])
                ->on('zona_servicios')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zona_servicio_horarios');
    }
};
