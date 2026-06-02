<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LayoutTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_operador_layout_renders_for_operador(): void
    {
        $user = User::factory()->create(['role' => 'operador']);

        $response = $this->actingAs($user)->get('/balanza');

        $response->assertStatus(200)
            ->assertSee('Pesaje')
            ->assertSee('Historial');
    }

    #[Test]
    public function test_admin_layout_renders_for_admin(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(200)
            ->assertSee('Dashboard')
            ->assertSee('Pesajes');
    }
}
