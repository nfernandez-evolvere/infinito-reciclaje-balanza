<?php

namespace Database\Factories;

use App\Models\Zona;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Zona>
 */
class ZonaFactory extends Factory
{
    protected $model = Zona::class;

    public function definition(): array
    {
        return [
            'nombre'     => $this->faker->unique()->city(),
            'hectareas'  => $this->faker->randomFloat(1, 100, 2000),
            'habitantes' => $this->faker->numberBetween(1000, 50000),
            'barrios'    => null,
            'activo'     => true,
        ];
    }

    public function inactiva(): static
    {
        return $this->state(fn (array $attrs) => ['activo' => false]);
    }

    public function conGeometria(): static
    {
        return $this->state(fn (array $attrs) => [
            'geojson'    => json_encode([
                'type'     => 'FeatureCollection',
                'features' => [[
                    'type'       => 'Feature',
                    'properties' => (object) [],
                    'geometry'   => [
                        'type'        => 'Polygon',
                        'coordinates' => [[
                            [-58.84, -27.47],
                            [-58.82, -27.47],
                            [-58.82, -27.45],
                            [-58.84, -27.45],
                            [-58.84, -27.47],
                        ]],
                    ],
                ]],
            ]),
            'centro_lat' => -27.46,
            'centro_lng' => -58.83,
        ]);
    }
}
