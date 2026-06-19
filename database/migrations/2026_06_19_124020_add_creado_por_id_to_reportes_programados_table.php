<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reportes_programados', function (Blueprint $table) {
            // Dueño del programado: a quién se le notifica cuando el scheduler
            // genera/envía este reporte (no hay usuario en el ciclo de la cola).
            // users no es alcanzable por cascada desde organizaciones → sin
            // conflicto de caminos múltiples; nullOnDelete conserva el programado.
            $table->foreignId('creado_por_id')->nullable()->after('organizacion_id')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reportes_programados', function (Blueprint $table) {
            $table->dropConstrainedForeignId('creado_por_id');
        });
    }
};
