<?php

namespace Database\Factories;

use App\Models\Pesaje;
use App\Models\TipoServicio;
use App\Models\User;
use App\Models\Vehiculo;
use App\Models\Zona;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Pesaje>
 */
class PesajeFactory extends Factory
{
    protected $model = Pesaje::class;

    public function definition(): array
    {
        $bruto = $this->faker->numberBetween(5000, 20000);
        $tara = $this->faker->numberBetween(2000, $bruto - 500);
        $neto = $bruto - $tara;

        return [
            'uuid'               => (string) Str::uuid(),
            'vehiculo_id'        => Vehiculo::factory(),
            'operador_id'        => User::factory(),
            'tipo_servicio_id'   => TipoServicio::factory(),
            'zona_id'            => Zona::factory(),
            'turno'              => $this->faker->randomElement(['Mañana', 'Tarde', 'Noche']),
            'peso_bruto_kg'      => $bruto,
            'peso_tara_kg'       => $tara,
            'peso_neto_kg'       => $neto,
            'alerta_peso'        => false,
            'estado'             => 'Cerrado',
            'editado'            => false,
            'observaciones'      => null,
            'hora_salida'        => null,
            'bruto_salida_kg'    => null,
            'motivo_cancelacion' => null,
            'cancelado_por_id'   => null,
            'cancelado_at'       => null,
        ];
    }

    public function cancelado(): static
    {
        return $this->state(fn () => ['estado' => 'Cancelado']);
    }

    public function enPredio(): static
    {
        return $this->state(fn () => ['estado' => 'En predio']);
    }
}
