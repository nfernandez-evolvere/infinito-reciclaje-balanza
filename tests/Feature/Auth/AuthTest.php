<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    #[Test]
    public function test_operador_redirected_to_balanza_after_login(): void
    {
        // El factory ya adjunta el usuario a la organización activa del test.
        $user = User::factory()->create(['role' => 'operador']);

        $this->post('/login', [
            'email'           => $user->email,
            'password'        => 'password',
            'organizacion_id' => app('organizacion')->id,
        ])->assertRedirect('/balanza');
    }

    #[Test]
    public function test_admin_redirected_to_dashboard_after_login(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->post('/login', [
            'email'           => $user->email,
            'password'        => 'password',
            'organizacion_id' => app('organizacion')->id,
        ])->assertRedirect('/admin/dashboard');
    }

    #[Test]
    public function test_invalid_credentials_returns_error(): void
    {
        User::factory()->create(['email' => 'test@test.com']);

        $this->post('/login', ['email' => 'test@test.com', 'password' => 'wrong'])
            ->assertStatus(302)
            ->assertSessionHasErrors('email');
    }

    #[Test]
    public function test_register_route_does_not_exist(): void
    {
        $this->get('/register')->assertStatus(404);
    }

    #[Test]
    public function test_logout_destroys_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    #[Test]
    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->inactive()->create(['email' => 'inactivo@test.com']);

        $this->post('/login', ['email' => 'inactivo@test.com', 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    #[Test]
    public function test_inactive_user_error_message_is_descriptive(): void
    {
        $user = User::factory()->inactive()->create(['email' => 'inactivo@test.com']);

        $response = $this->post('/login', [
            'email'    => 'inactivo@test.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email' => 'Tu cuenta está desactivada. Contactá al administrador.']);
    }

    #[Test]
    public function test_inactive_user_with_correct_password_is_still_blocked(): void
    {
        // Distingue entre "contraseña incorrecta" (mensaje genérico) y "usuario inactivo" (mensaje específico).
        // Con credenciales correctas pero cuenta inactiva, el error debe ser el de inactividad.
        $user = User::factory()->inactive()->create(['email' => 'inactivo@test.com']);

        $this->post('/login', ['email' => 'inactivo@test.com', 'password' => 'password'])
            ->assertSessionHasErrors(['email' => 'Tu cuenta está desactivada. Contactá al administrador.']);
    }
}
