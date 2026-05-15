<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RouteProtectionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_operador_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create(['role' => 'operador']);

        $this->actingAs($user)->get('/admin/dashboard')->assertStatus(403);
    }

    #[Test]
    public function test_admin_cannot_access_balanza(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)->get('/balanza')->assertStatus(403);
    }

    #[Test]
    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/balanza')->assertRedirect('/login');
    }

    #[Test]
    public function test_operador_can_access_balanza(): void
    {
        $user = User::factory()->create(['role' => 'operador']);

        $this->actingAs($user)->get('/balanza')->assertStatus(200);
    }
}
