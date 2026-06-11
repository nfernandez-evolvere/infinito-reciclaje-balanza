<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reportes_generados', function (Blueprint $table) {
            // Datos congelados del reporte tal como se generó/envió: agregados,
            // pivots, detalle aplanado, mapa de calor, alertas y config de marca.
            // Permite re-descargar el reporte idéntico sin recalcular sobre los
            // pesajes vivos (la tara de un vehículo puede cambiar después).
            // En SQL Server json() → nvarchar(max); en SQLite (tests) → text.
            $table->json('snapshot')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reportes_generados', function (Blueprint $table) {
            $table->dropColumn('snapshot');
        });
    }
};
