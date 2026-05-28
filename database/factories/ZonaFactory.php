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
}
