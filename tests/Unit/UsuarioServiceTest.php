<?php

namespace Tests\Unit;

use App\Models\User;
use App\Repositories\UsuarioRepository;
use App\Services\UsuarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UsuarioServiceTest extends TestCase
{
    use RefreshDatabase;

    private UsuarioService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UsuarioService(new UsuarioRepository());
    }

    // — Crear ——————————————————————————————————————————————————

    #[Test]
    public function test_crear_stores_record(): void
    {
        $usuario = $this->service->crear([
            'name'     => 'Roberto García',
            'email'    => 'roberto@municipio.gob.ar',
            'password' => 'password123',
            'role'     => 'operador',
            'activo'   => true,
        ]);

        $this->assertInstanceOf(User::class, $usuario);
        $this->assertDatabaseHas('users', [
            'name'  => 'Roberto García',
            'email' => 'roberto@municipio.gob.ar',
            'role'  => 'operador',
        ]);
    }

    #[Test]
    public function test_crear_hashes_password(): void
    {
        $this->service->crear([
            'name'     => 'Roberto García',
            'email'    => 'roberto@municipio.gob.ar',
            'password' => 'password123',
            'role'     => 'operador',
            'activo'   => true,
        ]);

        $usuario = User::where('email', 'roberto@municipio.gob.ar')->first();

        $this->assertNotEquals('password123', $usuario->password);
        $this->assertTrue(Hash::check('password123', $usuario->password));
    }

    #[Test]
    public function test_crear_sets_activo_true(): void
    {
        $usuario = $this->service->crear([
            'name'     => 'Roberto García',
            'email'    => 'roberto@municipio.gob.ar',
            'password' => 'password123',
            'role'     => 'operador',
            'activo'   => true,
        ]);

        $this->assertDatabaseHas('users', ['id' => $usuario->id, 'activo' => true]);
    }

    // — Actualizar ——————————————————————————————————————————————

    #[Test]
    public function test_actualizar_modifies_name_email_role(): void
    {
        $usuario = User::factory()->create(['name' => 'Nombre Viejo', 'role' => 'operador']);

        $this->service->actualizar($usuario, [
            'name'  => 'Nombre Nuevo',
            'email' => 'nuevo@email.com',
            'role'  => 'admin',
        ]);

        $this->assertDatabaseHas('users', [
            'id'    => $usuario->id,
            'name'  => 'Nombre Nuevo',
            'email' => 'nuevo@email.com',
            'role'  => 'admin',
        ]);
    }

    #[Test]
    public function test_actualizar_does_not_change_password(): void
    {
        $usuario          = User::factory()->create();
        $originalPassword = $usuario->password;

        $this->service->actualizar($usuario, [
            'name'  => 'Otro Nombre',
            'email' => $usuario->email,
            'role'  => $usuario->role,
        ]);

        $this->assertDatabaseHas('users', [
            'id'       => $usuario->id,
            'password' => $originalPassword,
        ]);
    }

    // — Toggle activo ——————————————————————————————————————————

    #[Test]
    public function test_desactivar_sets_activo_false(): void
    {
        $usuario = User::factory()->create(['activo' => true]);

        $this->service->desactivar($usuario);

        $this->assertDatabaseHas('users', ['id' => $usuario->id, 'activo' => false]);
    }

    #[Test]
    public function test_activar_sets_activo_true(): void
    {
        $usuario = User::factory()->inactive()->create();

        $this->service->activar($usuario);

        $this->assertDatabaseHas('users', ['id' => $usuario->id, 'activo' => true]);
    }

    #[Test]
    public function test_desactivar_does_not_affect_other_users(): void
    {
        $objetivo = User::factory()->create(['activo' => true]);
        $otro     = User::factory()->create(['activo' => true]);

        $this->service->desactivar($objetivo);

        $this->assertDatabaseHas('users', ['id' => $otro->id, 'activo' => true]);
    }

    // — Resetear contraseña ————————————————————————————————————

    #[Test]
    public function test_resetear_password_hashes_new_password(): void
    {
        $usuario = User::factory()->create();

        $this->service->resetearPassword($usuario, 'nuevaPassword123');

        $usuario->refresh();
        $this->assertNotEquals('nuevaPassword123', $usuario->password);
        $this->assertTrue(Hash::check('nuevaPassword123', $usuario->password));
    }

    #[Test]
    public function test_resetear_password_replaces_old_hash(): void
    {
        $usuario          = User::factory()->create();
        $originalPassword = $usuario->password;

        $this->service->resetearPassword($usuario, 'nuevaPassword123');

        $usuario->refresh();
        $this->assertNotEquals($originalPassword, $usuario->password);
        $this->assertFalse(Hash::check('password', $usuario->password));
    }

    // — Listar ——————————————————————————————————————————————————

    #[Test]
    public function test_listar_returns_all_without_filters(): void
    {
        User::factory()->count(3)->create();

        $resultado = $this->service->listar([]);

        $this->assertCount(3, $resultado);
    }

    #[Test]
    public function test_listar_filters_by_name(): void
    {
        User::factory()->create(['name' => 'Roberto García']);
        User::factory()->create(['name' => 'Ignacio López']);

        $resultado = $this->service->listar(['buscar' => 'Roberto']);

        $this->assertCount(1, $resultado);
        $this->assertEquals('Roberto García', $resultado->first()->name);
    }

    #[Test]
    public function test_listar_filters_by_email(): void
    {
        User::factory()->create(['email' => 'roberto@municipio.gob.ar']);
        User::factory()->create(['email' => 'nacho@municipio.gob.ar']);

        $resultado = $this->service->listar(['buscar' => 'roberto']);

        $this->assertCount(1, $resultado);
        $this->assertEquals('roberto@municipio.gob.ar', $resultado->first()->email);
    }

    #[Test]
    public function test_listar_buscar_matches_partial_name(): void
    {
        User::factory()->create(['name' => 'Roberto García']);
        User::factory()->create(['name' => 'Ignacio López']);

        $resultado = $this->service->listar(['buscar' => 'garc']);

        $this->assertCount(1, $resultado);
    }

    #[Test]
    public function test_listar_filters_by_role(): void
    {
        User::factory()->count(2)->create(['role' => 'operador']);
        User::factory()->create(['role' => 'admin']);

        $operadores = $this->service->listar(['role' => 'operador']);
        $admins     = $this->service->listar(['role' => 'admin']);

        $this->assertCount(2, $operadores);
        $this->assertCount(1, $admins);
    }

    #[Test]
    public function test_listar_filters_by_activo(): void
    {
        User::factory()->count(2)->create(['activo' => true]);
        User::factory()->inactive()->create();

        $activos   = $this->service->listar(['activo' => '1']);
        $inactivos = $this->service->listar(['activo' => '0']);

        $this->assertCount(2, $activos);
        $this->assertCount(1, $inactivos);
    }

    #[Test]
    public function test_listar_empty_filter_strings_return_all(): void
    {
        User::factory()->create(['activo' => true]);
        User::factory()->inactive()->create();
        User::factory()->create(['role' => 'admin']);

        $resultado = $this->service->listar(['buscar' => '', 'role' => '', 'activo' => '']);

        $this->assertCount(3, $resultado);
    }

    #[Test]
    public function test_listar_returns_paginator(): void
    {
        User::factory()->count(5)->create();

        $resultado = $this->service->listar([]);

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $resultado);
    }
}
