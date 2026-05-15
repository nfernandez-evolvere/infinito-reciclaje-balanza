<?php

namespace Database\Seeders;

use App\Models\TipoVehiculo;
use Illuminate\Database\Seeder;

class TipoVehiculoSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'Compactador', 'peso_min_kg' => 10000, 'peso_max_kg' => 26500],
            ['nombre' => 'Volcador',    'peso_min_kg' => 13000, 'peso_max_kg' => 30000],
            ['nombre' => 'Volquete',    'peso_min_kg' => 7000,  'peso_max_kg' => 20000],
            ['nombre' => 'Particular',  'peso_min_kg' => 1000,  'peso_max_kg' => 5000],
        ];

        foreach ($tipos as $tipo) {
            TipoVehiculo::firstOrCreate(['nombre' => $tipo['nombre']], $tipo);
        }
    }
}
