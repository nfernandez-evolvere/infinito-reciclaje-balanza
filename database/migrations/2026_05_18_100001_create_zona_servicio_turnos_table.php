<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zona_servicio_turnos', function (Blueprint $table) {
            $table->foreignId('zona_id');
            $table->foreignId('tipo_servicio_id');
            $table->string('turno', 10);
            $table->primary(['zona_id', 'tipo_servicio_id', 'turno']);
            $table->foreign(['zona_id', 'tipo_servicio_id'])
                ->references(['zona_id', 'tipo_servicio_id'])
                ->on('zona_servicios')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zona_servicio_turnos');
    }
};
