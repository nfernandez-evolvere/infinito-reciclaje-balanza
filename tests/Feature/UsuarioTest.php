<?php

namespace Tests\Feature;

use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UsuarioTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function operador(): User
    {
        return User::factory()->create(['role' => 'operador']);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name'                  => 'Roberto García',
            'email'                 => 'roberto@municipio.gob.ar',
            'role'                  => 'operador',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }

    // — Acceso ——————————————————————————————————————————————————

    #[Test]
    public function test_guest_redirected_to_login(): void
    {
        $this->get(route('admin.usuarios.index'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function test_operador_cannot_access_index(): void
    {
        $this->actingAs($this->operador())
            ->get(route('admin.usuarios.index'))
            ->assertStatus(403);
    }

    #[Test]
    public function test_operador_cannot_store(): void
    {
        $this->actingAs($this->operador())
            ->post(route('admin.usuarios.store'), $this->payload())
            ->assertStatus(403);
    }

    #[Test]
    public function test_operador_cannot_update(): void
    {
        $target = User::factory()->create();

        $this->actingAs($this->operador())
            ->put(route('admin.usuarios.update', $target), $this->payload())
            ->assertStatus(403);
    }

    #[Test]
    public function test_operador_cannot_toggle(): void
    {
        $target = User::factory()->create();

        $this->actingAs($this->operador())
            ->patch(route('admin.usuarios.toggle', $target))
            ->assertStatus(403);
    }

    #[Test]
    public function test_operador_cannot_reset_password(): void
    {
        $target = User::factory()->create();

        $this->actingAs($this->operador())
            ->patch(route('admin.usuarios.reset-password', $target), [
                'password'              => 'nuevaPass123',
                'password_confirmation' => 'nuevaPass123',
            ])
            ->assertStatus(403);
    }

    // — Index ——————————————————————————————————————————————————

    #[Test]
    public function test_index_renders_user_list(): void
    {
        User::factory()->create(['name' => 'Roberto García']);
        User::factory()->create(['name' => 'Ignacio López']);

        $this->actingAs($this->admin())
            ->get(route('admin.usuarios.index'))
            ->assertStatus(200)
            ->assertSee('Roberto García')
            ->assertSee('Ignacio López');
    }

    #[Test]
    public function test_index_shows_role_labels(): void
    {
        User::factory()->create(['name' => 'Operador Test', 'role' => 'operador']);
        User::factory()->admin()->create(['name' => 'Admin Test']);

        $this->actingAs($this->admin())
            ->get(route('admin.usuarios.index'))
            ->assertStatus(200)
            ->assertSee('Operador')
            ->assertSee('Admin');
    }

    #[Test]
    public function test_index_filter_by_name(): void
    {
        User::factory()->create(['name' => 'Roberto García']);
        User::factory()->create(['name' => 'Ignacio López']);

        $this->actingAs($this->admin())
            ->get(route('admin.usuarios.index', ['buscar' => 'Roberto']))
            ->assertStatus(200)
            ->assertSee('Roberto García')
            ->assertDontSee('Ignacio López');
    }

    #[Test]
    public function test_index_filter_by_email(): void
    {
        User::factory()->create(['name' => 'Roberto', 'email' => 'roberto@test.com']);
        User::factory()->create(['name' => 'Ignacio', 'email' => 'nacho@test.com']);

        $this->actingAs($this->admin())
            ->get(route('admin.usuarios.index', ['buscar' => 'roberto@test.com']))
            ->assertStatus(200)
            ->assertSee('Roberto')
            ->assertDontSee('Ignacio');
    }

    #[Test]
    public function test_index_filter_by_role(): void
    {
        User::factory()->create(['name' => 'El Operador', 'role' => 'operador']);
        User::factory()->admin()->create(['name' => 'El Admin']);

        $this->actingAs($this->admin())
            ->get(route('admin.usuarios.index', ['role' => 'operador']))
            ->assertStatus(200)
            ->assertSee('El Operador')
            ->assertDontSee('El Admin');
    }

    #[Test]
    public function test_index_filter_by_activo(): void
    {
        User::factory()->create(['name' => 'Usuario Activo', 'activo' => true]);
        User::factory()->inactive()->create(['name' => 'Usuario Inactivo']);

        $this->actingAs($this->admin())
            ->get(route('admin.usuarios.index', ['activo' => '1']))
            ->assertStatus(200)
            ->assertSee('Usuario Activo')
            ->assertDontSee('Usuario Inactivo');
    }

    // — Store ——————————————————————————————————————————————————

    #[Test]
    public function test_admin_can_create_operador(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.usuarios.store'), $this->payload())
            ->assertRedirect(route('admin.usuarios.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'roberto@municipio.gob.ar',
            'role'  => 'operador',
        ]);
    }

    #[Test]
    public function test_admin_can_create_admin(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.usuarios.store'), $this->payload([
                'email' => 'otro.admin@municipio.gob.ar',
                'role'  => 'admin',
            ]))
            ->assertRedirect(route('admin.usuarios.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'otro.admin@municipio.gob.ar',
            'role'  => 'admin',
        ]);
    }

    #[Test]
    public function test_store_new_user_is_active(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.usuarios.store'), $this->payload());

        $this->assertDatabaseHas('users', [
            'email'  => 'roberto@municipio.gob.ar',
            'activo' => true,
        ]);
    }

    #[Test]
    public function test_store_password_is_hashed_not_stored_plain(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.usuarios.store'), $this->payload());

        $usuario = User::where('email', 'roberto@municipio.gob.ar')->first();

        $this->assertNotNull($usuario);
        $this->assertNotEquals('password123', $usuario->password);
        $this->assertTrue(Hash::check('password123', $usuario->password));
    }

    #[Test]
    public function test_store_sends_welcome_email_to_new_user(): void
    {
        Mail::fake();

        $this->actingAs($this->admin())
            ->post(route('admin.usuarios.store'), $this->payload());

        Mail::assertSent(WelcomeMail::class, function (WelcomeMail $mail) {
            return $mail->hasTo('roberto@municipio.gob.ar');
        });
    }

    #[Test]
    public function test_store_welcome_email_includes_plain_password(): void
    {
        Mail::fake();

        $this->actingAs($this->admin())
            ->post(route('admin.usuarios.store'), $this->payload([
                'password'              => 'miPassword123',
                'password_confirmation' => 'miPassword123',
            ]));

        Mail::assertSent(WelcomeMail::class, function (WelcomeMail $mail) {
            return $mail->temporaryPassword === 'miPassword123';
        });
    }

    #[Test]
    public function test_store_sends_exactly_one_email(): void
    {
        Mail::fake();

        $this->actingAs($this->admin())
            ->post(route('admin.usuarios.store'), $this->payload());

        Mail::assertSentCount(1);
    }

    #[Test]
    public function test_store_does_not_send_email_on_validation_failure(): void
    {
        Mail::fake();

        $this->actingAs($this->admin())
            ->post(route('admin.usuarios.store'), $this->payload(['email' => '']));

        Mail::assertNothingSent();
    }

    #[Test]
    public function test_store_password_confirmation_not_persisted_as_attribute(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.usuarios.store'), $this->payload());

        $usuario = User::where('email', 'roberto@municipio.gob.ar')->first();

        $this->assertNotNull($usuario);
        $this->assertArrayNotHasKey('password_confirmation', $usuario->getAttributes());
    }

    // — Store validaciones ————————————————————————————————————

    #[Test]
    public function test_store_validates_name_required(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.usuarios.store'), $this->payload(['name' => '']))
            ->assertSessionHasErrors('name');
    }

    #[Test]
    public function test_store_validates_email_required(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.usuarios.store'), $this->payload(['email' => '']))
            ->assertSessionHasErrors('email');
    }

    #[Test]
    public function test_store_validates_email_format(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.usuarios.store'), $this->payload(['email' => 'no-es-un-email']))
            ->assertSessionHasErrors('email');
    }

    #[Test]
    public function test_store_validates_email_unique_among_active_users(): void
    {
        User::factory()->create(['email' => 'duplicado@test.com', 'activo' => true]);

        $this->actingAs($this->admin())
            ->post(route('admin.usuarios.store'), $this->payload(['email' => 'duplicado@test.com']))
            ->assertSessionHasErrors('email');
    }

    #[Test]
    public function test_store_validates_email_unique_even_for_inactive_users(): void
    {
        // Un email de usuario inactivo no puede reutilizarse — el historial debe mantenerse íntegro
        User::factory()->inactive()->create(['email' => 'inactivo@test.com']);

        $this->actingAs($this->admin())
            ->post(route('admin.usuarios.store'), $this->payload(['email' => 'inactivo@test.com']))
            ->assertSessionHasErrors('email');
    }

    #[Test]
    public function test_store_validates_role_required(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.usuarios.store'), $this->payload(['role' => '']))
            ->assertSessionHasErrors('role');
    }

    #[Test]
    public function test_store_validates_role_only_valid_values(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.usuarios.store'), $this->payload(['role' => 'superadmin']))
            ->assertSessionHasErrors('role');
    }

    #[Test]
    public function test_store_validates_password_required(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.usuarios.store'), $this->payload([
                'password'              => '',
                'password_confirmation' => '',
            ]))
            ->assertSessionHasErrors('password');
    }

    #[Test]
    public function test_store_validates_password_min_8_chars(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.usuarios.store'), $this->payload([
                'password'              => 'corta',
                'password_confirmation' => 'corta',
            ]))
            ->assertSessionHasErrors('password');
    }

    #[Test]
    public function test_store_validates_password_confirmation_must_match(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.usuarios.store'), $this->payload([
                'password'              => 'password123',
                'password_confirmation' => 'diferente456',
            ]))
            ->assertSessionHasErrors('password');
    }

    // — Update ——————————————————————————————————————————————————

    #[Test]
    public function test_admin_can_update_name_and_role(): void
    {
        $usuario = User::factory()->create(['name' => 'Nombre Viejo', 'role' => 'operador']);

        $this->actingAs($this->admin())
            ->put(route('admin.usuarios.update', $usuario), [
                'name'  => 'Nombre Nuevo',
                'email' => $usuario->email,
                'role'  => 'admin',
            ])
            ->assertRedirect(route('admin.usuarios.index'));

        $this->assertDatabaseHas('users', [
            'id'   => $usuario->id,
            'name' => 'Nombre Nuevo',
            'role' => 'admin',
        ]);
    }

    #[Test]
    public function test_update_allows_same_email_on_same_record(): void
    {
        $usuario = User::factory()->create(['email' => 'mismo@email.com']);

        $this->actingAs($this->admin())
            ->put(route('admin.usuarios.update', $usuario), [
                'name'  => 'Nombre Actualizado',
                'email' => 'mismo@email.com',
                'role'  => $usuario->role,
            ])
            ->assertRedirect(route('admin.usuarios.index'))
            ->assertSessionHasNoErrors();
    }

    #[Test]
    public function test_update_rejects_email_already_used_by_another_user(): void
    {
        User::factory()->create(['email' => 'ocupado@email.com']);
        $usuario = User::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('admin.usuarios.update', $usuario), [
                'name'  => $usuario->name,
                'email' => 'ocupado@email.com',
                'role'  => $usuario->role,
            ])
            ->assertSessionHasErrors('email');
    }

    #[Test]
    public function test_update_does_not_change_password(): void
    {
        $usuario          = User::factory()->create();
        $originalPassword = $usuario->password;

        $this->actingAs($this->admin())
            ->put(route('admin.usuarios.update', $usuario), [
                'name'  => 'Nombre Modificado',
                'email' => $usuario->email,
                'role'  => $usuario->role,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id'       => $usuario->id,
            'password' => $originalPassword,
        ]);
    }

    #[Test]
    public function test_admin_cannot_change_own_role_to_operador(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put(route('admin.usuarios.update', $admin), [
                'name'  => $admin->name,
                'email' => $admin->email,
                'role'  => 'operador',
            ])
            ->assertRedirect(route('admin.usuarios.index'));

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);
    }

    #[Test]
    public function test_update_validates_role_must_be_valid(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('admin.usuarios.update', $usuario), [
                'name'  => $usuario->name,
                'email' => $usuario->email,
                'role'  => 'superadmin',
            ])
            ->assertSessionHasErrors('role');
    }

    // — Toggle ——————————————————————————————————————————————————

    #[Test]
    public function test_admin_can_deactivate_active_user(): void
    {
        $usuario = User::factory()->create(['activo' => true]);

        $this->actingAs($this->admin())
            ->patch(route('admin.usuarios.toggle', $usuario))
            ->assertRedirect(route('admin.usuarios.index'));

        $this->assertDatabaseHas('users', ['id' => $usuario->id, 'activo' => false]);
    }

    #[Test]
    public function test_admin_can_activate_inactive_user(): void
    {
        $usuario = User::factory()->inactive()->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.usuarios.toggle', $usuario))
            ->assertRedirect(route('admin.usuarios.index'));

        $this->assertDatabaseHas('users', ['id' => $usuario->id, 'activo' => true]);
    }

    #[Test]
    public function test_admin_cannot_toggle_own_account(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->patch(route('admin.usuarios.toggle', $admin))
            ->assertRedirect(route('admin.usuarios.index'));

        // La cuenta del admin debe quedar intacta
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'activo' => true]);
    }

    #[Test]
    public function test_toggle_does_not_affect_other_users(): void
    {
        $objetivo = User::factory()->create(['activo' => true]);
        $otro     = User::factory()->create(['activo' => true]);

        $this->actingAs($this->admin())
            ->patch(route('admin.usuarios.toggle', $objetivo));

        $this->assertDatabaseHas('users', ['id' => $otro->id, 'activo' => true]);
    }

    // — Reset password ——————————————————————————————————————————

    #[Test]
    public function test_admin_can_reset_password(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.usuarios.reset-password', $usuario), [
                'password'              => 'nuevaPassword123',
                'password_confirmation' => 'nuevaPassword123',
            ])
            ->assertRedirect(route('admin.usuarios.index'));

        $usuario->refresh();
        $this->assertTrue(Hash::check('nuevaPassword123', $usuario->password));
    }

    #[Test]
    public function test_reset_password_invalidates_old_password(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.usuarios.reset-password', $usuario), [
                'password'              => 'nuevaPassword123',
                'password_confirmation' => 'nuevaPassword123',
            ]);

        $usuario->refresh();
        $this->assertFalse(Hash::check('password', $usuario->password));
    }

    #[Test]
    public function test_reset_password_validates_min_8_chars(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.usuarios.reset-password', $usuario), [
                'password'              => 'corta',
                'password_confirmation' => 'corta',
            ])
            ->assertSessionHasErrors('password');
    }

    #[Test]
    public function test_reset_password_validates_confirmation_must_match(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.usuarios.reset-password', $usuario), [
                'password'              => 'password123',
                'password_confirmation' => 'diferente456',
            ])
            ->assertSessionHasErrors('password');
    }

    #[Test]
    public function test_reset_password_does_not_change_other_fields(): void
    {
        $usuario    = User::factory()->create(['name' => 'Nombre Fijo', 'role' => 'operador']);
        $originalId = $usuario->id;

        $this->actingAs($this->admin())
            ->patch(route('admin.usuarios.reset-password', $usuario), [
                'password'              => 'nuevaPassword123',
                'password_confirmation' => 'nuevaPassword123',
            ]);

        $this->assertDatabaseHas('users', [
            'id'   => $originalId,
            'name' => 'Nombre Fijo',
            'role' => 'operador',
        ]);
    }
}
