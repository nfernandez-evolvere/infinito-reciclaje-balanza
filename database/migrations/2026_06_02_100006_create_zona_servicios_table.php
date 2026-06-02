<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zona_servicios', function (Blueprint $table) {
            $table->foreignId('zona_id')->constrained('zonas')->cascadeOnDelete();
            $table->foreignId('tipo_servicio_id')->constrained('tipos_servicio')->noActionOnDelete();
            $table->primary(['zona_id', 'tipo_servicio_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zona_servicios');
    }
};
