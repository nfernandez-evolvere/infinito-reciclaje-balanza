<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes_programados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizacion_id')->constrained('organizaciones')->cascadeOnDelete();
            // Dueño del programado: a quién se le notifica cuando el scheduler lo dispara
            // (no hay usuario en el ciclo de la cola). users no es alcanzable por cascada
            // desde organizaciones → sin conflicto; nullOnDelete conserva el programado.
            $table->foreignId('creado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo', 30)->default('informe_mensual'); // informe_mensual | alertas
            $table->string('nombre', 150);
            $table->string('frecuencia', 20)->default('mensual');   // diaria | semanal | quincenal | mensual
            $table->string('cron_expresion', 50)->default('0 8 1 * *');
            $table->json('destinatarios');
            $table->json('opciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('ultimo_envio_at')->nullable();
            $table->timestamp('proximo_envio_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes_programados');
    }
};
