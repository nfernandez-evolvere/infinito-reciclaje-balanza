<?php

namespace Tests;

use App\Models\Organizacion;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\ActingAsRoles;
use Tests\Concerns\InteractsWithTenants;

abstract class TestCase extends BaseTestCase
{
    use ActingAsRoles;
    use InteractsWithTenants;

    /**
     * Se corre apenas se bootea la app, ANTES de que RefreshDatabase migre.
     * Acá vive la red de seguridad contra la base compartida.
     */
    protected function refreshApplication(): void
    {
        parent::refreshApplication();

        $this->guardAgainstSharedDatabase();
    }

    /**
     * Aborta si la suite apunta a una base que no parece de test.
     *
     * RefreshDatabase corre migrate:fresh (dropea TODAS las tablas). Contra la base
     * compartida (prefijos por proyecto) sería catastrófico. Exigimos que el nombre
     * de la base delate que es de test; SQLite en memoria también es seguro.
     */
    protected function guardAgainstSharedDatabase(): void
    {
        $connection = config('database.default');
        $database = strtolower((string) config("database.connections.{$connection}.database"));

        if ($connection === 'sqlite' && in_array($database, [':memory:', ''], true)) {
            return;
        }

        if (! str_contains($database, 'test')) {
            throw new \RuntimeException(
                "Tests abortados: la conexión '{$connection}' apunta a la base '{$database}', ".
                'que no parece de test (su nombre no contiene "test"). RefreshDatabase la borraría. '.
                'Configurá .env.testing con una database dedicada (ej: infinito_balanza_testing).'
            );
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Los feature tests no dependen de un build de Vite: sin esto, el @vite(...)
        // del layout exigiría el manifest y el CI necesitaría correr `npm run build`.
        $this->withoutVite();

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
