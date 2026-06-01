<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reporte_configuraciones', function (Blueprint $table) {
            $table->json('ai_prompts')->nullable()->after('ai_modelo');
        });
    }

    public function down(): void
    {
        Schema::table('reporte_configuraciones', function (Blueprint $table) {
            $table->dropColumn('ai_prompts');
        });
    }
};
