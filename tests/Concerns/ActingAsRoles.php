<?php

namespace Tests\Concerns;

use App\Models\User;

/**
 * Helpers para crear usuarios autenticables por rol.
 *
 * Centraliza los admin()/operador()/superAdmin() que antes se duplicaban en
 * ~7 archivos de test. Cada método acepta atributos extra para sobreescribir
 * el estado por defecto del factory (ej: $this->admin(['name' => 'Ana'])).
 */
trait ActingAsRoles
{
    protected function admin(array $attrs = []): User
    {
        return User::factory()->admin()->create($attrs);
    }

    protected function operador(array $attrs = []): User
    {
        return User::factory()->create($attrs);
    }

    protected function superAdmin(array $attrs = []): User
    {
        return User::factory()->state(['role' => 'super_admin'])->create($attrs);
    }
}
