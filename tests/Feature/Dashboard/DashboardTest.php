<?php

namespace Tests\Feature\Dashboard;

use App\Models\Pesaje;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use App\Models\Zona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    // ── Protección de rutas ───────────────────────────────────────────

    #[Test]
    public function admin_puede_acceder_al_dashboard(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    #[Test]
    public function operador_no_puede_acceder_al_dashboard(): void
    {
        $this->actingAs($this->operador())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    #[Test]
    public function invitado_es_redirigido_al_login(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    // ── Endpoint /admin/dashboard/data ────────────────────────────────

    #[Test]
    public function data_endpoint_devuelve_json_con_estructura_correcta(): void
    {
        $this->actingAs($this->admin())
            ->getJson(route('admin.dashboard.data'))
            ->assertOk()
            ->assertJsonStructure([
                'kpisDia' => ['total', 'toneladas', 'promedio', 'ultimo_hace_min',
                    'delta', 'delta_base', 'delta_toneladas'],
                'kpisMes'     => ['total', 'toneladas', 'dias_op', 'delta', 'delta_base'],
                'evolucion7'  => ['datos', 'promedio'],
                'evolucion15' => ['datos', 'promedio'],
                'evolucion90' => ['datos', 'promedio'],
                'desgloseVehiculo',
                'desgloseZona',
                'desgloseVehiculoMes',
                'desgloseZonaMes',
                'alertas',
            ]);
    }

    #[Test]
    public function data_con_rango_valido_incluye_claves_de_rango(): void
    {
        $desde = today()->subDays(7)->format('Y-m-d');
        $hasta = today()->format('Y-m-d');

        $this->actingAs($this->admin())
            ->getJson(route('admin.dashboard.data', compact('desde', 'hasta')))
            ->assertOk()
            ->assertJsonStructure([
                'kpisRango'      => ['total', 'toneladas', 'dias_rango', 'dias_op'],
                'evolucionRango' => ['datos', 'promedio'],
                'desgloseVehiculoRango',
                'desgloseZonaRango',
            ]);
    }

    #[Test]
    public function data_con_fechas_invalidas_no_incluye_claves_de_rango(): void
    {
        $this->actingAs($this->admin())
            ->getJson(route('admin.dashboard.data', ['desde' => 'not-a-date', 'hasta' => 'also-not']))
            ->assertOk()
            ->assertJsonMissingPath('kpisRango');
    }

    #[Test]
    public function data_con_rango_invertido_no_incluye_claves_de_rango(): void
    {
        // hasta < desde → se ignora
        $this->actingAs($this->admin())
            ->getJson(route('admin.dashboard.data', [
                'desde' => today()->format('Y-m-d'),
                'hasta' => today()->subDays(7)->format('Y-m-d'),
            ]))
            ->assertOk()
            ->assertJsonMissingPath('kpisRango');
    }

    #[Test]
    public function data_kpis_reflejan_pesajes_creados_hoy(): void
    {
        Pesaje::factory()->count(3)->create(['created_at' => today(), 'peso_neto_kg' => 2000]);
        Pesaje::factory()->cancelado()->create(['created_at' => today(), 'peso_neto_kg' => 9000]);

        $json = $this->actingAs($this->admin())
            ->getJson(route('admin.dashboard.data'))
            ->assertOk()
            ->json();

        $this->assertEquals(3, $json['kpisDia']['total']);
        $this->assertEquals(6.0, $json['kpisDia']['toneladas']);
    }

    #[Test]
    public function operador_no_puede_acceder_al_data_endpoint(): void
    {
        $this->actingAs($this->operador())
            ->getJson(route('admin.dashboard.data'))
            ->assertForbidden();
    }

    // ── Cálculos con datos reales ─────────────────────────────────────

    #[Test]
    public function data_kpisMes_calcula_delta_contra_mes_anterior(): void
    {
        // Mes actual: 4 pesajes × 2000 kg = 8000 kg
        Pesaje::factory()->count(4)->create(['created_at' => today(), 'peso_neto_kg' => 2000]);
        // Mes anterior mismo período: 2 pesajes × 2000 kg = 4000 kg → delta total = +100%
        Pesaje::factory()->count(2)->create(['created_at' => today()->subMonth(), 'peso_neto_kg' => 2000]);

        $json = $this->actingAs($this->admin())
            ->getJson(route('admin.dashboard.data'))
            ->assertOk()
            ->json();

        $this->assertEquals(4, $json['kpisMes']['total']);
        $this->assertEquals(2, $json['kpisMes']['delta_base']);
        $this->assertEquals(100.0, $json['kpisMes']['delta']);
        $this->assertEquals(8.0, $json['kpisMes']['toneladas']);
        $this->assertEquals(100.0, $json['kpisMes']['delta_toneladas']);
    }

    #[Test]
    public function data_kpisDia_calcula_kg_por_ha_y_por_persona(): void
    {
        $zona = Zona::factory()->create(['hectareas' => 500.0, 'habitantes' => 10000]);

        // 1 pesaje hoy: 5000 kg
        // kg_por_ha     = round(5000 / 500,   1) = 10.0
        // kg_por_persona = round(5000 / 10000, 2) =  0.5
        Pesaje::factory()->create(['created_at' => today(), 'zona_id' => $zona->id, 'peso_neto_kg' => 5000]);

        $json = $this->actingAs($this->admin())
            ->getJson(route('admin.dashboard.data'))
            ->assertOk()
            ->json();

        $this->assertEquals(10.0, $json['kpisDia']['kg_por_ha']);
        $this->assertEquals(0.5, $json['kpisDia']['kg_por_persona']);
    }

    #[Test]
    public function data_desgloseZona_agrupa_misma_zona_por_turno(): void
    {
        $zona = Zona::factory()->create();

        Pesaje::factory()->create(['created_at' => today(), 'zona_id' => $zona->id, 'turno' => 'Mañana', 'peso_neto_kg' => 3000]);
        Pesaje::factory()->create(['created_at' => today(), 'zona_id' => $zona->id, 'turno' => 'Tarde',  'peso_neto_kg' => 2000]);

        $json = $this->actingAs($this->admin())
            ->getJson(route('admin.dashboard.data'))
            ->assertOk()
            ->json();

        // La misma zona con 2 turnos distintos debe producir 2 entradas
        $entradas = collect($json['desgloseZona'])->where('zona_id', $zona->id);
        $this->assertCount(2, $entradas);

        $turnos = $entradas->pluck('turno')->sort()->values()->all();
        $this->assertEquals(['Mañana', 'Tarde'], $turnos);
    }

    #[Test]
    public function data_desgloseZona_calcula_kg_por_ha_y_kg_por_hab(): void
    {
        $zona = Zona::factory()->create(['hectareas' => 100.0, 'habitantes' => 5000]);

        // 2 pesajes × 5000 kg, mismo turno → mismo grupo → 10 000 kg para esta zona
        // kg_por_ha  = round(10000 / 100,  1) = 100.0
        // kg_por_hab = round(10000 / 5000, 2) =   2.0
        Pesaje::factory()->count(2)->create(['created_at' => today(), 'zona_id' => $zona->id, 'peso_neto_kg' => 5000, 'turno' => 'Mañana']);

        $json = $this->actingAs($this->admin())
            ->getJson(route('admin.dashboard.data'))
            ->assertOk()
            ->json();

        $entrada = collect($json['desgloseZona'])->firstWhere('zona_id', $zona->id);

        $this->assertNotNull($entrada);
        $this->assertEquals(100.0, $entrada['kg_por_ha']);
        $this->assertEquals(2.0, $entrada['kg_por_hab']);
    }

    #[Test]
    public function data_evolucionRango_tiene_un_dato_por_dia_del_rango(): void
    {
        $desde = today()->subDays(13)->format('Y-m-d');
        $hasta = today()->format('Y-m-d');

        $json = $this->actingAs($this->admin())
            ->getJson(route('admin.dashboard.data', compact('desde', 'hasta')))
            ->assertOk()
            ->json();

        $this->assertCount(14, $json['evolucionRango']['datos']);
    }

    #[Test]
    public function data_evolucionRango_registra_toneladas_en_el_dia_correcto(): void
    {
        $desde = today()->subDays(6)->format('Y-m-d');
        $hasta = today()->format('Y-m-d');

        Pesaje::factory()->create(['created_at' => today()->subDays(2), 'peso_neto_kg' => 6000]);

        $json = $this->actingAs($this->admin())
            ->getJson(route('admin.dashboard.data', compact('desde', 'hasta')))
            ->assertOk()
            ->json();

        $datos = collect($json['evolucionRango']['datos']);

        $diaConPesaje = $datos->firstWhere('toneladas', 6.0);
        $this->assertNotNull($diaConPesaje);

        // El resto de los días deben estar en 0
        $this->assertEquals(6, $datos->where('toneladas', 0)->count());
    }

    #[Test]
    public function data_desglose_viene_ordenado_descendente_por_toneladas(): void
    {
        $tipo = TipoVehiculo::factory()->create();
        $v1 = Vehiculo::factory()->create(['tipo_vehiculo_id' => $tipo->id]);
        $v2 = Vehiculo::factory()->create(['tipo_vehiculo_id' => $tipo->id]);

        $zona1 = Zona::factory()->create();
        $zona2 = Zona::factory()->create();

        Pesaje::factory()->create(['created_at' => today(), 'vehiculo_id' => $v1->id, 'zona_id' => $zona1->id, 'peso_neto_kg' => 8000]);
        Pesaje::factory()->create(['created_at' => today(), 'vehiculo_id' => $v2->id, 'zona_id' => $zona2->id, 'peso_neto_kg' => 2000]);

        $json = $this->actingAs($this->admin())
            ->getJson(route('admin.dashboard.data'))
            ->assertOk()
            ->json();

        // desgloseZona: primer elemento debe tener más toneladas que el segundo
        $toneladasZona = collect($json['desgloseZona'])->pluck('toneladas');
        $this->assertTrue($toneladasZona->first() >= $toneladasZona->skip(1)->first());

        // desgloseVehiculo: ídem
        $toneladasVeh = collect($json['desgloseVehiculo'])->pluck('toneladas');
        $this->assertTrue($toneladasVeh->first() >= $toneladasVeh->skip(1)->first());
    }
}
