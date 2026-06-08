<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('zonas', function (Blueprint $table) {
            // GeoJSON (FeatureCollection con el polígono de la zona). nvarchar(max) en SQL Server.
            $table->json('geojson')->nullable();
            // Centro del polígono — derivado en el cliente, para centrar el mapa sin parsear la geometría.
            $table->decimal('centro_lat', 10, 7)->nullable();
            $table->decimal('centro_lng', 10, 7)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('zonas', function (Blueprint $table) {
            $table->dropColumn(['geojson', 'centro_lat', 'centro_lng']);
        });
    }
};
