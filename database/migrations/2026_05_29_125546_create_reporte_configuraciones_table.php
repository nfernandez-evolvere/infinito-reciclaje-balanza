<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reporte_configuraciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizacion_id')->unique()->constrained('organizaciones')->cascadeOnDelete();
            $table->string('municipalidad_nombre', 200)->default('Municipalidad');
            $table->text('intro_empresa')->nullable();
            $table->json('servicios')->nullable();
            $table->boolean('ai_enabled')->default(false);
            $table->string('ai_proveedor', 50)->default('gemini');
            $table->text('ai_api_key')->nullable();
            $table->string('ai_modelo', 100)->default('gemini-2.0-flash-lite');
            $table->boolean('tipo_informe_mensual_activo')->default(true);
            $table->boolean('tipo_alertas_activo')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporte_configuraciones');
    }
};
