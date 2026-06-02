<?php

namespace Database\Factories;

use App\Models\TipoServicio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TipoServicio>
 */
class TipoServicioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->unique()->word().' '.$this->faker->unique()->numberBetween(1, 9999),
            'activo' => true,
        ];
    }
}
