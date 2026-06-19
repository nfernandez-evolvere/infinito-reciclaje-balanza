<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alertas', function (Blueprint $table) {
            // Vincula la notificación con el reporte que la originó (deep-link al
            // historial). FK secundaria: alertas y reportes_generados ya cascadean
            // de organizaciones → segundo camino → noActionOnDelete (regla SQL Server).
            $table->foreignId('reporte_generado_id')->nullable()->after('zona_id')
                ->constrained('reportes_generados')->noActionOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('alertas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reporte_generado_id');
        });
    }
};
