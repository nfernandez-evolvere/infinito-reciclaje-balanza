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
            [
                'nombre'      => 'Domiciliario',
                'descripcion' => 'Recolección regular de residuos domiciliarios en frecuencia establecida por zona.',
                'vehiculos'   => [$compactador],
            ],
            [
                'nombre'      => 'Voluminoso',
                'descripcion' => 'Retiro de residuos de gran volumen (muebles, escombros menores, poda) fuera de la recolección regular.',
                'vehiculos'   => [$compactador],
            ],
            [
                'nombre'      => 'Barrido',
                'descripcion' => 'Limpieza y barrido de calles y espacios públicos, con recolección de lo acumulado.',
                'vehiculos'   => [$volcador],
            ],
            [
                'nombre'      => 'Servicios Especiales',
                'descripcion' => 'Operativos puntuales fuera del esquema regular, solicitados por el organismo contratante.',
                'vehiculos'   => [$volcador],
            ],
            [
                'nombre'      => 'Centros de Transferencia',
                'descripcion' => 'Traslado de residuos desde centros de transferencia hacia el predio de disposición final.',
                'vehiculos'   => [$volquete],
            ],
        ];

        foreach ($servicios as $data) {
            $tipo = TipoServicio::firstOrCreate(
                ['nombre' => $data['nombre']],
                ['descripcion' => $data['descripcion']]
            );
            $tipo->fill(['descripcion' => $data['descripcion']])->save();
            $tipo->tiposVehiculo()->sync(array_filter($data['vehiculos']));
        }
    }
}
