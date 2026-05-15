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
        $user = User::factory()->create(['role' => 'operador']);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/balanza');
    }

    #[Test]
    public function test_admin_redirected_to_dashboard_after_login(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/admin/dashboard');
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
}
