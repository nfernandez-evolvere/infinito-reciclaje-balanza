<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zona_horarios', function (Blueprint $table) {
            $table->foreignId('zona_id')->constrained('zonas')->cascadeOnDelete();
            $table->unsignedTinyInteger('dia_semana'); // 1=Lunes … 7=Domingo
            $table->unsignedTinyInteger('franja');     // orden dentro del día
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->primary(['zona_id', 'dia_semana', 'franja']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zona_horarios');
    }
};
