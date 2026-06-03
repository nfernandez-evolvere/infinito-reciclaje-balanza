<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Organizacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SuperAdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    // ── Acceso ────────────────────────────────────────────────────────

    #[Test]
    public function super_admin_accede_al_dashboard(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('super.dashboard'))
            ->assertOk();
    }

    #[Test]
    public function admin_no_accede_al_dashboard(): void
    {
        $this->actingAs($this->admin())
            ->get(route('super.dashboard'))
            ->assertForbidden();
    }

    #[Test]
    public function guest_es_redirigido_al_login_desde_dashboard(): void
    {
        $this->get(route('super.dashboard'))
            ->assertRedirect(route('login'));
    }

    // ── Stats cross-organizacion ──────────────────────────────────────

    #[Test]
    public function dashboard_muestra_total_de_organizaciones(): void
    {
        Organizacion::factory()->count(3)->create(['activo' => true]);
        Organizacion::factory()->count(2)->create(['activo' => false]);

        $this->actingAs($this->superAdmin())
            ->get(route('super.dashboard'))
            ->assertOk()
            ->assertSee('5');   // total orgs
    }

    #[Test]
    public function dashboard_cuenta_orgs_activas_e_inactivas_por_separado(): void
    {
        Organizacion::factory()->count(4)->create(['activo' => true]);
        Organizacion::factory()->count(1)->create(['activo' => false]);

        $response = $this->actingAs($this->superAdmin())
            ->get(route('super.dashboard'))
            ->assertOk();

        // Ambos valores deben aparecer en la vista.
        $response->assertSee('4');
        $response->assertSee('1');
    }

    #[Test]
    public function dashboard_muestra_total_de_usuarios_no_super_admin(): void
    {
        // superAdmin() del trait ya existe; creamos otros 3 no-super.
        User::factory()->count(2)->create(['role' => 'admin']);
        User::factory()->count(1)->create(['role' => 'operador']);

        $superAdmin = $this->superAdmin();

        $this->actingAs($superAdmin)
            ->get(route('super.dashboard'))
            ->assertOk()
            ->assertSee('3');   // solo los no-super
    }

    #[Test]
    public function dashboard_lista_las_organizaciones_mas_recientes(): void
    {
        Organizacion::factory()->create(['nombre' => 'Org Antigua']);
        Organizacion::factory()->create(['nombre' => 'Org Reciente']);

        $this->actingAs($this->superAdmin())
            ->get(route('super.dashboard'))
            ->assertOk()
            ->assertSee('Org Reciente');
    }
}
