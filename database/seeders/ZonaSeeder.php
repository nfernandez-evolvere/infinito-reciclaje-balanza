<?php

namespace Database\Seeders;

use App\Models\TipoServicio;
use App\Models\Zona;
use App\Models\ZonaTurno;
use Illuminate\Database\Seeder;

class ZonaSeeder extends Seeder
{
    public function run(): void
    {
        // Metadatos geográficos por nombre de zona (compartidos entre servicios).
        $areas = [
            'Zona Norte'      => ['hectareas' => 1850.50, 'barrios' => 12, 'habitantes' => 48000],
            'Zona Sur'        => ['hectareas' => 2200.00, 'barrios' => 15, 'habitantes' => 62000],
            'Zona Centro'     => ['hectareas' => 620.00,  'barrios' => 6,  'habitantes' => 35000],
            'Zona Industrial' => ['hectareas' => 3400.00, 'barrios' => 3,  'habitantes' => 8000],
        ];

        // Cada servicio tiene sus propias zonas. La misma área puede repetirse en
        // distintos servicios — son zonas distintas (modelo 1:N servicio → zonas).
        $porServicio = [
            'Domiciliario' => [
                'Zona Norte'      => ['Diurna', 'Nocturna'],
                'Zona Sur'        => ['Diurna', 'Nocturna'],
                'Zona Centro'     => ['Diurna'],
                'Zona Industrial' => [],
            ],
            'Barrido' => [
                'Zona Norte'  => ['Diurna'],
                'Zona Sur'    => ['Diurna'],
                'Zona Centro' => ['Diurna'],
            ],
            'Voluminoso' => [
                'Zona Sur'    => [],
                'Zona Centro' => [],
            ],
        ];

        foreach ($porServicio as $servicioNombre => $zonas) {
            $servicioId = TipoServicio::where('nombre', $servicioNombre)->value('id');
            if (! $servicioId) {
                continue;
            }

            foreach ($zonas as $zonaNombre => $turnos) {
                $area = $areas[$zonaNombre] ?? ['hectareas' => null, 'barrios' => null, 'habitantes' => null];

                $zona = Zona::firstOrCreate(
                    ['tipo_servicio_id' => $servicioId, 'nombre' => $zonaNombre],
                    [
                        'hectareas'  => $area['hectareas'],
                        'barrios'    => $area['barrios'],
                        'habitantes' => $area['habitantes'],
                        'activo'     => true,
                    ],
                );

                foreach ($turnos as $turno) {
                    ZonaTurno::firstOrCreate([
                        'zona_id' => $zona->id,
                        'turno'   => $turno,
                    ]);
                }
            }
        }
    }
}
