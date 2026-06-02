<?php

namespace Tests\Integration;

use App\Models\Pesaje;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use App\Models\Zona;
use App\Repositories\PesajeRepository;
use App\Repositories\TipoVehiculoRepository;
use App\Repositories\ZonaRepository;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    private DashboardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DashboardService(
            new PesajeRepository(),
            new ZonaRepository(),
            new TipoVehiculoRepository()
        );
    }

    // ── kpisDelDia ────────────────────────────────────────────────────

    #[Test]
    public function kpisDelDia_retorna_ceros_cuando_no_hay_pesajes(): void
    {
        $kpis = $this->service->kpisDelDia();

        $this->assertSame(0, $kpis['total']);
        $this->assertEquals(0, $kpis['toneladas']);
        $this->assertEquals(0, $kpis['promedio']);
        $this->assertNull($kpis['ultimo_hace_min']);
        $this->assertNull($kpis['delta']);
    }

    #[Test]
    public function kpisDelDia_excluye_pesajes_cancelados(): void
    {
        Pesaje::factory()->create(['created_at' => today(), 'peso_neto_kg' => 5000]);
        Pesaje::factory()->create(['created_at' => today(), 'peso_neto_kg' => 3000]);
        Pesaje::factory()->cancelado()->create(['created_at' => today(), 'peso_neto_kg' => 9000]);

        $kpis = $this->service->kpisDelDia();

        $this->assertSame(2, $kpis['total']);
        $this->assertSame(8.0, $kpis['toneladas']);
    }

    #[Test]
    public function kpisDelDia_calcula_toneladas_y_promedio_correctamente(): void
    {
        Pesaje::factory()->create(['created_at' => today(), 'peso_neto_kg' => 4000]);
        Pesaje::factory()->create(['created_at' => today(), 'peso_neto_kg' => 6000]);

        $kpis = $this->service->kpisDelDia();

        $this->assertSame(2, $kpis['total']);
        $this->assertSame(10.0, $kpis['toneladas']);
        $this->assertSame(5.0, $kpis['promedio']);
    }

    #[Test]
    public function kpisDelDia_calcula_delta_respecto_al_mismo_dia_del_mes_anterior(): void
    {
        // Hoy: 4 pesajes — mes anterior mismo día: 2 → delta = +100 %
        Pesaje::factory()->count(4)->create(['created_at' => today()]);
        Pesaje::factory()->count(2)->create(['created_at' => today()->subMonth()]);

        $kpis = $this->service->kpisDelDia();

        $this->assertSame(4, $kpis['total']);
        $this->assertSame(2, $kpis['delta_base']);
        $this->assertSame(100.0, $kpis['delta']);
    }

    #[Test]
    public function kpisDelDia_no_cuenta_pesajes_de_otros_dias(): void
    {
        Pesaje::factory()->create(['created_at' => today()->subDay()]);
        Pesaje::factory()->create(['created_at' => today()]);

        $kpis = $this->service->kpisDelDia();

        $this->assertSame(1, $kpis['total']);
    }

    // ── kpisDelMes ────────────────────────────────────────────────────

    #[Test]
    public function kpisDelMes_agrega_solo_el_mes_actual(): void
    {
        $inicioMes = today()->startOfMonth();

        Pesaje::factory()->create(['created_at' => $inicioMes, 'peso_neto_kg' => 2000]);
        Pesaje::factory()->create(['created_at' => today(), 'peso_neto_kg' => 3000]);
        // Día anterior al inicio del mes — debe excluirse
        Pesaje::factory()->create(['created_at' => today()->subMonth()->endOfMonth(), 'peso_neto_kg' => 9000]);

        $kpis = $this->service->kpisDelMes();

        $this->assertSame(2, $kpis['total']);
        $this->assertSame(5.0, $kpis['toneladas']);
    }

    #[Test]
    public function kpisDelMes_cuenta_dias_operativos_distintos(): void
    {
        Pesaje::factory()->create(['created_at' => today()]);
        Pesaje::factory()->create(['created_at' => today()]);
        Pesaje::factory()->create(['created_at' => today()->subDay()]);

        $kpis = $this->service->kpisDelMes();

        $this->assertSame(2, $kpis['dias_op']);
    }

    // ── evolucionDiaria ───────────────────────────────────────────────

    #[Test]
    public function evolucionDiaria_devuelve_exactamente_n_entradas(): void
    {
        $this->assertCount(7, $this->service->evolucionDiaria(7)['datos']);
        $this->assertCount(15, $this->service->evolucionDiaria(15)['datos']);
        $this->assertCount(90, $this->service->evolucionDiaria(90)['datos']);
    }

    #[Test]
    public function evolucionDiaria_rellena_con_cero_dias_sin_pesajes(): void
    {
        Pesaje::factory()->create(['created_at' => today(), 'peso_neto_kg' => 5000]);

        $datos = collect($this->service->evolucionDiaria(7)['datos']);

        // Los 6 días anteriores deben ser 0; el último (hoy) debe ser 5.0
        $this->assertTrue($datos->slice(0, 6)->every(fn ($d) => $d['toneladas'] === 0));
        $this->assertSame(5.0, $datos->last()['toneladas']);
    }

    #[Test]
    public function evolucionDiaria_ejecuta_una_sola_query_para_tres_periodos(): void
    {
        Pesaje::factory()->count(2)->create(['created_at' => today()]);

        DB::enableQueryLog();

        $this->service->evolucionDiaria(7);
        $this->service->evolucionDiaria(15);
        $this->service->evolucionDiaria(90);

        $pesajeQueries = collect(DB::getQueryLog())
            ->filter(fn ($q) => str_contains(strtolower($q['query']), 'pesajes'));

        DB::disableQueryLog();

        $this->assertCount(1, $pesajeQueries);
    }

    // ── desgloseByZona ────────────────────────────────────────────────

    #[Test]
    public function desgloseByZona_incluye_zonas_activas_sin_pesajes(): void
    {
        $zona1 = Zona::factory()->create();
        $zona2 = Zona::factory()->create();

        Pesaje::factory()->create(['created_at' => today(), 'zona_id' => $zona1->id]);

        $desglose = $this->service->desgloseByZona();
        $ids = $desglose->pluck('zona_id');

        $this->assertTrue($ids->contains($zona1->id));
        $this->assertTrue($ids->contains($zona2->id));

        $vacia = $desglose->firstWhere('zona_id', $zona2->id);
        $this->assertSame(0, $vacia['pesajes']);
        $this->assertSame(0.0, $vacia['toneladas']);
    }

    #[Test]
    public function desgloseByZona_no_incluye_zonas_inactivas(): void
    {
        Zona::factory()->create(['activo' => true]);
        $inactiva = Zona::factory()->inactiva()->create();

        $desglose = $this->service->desgloseByZona();

        $this->assertNull($desglose->firstWhere('zona_id', $inactiva->id));
    }

    #[Test]
    public function desgloseByZona_calcula_porcentajes_correctamente(): void
    {
        $zona1 = Zona::factory()->create();
        $zona2 = Zona::factory()->create();

        Pesaje::factory()->create(['created_at' => today(), 'zona_id' => $zona1->id, 'peso_neto_kg' => 6000]);
        Pesaje::factory()->create(['created_at' => today(), 'zona_id' => $zona2->id, 'peso_neto_kg' => 4000]);

        $desglose = $this->service->desgloseByZona();

        $this->assertSame(60.0, $desglose->firstWhere('zona_id', $zona1->id)['porcentaje']);
        $this->assertSame(40.0, $desglose->firstWhere('zona_id', $zona2->id)['porcentaje']);
    }

    // ── desgloseByTipoVehiculo ────────────────────────────────────────

    #[Test]
    public function desgloseByTipoVehiculo_agrupa_pesajes_por_tipo(): void
    {
        $tipo1 = TipoVehiculo::factory()->create(['nombre' => 'Compactador', 'activo' => true]);
        $tipo2 = TipoVehiculo::factory()->create(['nombre' => 'Volcador', 'activo' => true]);

        $v1 = Vehiculo::factory()->create(['tipo_vehiculo_id' => $tipo1->id]);
        $v2 = Vehiculo::factory()->create(['tipo_vehiculo_id' => $tipo2->id]);

        Pesaje::factory()->count(3)->create(['created_at' => today(), 'vehiculo_id' => $v1->id]);
        Pesaje::factory()->count(1)->create(['created_at' => today(), 'vehiculo_id' => $v2->id]);

        $desglose = $this->service->desgloseByTipoVehiculo();

        $this->assertSame(3, $desglose->firstWhere('nombre', 'Compactador')['pesajes']);
        $this->assertSame(1, $desglose->firstWhere('nombre', 'Volcador')['pesajes']);
    }

    #[Test]
    public function desgloseByTipoVehiculo_incluye_tipos_activos_sin_pesajes(): void
    {
        TipoVehiculo::factory()->create(['nombre' => 'Sin actividad', 'activo' => true]);

        $desglose = $this->service->desgloseByTipoVehiculo();

        $entry = $desglose->firstWhere('nombre', 'Sin actividad');
        $this->assertNotNull($entry);
        $this->assertSame(0, $entry['pesajes']);
        $this->assertSame(0.0, $entry['toneladas']);
    }

    // ── kpisDelRango ──────────────────────────────────────────────────

    #[Test]
    public function kpisDelRango_calcula_metricas_del_rango_dado(): void
    {
        $desde = today()->subDays(6)->startOfDay();
        $hasta = today()->endOfDay();

        Pesaje::factory()->create(['created_at' => today()->subDays(3), 'peso_neto_kg' => 5000]);
        Pesaje::factory()->create(['created_at' => today(), 'peso_neto_kg' => 3000]);
        // Fuera del rango — no debe contarse
        Pesaje::factory()->create(['created_at' => today()->subDays(10), 'peso_neto_kg' => 9999]);

        $kpis = $this->service->kpisDelRango($desde, $hasta);

        $this->assertSame(2, $kpis['total']);
        $this->assertSame(8.0, $kpis['toneladas']);
        $this->assertSame(7, $kpis['dias_rango']);
    }

    // ── kpisDelMes delta ──────────────────────────────────────────────

    #[Test]
    public function kpisDelMes_calcula_delta_contra_mes_anterior(): void
    {
        // Mes actual: 4 pesajes de 2000 kg c/u = 8000 kg
        Pesaje::factory()->count(4)->create(['created_at' => today(), 'peso_neto_kg' => 2000]);
        // Mes anterior mismo período: 2 pesajes → delta total = +100%, delta toneladas = +100%
        Pesaje::factory()->count(2)->create(['created_at' => today()->subMonth(), 'peso_neto_kg' => 2000]);

        $kpis = $this->service->kpisDelMes();

        $this->assertSame(4, $kpis['total']);
        $this->assertSame(2, $kpis['delta_base']);
        $this->assertSame(100.0, $kpis['delta']);
        $this->assertSame(8.0, $kpis['toneladas']);
        $this->assertSame(4.0, $kpis['delta_toneladas_base']);
        $this->assertSame(100.0, $kpis['delta_toneladas']);
    }

    // ── desgloseByZona turno ──────────────────────────────────────────

    #[Test]
    public function desgloseByZona_agrupa_misma_zona_en_turnos_distintos(): void
    {
        $zona = Zona::factory()->create();

        Pesaje::factory()->create(['created_at' => today(), 'zona_id' => $zona->id, 'turno' => 'Mañana', 'peso_neto_kg' => 3000]);
        Pesaje::factory()->create(['created_at' => today(), 'zona_id' => $zona->id, 'turno' => 'Tarde',  'peso_neto_kg' => 2000]);

        $desglose = $this->service->desgloseByZona();
        $entradas = $desglose->filter(fn ($e) => $e['zona_id'] === $zona->id);

        $this->assertCount(2, $entradas);

        $turnos = $entradas->pluck('turno')->sort()->values();
        $this->assertEquals(['Mañana', 'Tarde'], $turnos->all());
    }

    #[Test]
    public function desgloseByZona_calcula_kg_por_ha_y_kg_por_hab_de_zona(): void
    {
        $zona = Zona::factory()->create(['hectareas' => 100.0, 'habitantes' => 5000]);

        // 2 pesajes × 5000 kg, mismo turno → mismo grupo → 10 000 kg para esta zona
        Pesaje::factory()->count(2)->create(['created_at' => today(), 'zona_id' => $zona->id, 'peso_neto_kg' => 5000, 'turno' => 'Mañana']);

        $desglose  = $this->service->desgloseByZona();
        $entrada   = $desglose->firstWhere('zona_id', $zona->id);

        // kg_por_ha  = round(10000 / 100,  1) = 100.0
        // kg_por_hab = round(10000 / 5000, 2) =   2.0
        $this->assertEquals(100.0, $entrada['kg_por_ha']);
        $this->assertEquals(2.0,   $entrada['kg_por_hab']);
    }

    #[Test]
    public function desgloseByZona_viene_ordenado_descendente_por_toneladas(): void
    {
        $zona1 = Zona::factory()->create();
        $zona2 = Zona::factory()->create();

        Pesaje::factory()->create(['created_at' => today(), 'zona_id' => $zona1->id, 'peso_neto_kg' => 8000]);
        Pesaje::factory()->create(['created_at' => today(), 'zona_id' => $zona2->id, 'peso_neto_kg' => 2000]);

        $toneladas = $this->service->desgloseByZona()->pluck('toneladas');

        $this->assertTrue($toneladas->first() >= $toneladas->last());
        $this->assertEquals(8.0, $toneladas->first());
    }

    // ── evolucionDelRango ──────────────────────────────────────────────

    #[Test]
    public function evolucionDelRango_contiene_un_dato_por_dia_del_rango(): void
    {
        $desde = today()->subDays(13)->startOfDay();
        $hasta = today()->endOfDay();

        Pesaje::factory()->create(['created_at' => today()->subDays(5), 'peso_neto_kg' => 4000]);

        $result = $this->service->evolucionDelRango($desde, $hasta);

        $this->assertCount(14, $result['datos']);
    }

    #[Test]
    public function evolucionDelRango_registra_toneladas_en_el_dia_correcto(): void
    {
        $desde = today()->subDays(6)->startOfDay();
        $hasta = today()->endOfDay();

        Pesaje::factory()->create(['created_at' => today()->subDays(2), 'peso_neto_kg' => 6000]);

        $datos = collect($this->service->evolucionDelRango($desde, $hasta)['datos']);

        // El 5to día (índice 4 desde el inicio, que es subDays(2)) debe tener 6.0 tons
        $diaConPesaje = $datos->firstWhere('toneladas', 6.0);
        $this->assertNotNull($diaConPesaje);

        // Los demás días deben ser 0
        $this->assertEquals(6, $datos->where('toneladas', 0)->count());
    }

    // ── cache de totalesZonas ─────────────────────────────────────────

    #[Test]
    public function totalesZonas_se_consulta_una_sola_vez_al_combinar_kpisDelDia_y_kpisDelMes(): void
    {
        Zona::factory()->count(3)->create();

        DB::enableQueryLog();

        $this->service->kpisDelDia();
        $this->service->kpisDelMes();

        $zonaQueries = collect(DB::getQueryLog())
            ->filter(fn ($q) => str_contains(strtolower($q['query']), 'from "zonas"')
                             || str_contains(strtolower($q['query']), "from `zonas`"));

        DB::disableQueryLog();

        $this->assertCount(1, $zonaQueries);
    }
}
