<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reportes_programados', function (Blueprint $table) {
            // Fecha ancla del primer envío elegida por el usuario. Define la fase
            // del cronograma (elegís el 1 → corre todos los 1) y conserva el día
            // ancla mensual a través de meses cortos (31 → 28/02 → 31/03).
            // Nullable: los programados creados antes de esta opción siguen
            // anclados a su proximo_envio_at vigente.
            $table->date('inicio_en')->nullable()->after('cron_expresion');
        });
    }

    public function down(): void
    {
        Schema::table('reportes_programados', function (Blueprint $table) {
            $table->dropColumn('inicio_en');
        });
    }
};
