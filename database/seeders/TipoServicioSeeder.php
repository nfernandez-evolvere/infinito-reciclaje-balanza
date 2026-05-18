<?php

namespace Database\Seeders;

use App\Models\TipoServicio;
use App\Models\TipoVehiculo;
use Illuminate\Database\Seeder;

class TipoServicioSeeder extends Seeder
{
    public function run(): void
    {
        $compactador = TipoVehiculo::where('nombre', 'Compactador')->value('id');
        $volcador    = TipoVehiculo::where('nombre', 'Volcador')->value('id');
        $volquete    = TipoVehiculo::where('nombre', 'Volquete')->value('id');

        $servicios = [
            ['nombre' => 'Domiciliario',           'tipo_vehiculo_sugerido_id' => $compactador],
            ['nombre' => 'Voluminoso',              'tipo_vehiculo_sugerido_id' => $compactador],
            ['nombre' => 'Barrido',                 'tipo_vehiculo_sugerido_id' => $volcador],
            ['nombre' => 'Servicios Especiales',    'tipo_vehiculo_sugerido_id' => $volcador],
            ['nombre' => 'Centros de Transferencia','tipo_vehiculo_sugerido_id' => $volquete],
        ];

        foreach ($servicios as $servicio) {
            TipoServicio::firstOrCreate(
                ['nombre' => $servicio['nombre']],
                $servicio,
            );
        }
    }
}
