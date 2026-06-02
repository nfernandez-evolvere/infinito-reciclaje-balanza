<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Adjunta cada usuario creado a la organización activa del contexto.
     *
     * Espeja el patrón del trait BelongsToOrganizacion: si `app('organizacion')`
     * está bindeada (siempre en tests, vía TestCase::setUp), el usuario queda como
     * miembro de esa org a través del pivot `organizacion_user`. Fuera de un request
     * o test el binding default es null, así que en seeders/consola no adjunta nada.
     * El super_admin es cross-organización, por eso se excluye.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            if ($user->role === 'super_admin') {
                return;
            }

            $org = app()->bound('organizacion') ? app('organizacion') : null;

            if ($org) {
                $user->organizaciones()->attach($org->id);
            }
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'role'              => 'operador',
            'onboarding_visto'  => false,
            'activo'            => true,
            'remember_token'    => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }
}
