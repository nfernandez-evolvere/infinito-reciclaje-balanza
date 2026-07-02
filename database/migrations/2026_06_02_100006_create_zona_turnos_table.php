<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zona_turnos', function (Blueprint $table) {
            $table->foreignId('zona_id')->constrained('zonas')->cascadeOnDelete();
            $table->string('turno', 10);
            $table->primary(['zona_id', 'turno']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zona_turnos');
    }
};
