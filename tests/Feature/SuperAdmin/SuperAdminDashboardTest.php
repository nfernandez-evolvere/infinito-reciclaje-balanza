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
    //
    // assertViewHas en lugar de assertSee: verifica los datos exactos que el servicio
    // pasa a la vista, sin depender del HTML renderizado (donde un '5' puede matchear
    // cualquier número, año, ID o clase CSS que contenga ese dígito).
    //
    // setUp de TestCase crea siempre 'Organización Test' (activo: true). Todos los
    // conteos incluyen esa org base; los tests lo expresan explícitamente.

    #[Test]
    public function stats_cuentan_total_activas_e_inactivas_correctamente(): void
    {
        // setUp ya tiene 1 org activa. Creamos 2 activas + 1 inactiva → total 4.
        Organizacion::factory()->count(2)->create(['activo' => true]);
        Organizacion::factory()->count(1)->create(['activo' => false]);

        $this->actingAs($this->superAdmin())
            ->get(route('super.dashboard'))
            ->assertOk()
            ->assertViewHas('stats', function (array $stats): bool {
                return $stats['total'] === 4     // 1 setUp + 2 activas + 1 inactiva
                    && $stats['activas'] === 3   // 1 setUp + 2 activas
                    && $stats['inactivas'] === 1;
            });
    }

    #[Test]
    public function stats_cuentan_usuarios_no_super_admin(): void
    {
        // No hay usuarios no-super en setUp. Creamos 2 admins + 1 operador.
        User::factory()->count(2)->create(['role' => 'admin']);
        User::factory()->count(1)->create(['role' => 'operador']);
        $superAdmin = $this->superAdmin(); // este NO se cuenta (role = super_admin)

        $this->actingAs($superAdmin)
            ->get(route('super.dashboard'))
            ->assertOk()
            ->assertViewHas('stats', fn (array $stats) => $stats['usuarios'] === 3);
    }

    #[Test]
    public function recientes_incluye_las_organizaciones_mas_nuevas(): void
    {
        Organizacion::factory()->create(['nombre' => 'Org Antigua']);
        Organizacion::factory()->create(['nombre' => 'Org Reciente']);

        $this->actingAs($this->superAdmin())
            ->get(route('super.dashboard'))
            ->assertOk()
            ->assertViewHas('recientes', function ($recientes): bool {
                return $recientes->contains('nombre', 'Org Reciente');
            });
    }
}
