<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reporte_destinatarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizacion_id')->constrained('organizaciones')->cascadeOnDelete();
            $table->string('email');
            $table->string('nombre')->nullable();
            $table->unsignedInteger('uso_count')->default(1);
            $table->timestamps();

            $table->unique(['organizacion_id', 'email']);
            $table->index(['organizacion_id', 'uso_count']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporte_destinatarios');
    }
};
