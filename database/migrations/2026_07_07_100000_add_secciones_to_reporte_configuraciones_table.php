<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reporte_configuraciones', function (Blueprint $table) {
            // Default de la organización: qué secciones incluye el informe mensual v2
            // por formato — {"pdf": [claves], "excel": [claves]} (ver ReporteSecciones).
            // Null = todas. Cada programado puede sobreescribirlo con
            // opciones['secciones'] y la descarga manual con secciones[] ad-hoc.
            $table->json('secciones')->nullable()->after('servicios');
        });
    }

    public function down(): void
    {
        Schema::table('reporte_configuraciones', function (Blueprint $table) {
            $table->dropColumn('secciones');
        });
    }
};
