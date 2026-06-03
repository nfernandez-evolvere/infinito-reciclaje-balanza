<?php

namespace Database\Factories;

use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehiculo>
 */
class VehiculoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patente'          => strtoupper($this->faker->unique()->bothify('???###')),
            'numero_interno'   => (string) $this->faker->unique()->numberBetween(1, 9999),
            'tara_kg'          => $this->faker->numberBetween(3000, 8000),
            'tipo_vehiculo_id' => TipoVehiculo::factory(),
            'titular'          => 'Municipalidad de '.$this->faker->city(),
            'capacidad_kg'     => null,
            'observaciones'    => null,
            'activo'           => true,
        ];
    }
}
