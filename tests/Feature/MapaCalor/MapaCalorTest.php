<?php

namespace Tests\Feature\MapaCalor;

use App\Models\Pesaje;
use App\Models\Zona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * El mapa de calor dejó de ser una sección standalone: ahora se embebe como panel
 * en el Dashboard (por período) y en Reportes → Generar. Estos tests verifican que
 * el panel y sus datos llegan a cada pantalla, y que la ruta vieja ya no existe.
 */
class MapaCalorTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function dashboard_embebe_el_panel_de_mapa_con_zonas_con_geometria(): void
    {
        $zona = Zona::factory()->conGeometria()->create(['nombre' => 'Zona Mapa Dashboard']);
        Pesaje::factory()->create(['created_at' => today(), 'zona_id' => $zona->id, 'peso_neto_kg' => 5000]);

        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Mapa de calor')          // título del panel embebido
            ->assertSee('Per cápita')             // selector de las 4 métricas
            ->assertSee('Zona Mapa Dashboard');   // dato de la zona en el payload del mapa
    }

    #[Test]
    public function reportes_generar_embebe_el_panel_de_mapa_con_zonas_con_geometria(): void
    {
        $zona = Zona::factory()->conGeometria()->create(['nombre' => 'Zona Mapa Reporte']);
        Pesaje::factory()->create(['created_at' => today(), 'zona_id' => $zona->id, 'peso_neto_kg' => 8000]);

        $this->actingAs($this->admin())
            ->get(route('admin.reportes.index', [
                'tab'   => 'generar',
                'desde' => today()->subDays(7)->format('Y-m-d'),
                'hasta' => today()->format('Y-m-d'),
            ]))
            ->assertOk()
            ->assertSee('Mapa de calor')
            ->assertSee('Zona Mapa Reporte');
    }

    #[Test]
    public function reportes_generar_sin_periodo_no_renderiza_el_panel_de_mapa(): void
    {
        Zona::factory()->conGeometria()->create(['nombre' => 'Zona Sin Periodo']);

        $this->actingAs($this->admin())
            ->get(route('admin.reportes.index', ['tab' => 'generar']))
            ->assertOk()
            ->assertDontSee('Mapa de calor');
    }

    #[Test]
    public function la_ruta_standalone_de_mapa_de_calor_fue_eliminada(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/mapa-calor')
            ->assertNotFound();
    }
}
