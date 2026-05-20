<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('zonas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizacion_id')->constrained('organizaciones')->cascadeOnDelete();
            $table->string('nombre', 150);
            $table->decimal('hectareas', 10, 2)->nullable();
            $table->integer('barrios')->nullable();
            $table->integer('habitantes')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['organizacion_id', 'nombre']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zonas');
    }
};
