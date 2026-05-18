<?php

namespace Database\Seeders;

use App\Models\TipoServicio;
use App\Models\Zona;
use App\Models\ZonaServicio;
use App\Models\ZonaServicioTurno;
use Illuminate\Database\Seeder;

class ZonaSeeder extends Seeder
{
    public function run(): void
    {
        $domiciliario = TipoServicio::where('nombre', 'Domiciliario')->value('id');
        $barrido      = TipoServicio::where('nombre', 'Barrido')->value('id');
        $voluminoso   = TipoServicio::where('nombre', 'Voluminoso')->value('id');

        $zonas = [
            [
                'nombre'     => 'Zona Norte',
                'hectareas'  => 1850.50,
                'barrios'    => 12,
                'habitantes' => 48000,
                'servicios'  => [
                    ['id' => $domiciliario, 'turnos' => ['Diurna', 'Nocturna']],
                    ['id' => $barrido,      'turnos' => ['Diurna']],
                ],
            ],
            [
                'nombre'     => 'Zona Sur',
                'hectareas'  => 2200.00,
                'barrios'    => 15,
                'habitantes' => 62000,
                'servicios'  => [
                    ['id' => $domiciliario, 'turnos' => ['Diurna', 'Nocturna']],
                    ['id' => $barrido,      'turnos' => ['Diurna']],
                    ['id' => $voluminoso,   'turnos' => []],
                ],
            ],
            [
                'nombre'     => 'Zona Centro',
                'hectareas'  => 620.00,
                'barrios'    => 6,
                'habitantes' => 35000,
                'servicios'  => [
                    ['id' => $domiciliario, 'turnos' => ['Diurna']],
                    ['id' => $barrido,      'turnos' => ['Diurna']],
                    ['id' => $voluminoso,   'turnos' => []],
                ],
            ],
            [
                'nombre'     => 'Zona Industrial',
                'hectareas'  => 3400.00,
                'barrios'    => 3,
                'habitantes' => 8000,
                'servicios'  => [
                    ['id' => $domiciliario, 'turnos' => []],
                ],
            ],
        ];

        foreach ($zonas as $data) {
            $zona = Zona::firstOrCreate(
                ['nombre' => $data['nombre']],
                [
                    'hectareas'  => $data['hectareas'],
                    'barrios'    => $data['barrios'],
                    'habitantes' => $data['habitantes'],
                    'activo'     => true,
                ],
            );

            foreach ($data['servicios'] as $servicio) {
                if (!$servicio['id']) {
                    continue;
                }

                $zs = ZonaServicio::firstOrCreate([
                    'zona_id'          => $zona->id,
                    'tipo_servicio_id' => $servicio['id'],
                ]);

                foreach ($servicio['turnos'] as $turno) {
                    ZonaServicioTurno::firstOrCreate([
                        'zona_id'          => $zona->id,
                        'tipo_servicio_id' => $servicio['id'],
                        'turno'            => $turno,
                    ]);
                }
            }
        }
    }
}
