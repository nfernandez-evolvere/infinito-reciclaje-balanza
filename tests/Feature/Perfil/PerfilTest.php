<?php

namespace Tests\Feature\Perfil;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PerfilTest extends TestCase
{
    use RefreshDatabase;

    // — Acceso ——————————————————————————————————————————————————

    #[Test]
    public function test_guest_redirected_to_login_on_show(): void
    {
        $this->get(route('perfil.show'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function test_guest_cannot_update_profile(): void
    {
        $this->put(route('perfil.update'), ['name' => 'Hacker'])
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function test_guest_cannot_update_password(): void
    {
        $this->put(route('perfil.password'), [
            'current_password'      => 'password',
            'password'              => 'NuevaPass@123',
            'password_confirmation' => 'NuevaPass@123',
        ])->assertRedirect(route('login'));
    }

    #[Test]
    public function test_authenticated_user_can_view_profile(): void
    {
        $this->actingAs($this->operador(['name' => 'Roberto García', 'email' => 'roberto@test.com']))
            ->get(route('perfil.show'))
            ->assertOk()
            ->assertSee('Mi perfil')
            ->assertSee('Roberto García')
            ->assertSee('roberto@test.com');
    }

    // — Actualizar nombre ——————————————————————————————————————

    #[Test]
    public function test_user_can_update_own_name(): void
    {
        $user = $this->operador(['name' => 'Nombre Viejo']);

        $this->actingAs($user)
            ->put(route('perfil.update'), ['name' => 'Nombre Nuevo'])
            ->assertRedirect(route('perfil.show'))
            ->assertSessionHas('toast.variant', 'success')
            ->assertSessionHas('toast.message', 'Datos actualizados.');

        $this->assertDatabaseHas('users', [
            'id'   => $user->id,
            'name' => 'Nombre Nuevo',
        ]);
    }

    #[Test]
    public function test_update_name_does_not_change_email_or_password(): void
    {
        $user = $this->operador(['name' => 'Nombre Viejo', 'email' => 'fijo@test.com']);
        $originalPassword = $user->password;

        $this->actingAs($user)
            ->put(route('perfil.update'), ['name' => 'Nombre Nuevo']);

        $this->assertDatabaseHas('users', [
            'id'       => $user->id,
            'email'    => 'fijo@test.com',
            'password' => $originalPassword,
        ]);
    }

    #[Test]
    public function test_update_name_validates_required(): void
    {
        $user = $this->operador(['name' => 'Nombre Viejo']);

        $this->actingAs($user)
            ->put(route('perfil.update'), ['name' => ''])
            ->assertSessionHasErrors('name', errorBag: 'updateProfile');

        // El nombre original no se modifica al fallar la validación.
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Nombre Viejo']);
    }

    #[Test]
    public function test_update_name_validates_max_255(): void
    {
        $user = $this->operador();

        $this->actingAs($user)
            ->put(route('perfil.update'), ['name' => str_repeat('a', 256)])
            ->assertSessionHasErrors('name', errorBag: 'updateProfile');
    }

    #[Test]
    public function test_update_name_accepts_boundary_255(): void
    {
        $user = $this->operador();
        $name = str_repeat('a', 255);

        $this->actingAs($user)
            ->put(route('perfil.update'), ['name' => $name])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('perfil.show'));

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => $name]);
    }

    // — Cambiar contraseña ——————————————————————————————————————

    #[Test]
    public function test_user_can_change_own_password(): void
    {
        $user = $this->operador();

        $this->actingAs($user)
            ->put(route('perfil.password'), [
                'current_password'      => 'password',
                'password'              => 'NuevaPass@123',
                'password_confirmation' => 'NuevaPass@123',
            ])
            ->assertRedirect(route('perfil.show'))
            ->assertSessionHas('toast.variant', 'success')
            ->assertSessionHas('toast.message', 'Contraseña actualizada.');

        $user->refresh();
        $this->assertTrue(Hash::check('NuevaPass@123', $user->password));
    }

    #[Test]
    public function test_change_password_invalidates_old_password(): void
    {
        $user = $this->operador();

        $this->actingAs($user)
            ->put(route('perfil.password'), [
                'current_password'      => 'password',
                'password'              => 'NuevaPass@123',
                'password_confirmation' => 'NuevaPass@123',
            ]);

        $user->refresh();
        $this->assertFalse(Hash::check('password', $user->password));
    }

    #[Test]
    public function test_change_password_stores_hash_not_plain_text(): void
    {
        $user = $this->operador();

        $this->actingAs($user)
            ->put(route('perfil.password'), [
                'current_password'      => 'password',
                'password'              => 'NuevaPass@123',
                'password_confirmation' => 'NuevaPass@123',
            ]);

        $user->refresh();
        $this->assertNotEquals('NuevaPass@123', $user->password);
    }

    #[Test]
    public function test_change_password_requires_correct_current_password(): void
    {
        $user = $this->operador();

        $this->actingAs($user)
            ->put(route('perfil.password'), [
                'current_password'      => 'incorrecta',
                'password'              => 'NuevaPass@123',
                'password_confirmation' => 'NuevaPass@123',
            ])
            ->assertSessionHasErrors('current_password', errorBag: 'updatePassword');

        // La contraseña no cambió: la anterior sigue siendo válida.
        $user->refresh();
        $this->assertTrue(Hash::check('password', $user->password));
    }

    #[Test]
    public function test_change_password_validates_current_password_required(): void
    {
        $this->actingAs($this->operador())
            ->put(route('perfil.password'), [
                'current_password'      => '',
                'password'              => 'NuevaPass@123',
                'password_confirmation' => 'NuevaPass@123',
            ])
            ->assertSessionHasErrors('current_password', errorBag: 'updatePassword');
    }

    #[Test]
    public function test_change_password_validates_new_password_required(): void
    {
        $this->actingAs($this->operador())
            ->put(route('perfil.password'), [
                'current_password'      => 'password',
                'password'              => '',
                'password_confirmation' => '',
            ])
            ->assertSessionHasErrors('password', errorBag: 'updatePassword');
    }

    #[Test]
    public function test_change_password_rejects_below_min_8_chars(): void
    {
        // 7 caracteres con toda la complejidad → falla solo por el largo mínimo.
        $this->actingAs($this->operador())
            ->put(route('perfil.password'), [
                'current_password'      => 'password',
                'password'              => 'Aa1@bcd',
                'password_confirmation' => 'Aa1@bcd',
            ])
            ->assertSessionHasErrors('password', errorBag: 'updatePassword');
    }

    #[Test]
    public function test_change_password_requires_mixed_case(): void
    {
        $this->actingAs($this->operador())
            ->put(route('perfil.password'), [
                'current_password'      => 'password',
                'password'              => 'todominuscula1@',
                'password_confirmation' => 'todominuscula1@',
            ])
            ->assertSessionHasErrors('password', errorBag: 'updatePassword');
    }

    #[Test]
    public function test_change_password_requires_a_number(): void
    {
        $this->actingAs($this->operador())
            ->put(route('perfil.password'), [
                'current_password'      => 'password',
                'password'              => 'SinNumero@abc',
                'password_confirmation' => 'SinNumero@abc',
            ])
            ->assertSessionHasErrors('password', errorBag: 'updatePassword');
    }

    #[Test]
    public function test_change_password_requires_a_symbol(): void
    {
        $this->actingAs($this->operador())
            ->put(route('perfil.password'), [
                'current_password'      => 'password',
                'password'              => 'SinSimbolo123',
                'password_confirmation' => 'SinSimbolo123',
            ])
            ->assertSessionHasErrors('password', errorBag: 'updatePassword');
    }

    #[Test]
    public function test_change_password_validates_confirmation_must_match(): void
    {
        $this->actingAs($this->operador())
            ->put(route('perfil.password'), [
                'current_password'      => 'password',
                'password'              => 'NuevaPass@123',
                'password_confirmation' => 'Diferente@456',
            ])
            ->assertSessionHasErrors('password', errorBag: 'updatePassword');
    }

    #[Test]
    public function test_change_password_does_not_change_name_or_email(): void
    {
        $user = $this->operador(['name' => 'Nombre Fijo', 'email' => 'fijo@test.com']);

        $this->actingAs($user)
            ->put(route('perfil.password'), [
                'current_password'      => 'password',
                'password'              => 'NuevaPass@123',
                'password_confirmation' => 'NuevaPass@123',
            ]);

        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'name'  => 'Nombre Fijo',
            'email' => 'fijo@test.com',
        ]);
    }

    #[Test]
    public function test_admin_can_also_manage_own_profile(): void
    {
        // El perfil es accesible para cualquier rol autenticado, no solo operadores.
        $admin = $this->admin(['name' => 'Admin Viejo']);

        $this->actingAs($admin)
            ->put(route('perfil.update'), ['name' => 'Admin Nuevo'])
            ->assertRedirect(route('perfil.show'));

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'name' => 'Admin Nuevo']);
    }
}
