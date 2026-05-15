<?php

namespace Database\Factories;

use App\Models\TipoVehiculo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TipoVehiculo>
 */
class TipoVehiculoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $min = $this->faker->numberBetween(500, 5000);

        return [
            'nombre'      => $this->faker->unique()->word() . ' ' . $this->faker->word(),
            'peso_min_kg' => $min,
            'peso_max_kg' => $min + $this->faker->numberBetween(1000, 20000),
            'activo'      => true,
        ];
    }
}
