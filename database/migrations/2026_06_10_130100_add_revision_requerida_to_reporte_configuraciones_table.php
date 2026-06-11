<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reporte_configuraciones', function (Blueprint $table) {
            // Default global de la organización: los reportes programados quedan
            // pendientes de revisión antes de enviarse. Activado por defecto:
            // ningún envío sale sin aprobación salvo decisión explícita. Cada
            // programado puede sobreescribirlo con opciones['revision']
            // (heredar|revisar|directo).
            $table->boolean('revision_requerida')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('reporte_configuraciones', function (Blueprint $table) {
            $table->dropColumn('revision_requerida');
        });
    }
};
