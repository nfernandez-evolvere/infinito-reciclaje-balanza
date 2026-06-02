<?php

namespace Tests;

use App\Models\Organizacion;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Todos los modelos con BelongsToOrganizacion requieren organizacion_id.
        // Creamos una organización de prueba y la bindeamos al container para que
        // el trait la asigne automáticamente al crear cada modelo.
        // Los unit tests sin RefreshDatabase no tienen tablas: el chequeo de schema
        // evita el "no such table: organizaciones" en ese contexto (ellos no la usan).
        if ($this->app->bound('db') && Schema::hasTable('organizaciones')) {
            $org = Organizacion::firstOrCreate(
                ['nombre' => 'Organización Test'],
                ['activo' => true]
            );
            $this->app->instance('organizacion', $org);
        }
    }
}
