<?php

namespace Database\Seeders;

use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use Illuminate\Database\Seeder;

class VehiculoSeeder extends Seeder
{
    public function run(): void
    {
        $compactador = TipoVehiculo::where('nombre', 'Compactador')->first();
        $volcador = TipoVehiculo::where('nombre', 'Volcador')->first();
        $volquete = TipoVehiculo::where('nombre', 'Volquete')->first();
        $particular = TipoVehiculo::where('nombre', 'Particular')->first();

        $vehiculos = [
            [
                'patente'          => 'ABC123',
                'numero_interno'   => '001',
                'tara_kg'          => 8500,
                'tipo_vehiculo_id' => $compactador?->id,
                'titular'          => 'Municipalidad de Corrientes',
                'capacidad_kg'     => 10000,
                'observaciones'    => null,
            ],
            [
                'patente'          => 'DEF456',
                'numero_interno'   => '002',
                'tara_kg'          => 9200,
                'tipo_vehiculo_id' => $compactador?->id,
                'titular'          => 'Municipalidad de Corrientes',
                'capacidad_kg'     => 10000,
                'observaciones'    => null,
            ],
            [
                'patente'          => 'GHI789',
                'numero_interno'   => '003',
                'tara_kg'          => 11000,
                'tipo_vehiculo_id' => $volcador?->id,
                'titular'          => 'Municipalidad de Corrientes',
                'capacidad_kg'     => 15000,
                'observaciones'    => null,
            ],
            [
                'patente'          => 'JKL012',
                'numero_interno'   => '004',
                'tara_kg'          => 6500,
                'tipo_vehiculo_id' => $volquete?->id,
                'titular'          => 'Municipalidad de Corrientes',
                'capacidad_kg'     => null,
                'observaciones'    => 'Requiere acompañante para maniobras en espacios reducidos.',
            ],
            [
                'patente'          => 'MNO345',
                'numero_interno'   => '005',
                'tara_kg'          => 1800,
                'tipo_vehiculo_id' => $particular?->id,
                'titular'          => 'Municipalidad de Corrientes',
                'capacidad_kg'     => null,
                'observaciones'    => null,
            ],
        ];

        foreach ($vehiculos as $vehiculo) {
            if ($vehiculo['tipo_vehiculo_id'] === null) {
                continue;
            }
            Vehiculo::firstOrCreate(
                ['patente' => $vehiculo['patente']],
                $vehiculo
            );
        }
    }
}
