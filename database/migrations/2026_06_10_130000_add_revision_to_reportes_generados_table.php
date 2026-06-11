<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reportes_generados', function (Blueprint $table) {
            // Auditoría del flujo de revisión: quién aprobó/descartó y cuándo.
            // FK a users con noAction (SQL Server rechaza cascadas múltiples
            // que convergen en organizaciones), como usuario_id en esta tabla.
            $table->foreignId('revisado_por_id')->nullable()->constrained('users')->noActionOnDelete();
            $table->dateTime('revisado_at')->nullable();
            // Con revisión, la generación (created_at) y el envío real pueden
            // separarse días: enviado_at registra cuándo salió el mail.
            $table->dateTime('enviado_at')->nullable();
            $table->string('motivo_descarte', 500)->nullable();

            // Contador de pendientes de revisión por organización.
            $table->index(['organizacion_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::table('reportes_generados', function (Blueprint $table) {
            $table->dropIndex(['organizacion_id', 'estado']);
            $table->dropConstrainedForeignId('revisado_por_id');
            $table->dropColumn(['revisado_at', 'enviado_at', 'motivo_descarte']);
        });
    }
};
