<?php

namespace Tests;

use App\Models\Organizacion;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Todos los modelos con BelongsToOrganizacion requieren organizacion_id.
        // Creamos una organización de prueba y la bindeamos al container para que
        // el trait la asigne automáticamente al crear cada modelo.
        if ($this->app->bound('db')) {
            $org = Organizacion::firstOrCreate(
                ['slug' => 'test'],
                ['nombre' => 'Organización Test', 'activo' => true]
            );
            $this->app->instance('organizacion', $org);
        }
    }
}
