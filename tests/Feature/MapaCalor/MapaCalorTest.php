<?php

namespace Tests\Feature\MapaCalor;

use App\Models\Zona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MapaCalorTest extends TestCase
{
    use RefreshDatabase;

    // ── Acceso ────────────────────────────────────────────────────────

    #[Test]
    public function solo_admin_accede_al_mapa(): void
    {
        $this->actingAs($this->operador())
            ->get(route('admin.mapa-calor.index'))
            ->assertForbidden();

        $this->actingAs($this->admin())
            ->get(route('admin.mapa-calor.index'))
            ->assertOk();
    }

    #[Test]
    public function guest_es_redirigido_al_login(): void
    {
        $this->get(route('admin.mapa-calor.index'))->assertRedirect(route('login'));
    }

    // ── Render ────────────────────────────────────────────────────────

    #[Test]
    public function muestra_empty_state_cuando_ninguna_zona_tiene_geometria(): void
    {
        Zona::factory()->create(['nombre' => 'Zona Sin Mapa', 'geojson' => null]);

        $this->actingAs($this->admin())
            ->get(route('admin.mapa-calor.index'))
            ->assertOk()
            ->assertSee('Todavía no hay zonas en el mapa');
    }

    #[Test]
    public function renderiza_el_mapa_cuando_hay_una_zona_con_geometria(): void
    {
        Zona::factory()->conGeometria()->create(['nombre' => 'Zona Mapeada']);

        $this->actingAs($this->admin())
            ->get(route('admin.mapa-calor.index'))
            ->assertOk()
            ->assertSee('Mapa de calor')
            ->assertSee('Zona Mapeada')
            ->assertDontSee('Todavía no hay zonas en el mapa');
    }

    #[Test]
    public function filtra_por_rango_de_fechas_de_la_query_string(): void
    {
        Zona::factory()->conGeometria()->create(['nombre' => 'Zona Rango']);

        $this->actingAs($this->admin())
            ->get(route('admin.mapa-calor.index', [
                'desde' => today()->subDays(7)->format('Y-m-d'),
                'hasta' => today()->format('Y-m-d'),
            ]))
            ->assertOk()
            ->assertSee('Zona Rango');
    }
}
