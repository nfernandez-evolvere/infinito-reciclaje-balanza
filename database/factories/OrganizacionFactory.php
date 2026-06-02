<?php

namespace Database\Factories;

use App\Models\Organizacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Organizacion>
 */
class OrganizacionFactory extends Factory
{
    protected $model = Organizacion::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->unique()->company(),
            'activo' => true,
        ];
    }
}
