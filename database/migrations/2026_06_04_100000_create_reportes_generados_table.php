<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes_generados', function (Blueprint $table) {
            $table->id();
            // organizacion_id / usuario_id: noAction como el resto del proyecto
            // (SQL Server rechaza múltiples cascadas que convergen en organizaciones).
            $table->foreignId('organizacion_id')->constrained('organizaciones')->noActionOnDelete();
            // usuario_id nullable: los envíos programados los dispara el job (sin usuario).
            $table->foreignId('usuario_id')->nullable()->constrained('users')->noActionOnDelete();
            // Si se elimina el programado, el historial se conserva (queda huérfano con null).
            $table->foreignId('reporte_programado_id')->nullable()->constrained('reportes_programados')->nullOnDelete();
            $table->string('origen', 20);                 // manual | programado
            $table->string('tipo', 30);                   // informe_mensual | alertas
            $table->string('formato', 20);                // pdf | excel | pdf+excel
            $table->date('periodo_desde');
            $table->date('periodo_hasta');
            $table->json('filtros')->nullable();          // zona_id, tipo_servicio_id, tipo_vehiculo_id
            $table->json('destinatarios')->nullable();    // emails (solo envíos)
            $table->string('estado', 20)->default('generado'); // generado | enviado | fallido
            $table->string('error', 500)->nullable();     // detalle si estado = fallido
            $table->text('conclusiones')->nullable();     // narrativa IA preservada del envío
            $table->timestamps();

            $table->index(['organizacion_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes_generados');
    }
};
