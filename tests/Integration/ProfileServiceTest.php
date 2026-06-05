<?php

namespace Tests\Integration;

use App\Models\User;
use App\Repositories\UsuarioRepository;
use App\Services\ProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProfileService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProfileService(new UsuarioRepository);
    }

    // — actualizarNombre ————————————————————————————————————————

    #[Test]
    public function test_actualizar_nombre_changes_name(): void
    {
        $user = User::factory()->create(['name' => 'Nombre Viejo']);

        $resultado = $this->service->actualizarNombre($user, 'Nombre Nuevo');

        $this->assertInstanceOf(User::class, $resultado);
        $this->assertEquals('Nombre Nuevo', $resultado->name);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Nombre Nuevo']);
    }

    #[Test]
    public function test_actualizar_nombre_does_not_touch_email_or_password(): void
    {
        $user = User::factory()->create(['email' => 'fijo@test.com']);
        $originalPassword = $user->password;

        $this->service->actualizarNombre($user, 'Otro Nombre');

        $this->assertDatabaseHas('users', [
            'id'       => $user->id,
            'email'    => 'fijo@test.com',
            'password' => $originalPassword,
        ]);
    }

    #[Test]
    public function test_actualizar_nombre_does_not_affect_other_users(): void
    {
        $user = User::factory()->create(['name' => 'Objetivo']);
        $otro = User::factory()->create(['name' => 'Intacto']);

        $this->service->actualizarNombre($user, 'Cambiado');

        $this->assertDatabaseHas('users', ['id' => $otro->id, 'name' => 'Intacto']);
    }

    // — cambiarPassword ————————————————————————————————————————

    #[Test]
    public function test_cambiar_password_hashes_new_password(): void
    {
        $user = User::factory()->create();

        $this->service->cambiarPassword($user, 'NuevaPass@123');

        $user->refresh();
        $this->assertNotEquals('NuevaPass@123', $user->password);
        $this->assertTrue(Hash::check('NuevaPass@123', $user->password));
    }

    #[Test]
    public function test_cambiar_password_replaces_old_hash(): void
    {
        $user = User::factory()->create();
        $originalPassword = $user->password;

        $this->service->cambiarPassword($user, 'NuevaPass@123');

        $user->refresh();
        $this->assertNotEquals($originalPassword, $user->password);
        $this->assertFalse(Hash::check('password', $user->password));
    }

    #[Test]
    public function test_cambiar_password_does_not_change_name_or_email(): void
    {
        $user = User::factory()->create(['name' => 'Nombre Fijo', 'email' => 'fijo@test.com']);

        $this->service->cambiarPassword($user, 'NuevaPass@123');

        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'name'  => 'Nombre Fijo',
            'email' => 'fijo@test.com',
        ]);
    }
}
