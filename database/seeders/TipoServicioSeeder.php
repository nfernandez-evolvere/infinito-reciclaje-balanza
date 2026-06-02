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
        $volcador = TipoVehiculo::where('nombre', 'Volcador')->value('id');
        $volquete = TipoVehiculo::where('nombre', 'Volquete')->value('id');

        $servicios = [
            ['nombre' => 'Domiciliario',            'vehiculos' => [$compactador]],
            ['nombre' => 'Voluminoso',               'vehiculos' => [$compactador]],
            ['nombre' => 'Barrido',                  'vehiculos' => [$volcador]],
            ['nombre' => 'Servicios Especiales',     'vehiculos' => [$volcador]],
            ['nombre' => 'Centros de Transferencia', 'vehiculos' => [$volquete]],
        ];

        foreach ($servicios as $data) {
            $tipo = TipoServicio::firstOrCreate(['nombre' => $data['nombre']]);
            $tipo->tiposVehiculo()->sync(array_filter($data['vehiculos']));
        }
    }
}
