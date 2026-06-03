<?php

namespace Tests\Concerns;

use App\Models\Organizacion;
use App\Models\User;

/**
 * Helpers para los tests de aislamiento multi-tenant.
 *
 * El global scope BelongsToOrganizacion lee una sola instancia app('organizacion'),
 * así que para simular dos organizaciones hay que rebindear el tenant activo entre
 * el armado del escenario y los asserts.
 */
trait InteractsWithTenants
{
    /**
     * Crea (o reutiliza) una organización por nombre.
     *
     * TestCase::setUp ya crea y bindea la organización de prueba base; este helper
     * sirve para levantar una SEGUNDA organización en los tests de aislamiento.
     */
    protected function createOrganizacion(string $nombre): Organizacion
    {
        return Organizacion::firstOrCreate(
            ['nombre' => $nombre],
            ['activo' => true],
        );
    }

    /**
     * Ejecuta el callback con $org como tenant activo y restaura el binding anterior.
     *
     * @template TReturn
     *
     * @param  callable():TReturn  $fn
     * @return TReturn
     */
    protected function actingInOrg(Organizacion $org, callable $fn): mixed
    {
        $previo = app()->bound('organizacion') ? app('organizacion') : null;
        app()->instance('organizacion', $org);

        try {
            return $fn();
        } finally {
            $previo
                ? app()->instance('organizacion', $previo)
                : app()->forgetInstance('organizacion');
        }
    }

    /**
     * Crea un usuario perteneciente a $org sin importar cuál es el tenant activo.
     *
     * El factory adjunta cada usuario a la org bindeada; bindeamos $org sólo para
     * la creación y restauramos. Útil para armar el escenario "usuario de Org B".
     */
    protected function userInOrg(Organizacion $org, array $attrs = []): User
    {
        return $this->actingInOrg($org, fn () => User::factory()->create($attrs));
    }
}
