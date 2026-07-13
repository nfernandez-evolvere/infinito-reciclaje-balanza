<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zonas', function (Blueprint $table) {
            $table->id();
            // organizacion_id se conserva denormalizado (derivable del servicio) para que el
            // global scope de BelongsToOrganizacion siga resolviendo sin un join extra.
            $table->foreignId('organizacion_id')->constrained('organizaciones')->cascadeOnDelete();
            // Cada zona pertenece a exactamente un servicio (tipo de servicio). noActionOnDelete:
            // organizaciones ya cascadea a zonas (path directo) y a tipos_servicio; un segundo
            // camino con cascade desde el mismo ancestro lo rechaza SQL Server.
            $table->foreignId('tipo_servicio_id')->constrained('tipos_servicio')->noActionOnDelete();
            $table->string('nombre', 150);
            $table->decimal('hectareas', 10, 2)->nullable();
            $table->integer('barrios')->nullable();
            $table->integer('habitantes')->nullable();
            // Geometría del área como GeoJSON (FeatureCollection con el polígono).
            // nvarchar(max) en SQL Server; se dibuja/edita con Leaflet + Geoman.
            $table->json('geojson')->nullable();
            // Centro del polígono, derivado en el cliente para centrar el mapa.
            $table->decimal('centro_lat', 10, 7)->nullable();
            $table->decimal('centro_lng', 10, 7)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // El nombre es único dentro de cada servicio: dos servicios pueden tener su "Zona Norte".
            $table->unique(['tipo_servicio_id', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zonas');
    }
};
