<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesajes', function (Blueprint $table) {
            $table->string('motivo_cancelacion', 500)->nullable()->after('editado');
            $table->foreignId('cancelado_por_id')->nullable()->constrained('users')->noActionOnDelete()->after('motivo_cancelacion');
            $table->datetime('cancelado_at')->nullable()->after('cancelado_por_id');
        });
    }

    public function down(): void
    {
        Schema::table('pesajes', function (Blueprint $table) {
            $table->dropForeign(['cancelado_por_id']);
            $table->dropColumn(['motivo_cancelacion', 'cancelado_por_id', 'cancelado_at']);
        });
    }
};
