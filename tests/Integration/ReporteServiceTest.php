<?php

namespace Tests\Integration;

use App\Models\Pesaje;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use App\Models\Zona;
use App\Repositories\PesajeRepository;
use App\Services\ReporteService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReporteServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReporteService $service;

    protected function setUp(): void
    {
        parent::setUp();
        // Tiempo congelado: el cálculo de rangos y agrupación por día es determinista.
        Carbon::setTestNow('2026-06-15 10:00:00');
        $this->service = new ReporteService(new PesajeRepository);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** Crea un pesaje Cerrado en una fecha y con un neto controlados. */
    private function pesaje(string $fecha, int $neto, array $extra = []): Pesaje
    {
        return Pesaje::factory()->create(array_merge([
            'created_at'   => Carbon::parse($fecha),
            'peso_neto_kg' => $neto,
            'estado'       => 'Cerrado',
        ], $extra));
    }

    // ── estructura ────────────────────────────────────────────────────

    #[Test]
    public function generar_retorna_la_estructura_completa(): void
    {
        $reporte = $this->service->generar(Carbon::parse('2026-06-01'), Carbon::parse('2026-06-30'));

        $this->assertSame(
            ['desde', 'hasta', 'filtros', 'kpis', 'evolucion', 'zonas', 'vehiculos', 'detalle'],
            array_keys($reporte)
        );
    }

    // ── KPIs ──────────────────────────────────────────────────────────

    #[Test]
    public function kpis_calcula_total_toneladas_y_dias_operativos(): void
    {
        $this->pesaje('2026-06-05', 5000);
        $this->pesaje('2026-06-05', 3000);  // mismo día → 1 solo día operativo
        $this->pesaje('2026-06-10', 2000);

        $kpis = $this->service->generar(Carbon::parse('2026-06-01'), Carbon::parse('2026-06-30'))['kpis'];

        $this->assertSame(3, $kpis['total']);
        $this->assertSame(10.0, $kpis['toneladas']);   // 10 000 kg
        $this->assertSame(2, $kpis['dias_op']);        // 05 y 10
    }

    #[Test]
    public function kpis_calcula_promedios(): void
    {
        $this->pesaje('2026-06-05', 6000);
        $this->pesaje('2026-06-05', 4000);
        $this->pesaje('2026-06-10', 2000);

        $kpis = $this->service->generar(Carbon::parse('2026-06-01'), Carbon::parse('2026-06-30'))['kpis'];

        // 12 000 kg en 2 días operativos → 6 t/día.
        $this->assertSame(6.0, $kpis['promedio_ton_dia']);
        // 12 000 kg en 3 viajes → 4000 kg/viaje.
        $this->assertSame(4000, $kpis['promedio_kg_viaje']);
    }

    #[Test]
    public function kpis_dias_rango_cuenta_inclusive(): void
    {
        // Mismo día desde/hasta → 1 día de rango.
        $unDia = $this->service->generar(Carbon::parse('2026-06-10'), Carbon::parse('2026-06-10'))['kpis'];
        $this->assertSame(1, $unDia['dias_rango']);

        // Junio completo → 30 días.
        $mes = $this->service->generar(Carbon::parse('2026-06-01'), Carbon::parse('2026-06-30'))['kpis'];
        $this->assertSame(30, $mes['dias_rango']);
    }

    #[Test]
    public function kpis_en_cero_no_divide_por_cero(): void
    {
        $kpis = $this->service->generar(Carbon::parse('2026-06-01'), Carbon::parse('2026-06-30'))['kpis'];

        $this->assertSame(0, $kpis['total']);
        $this->assertSame(0.0, $kpis['toneladas']);
        $this->assertSame(0, $kpis['dias_op']);
        $this->assertSame(0, $kpis['promedio_ton_dia']);
        $this->assertSame(0, $kpis['promedio_kg_viaje']);
        $this->assertSame(30, $kpis['dias_rango']);
    }

    #[Test]
    public function kpis_excluye_pesajes_cancelados(): void
    {
        $this->pesaje('2026-06-05', 5000);
        $this->pesaje('2026-06-06', 9000, ['estado' => 'Cancelado']);

        $kpis = $this->service->generar(Carbon::parse('2026-06-01'), Carbon::parse('2026-06-30'))['kpis'];

        $this->assertSame(1, $kpis['total']);
        $this->assertSame(5.0, $kpis['toneladas']);
    }

    // ── Evolución ─────────────────────────────────────────────────────

    #[Test]
    public function evolucion_tiene_un_dato_por_dia_del_rango(): void
    {
        $evolucion = $this->service->generar(Carbon::parse('2026-06-01'), Carbon::parse('2026-06-07'))['evolucion'];

        $this->assertCount(7, $evolucion['datos']);
    }

    #[Test]
    public function evolucion_registra_viajes_y_toneladas_en_el_dia_correcto(): void
    {
        $this->pesaje('2026-06-03', 5000);
        $this->pesaje('2026-06-03', 3000);  // mismo día → 2 viajes, 8 t

        $datos = collect(
            $this->service->generar(Carbon::parse('2026-06-01'), Carbon::parse('2026-06-07'))['evolucion']['datos']
        );

        // El día con actividad: 2 viajes, 8.0 toneladas.
        $conActividad = $datos->firstWhere('toneladas', 8.0);
        $this->assertNotNull($conActividad);
        $this->assertSame(2, $conActividad['viajes']);

        // El resto de los días: 0.
        $this->assertSame(6, $datos->where('toneladas', 0)->count());
    }

    #[Test]
    public function evolucion_promedio_max_min_solo_sobre_dias_con_actividad(): void
    {
        $this->pesaje('2026-06-02', 8000);  // 8.0 t
        $this->pesaje('2026-06-05', 2000);  // 2.0 t

        $evolucion = $this->service->generar(Carbon::parse('2026-06-01'), Carbon::parse('2026-06-07'))['evolucion'];

        // Promedio sobre los 2 días con actividad (no sobre los 7 del rango).
        $this->assertSame(5.0, $evolucion['promedio']);  // (8 + 2) / 2
        $this->assertSame(8.0, $evolucion['maximo']);
        $this->assertSame(2.0, $evolucion['minimo']);
    }

    // ── Por zona ──────────────────────────────────────────────────────

    #[Test]
    public function zonas_agrupa_la_misma_zona_en_turnos_distintos(): void
    {
        $zona = Zona::factory()->create(['hectareas' => 100, 'habitantes' => 5000]);

        $this->pesaje('2026-06-05', 3000, ['zona_id' => $zona->id, 'turno' => 'Diurna']);
        $this->pesaje('2026-06-05', 2000, ['zona_id' => $zona->id, 'turno' => 'Nocturna']);

        $zonas = $this->service->generar(Carbon::parse('2026-06-01'), Carbon::parse('2026-06-30'))['zonas'];

        $deEstaZona = $zonas->filter(fn ($z) => $z['nombre'] === $zona->nombre);
        $this->assertCount(2, $deEstaZona);
        $this->assertEqualsCanonicalizing(['Diurna', 'Nocturna'], $deEstaZona->pluck('turno')->all());
    }

    #[Test]
    public function zonas_calcula_porcentaje_kg_ha_y_kg_hab(): void
    {
        $zonaA = Zona::factory()->create(['hectareas' => 100.0, 'habitantes' => 5000]);
        $zonaB = Zona::factory()->create(['hectareas' => 200.0, 'habitantes' => 8000]);

        $this->pesaje('2026-06-05', 5000, ['zona_id' => $zonaA->id, 'turno' => 'Diurna']);
        $this->pesaje('2026-06-06', 5000, ['zona_id' => $zonaA->id, 'turno' => 'Diurna']);
        $this->pesaje('2026-06-07', 4000, ['zona_id' => $zonaB->id, 'turno' => 'Diurna']);

        $zonas = $this->service->generar(Carbon::parse('2026-06-01'), Carbon::parse('2026-06-30'))['zonas'];

        // Zona A: 10 000 kg de 14 000 totales → 71.4 %.
        $a = $zonas->firstWhere('nombre', $zonaA->nombre);
        $this->assertSame(2, $a['viajes']);
        $this->assertSame(10.0, $a['toneladas']);
        $this->assertSame(5000, $a['kg_viaje']);
        $this->assertSame(71.4, $a['porcentaje']);
        $this->assertSame(100.0, $a['kg_ha']);   // 10 000 / 100
        $this->assertSame(2.0, $a['kg_hab']);     // 10 000 / 5000
    }

    #[Test]
    public function zonas_kg_ha_y_kg_hab_son_null_sin_datos_de_zona(): void
    {
        $zona = Zona::factory()->create(['hectareas' => null, 'habitantes' => null]);

        $this->pesaje('2026-06-05', 5000, ['zona_id' => $zona->id, 'turno' => 'Diurna']);

        $zonas = $this->service->generar(Carbon::parse('2026-06-01'), Carbon::parse('2026-06-30'))['zonas'];
        $entry = $zonas->firstWhere('nombre', $zona->nombre);

        $this->assertNull($entry['kg_ha']);
        $this->assertNull($entry['kg_hab']);
    }

    #[Test]
    public function zonas_vienen_ordenadas_desc_por_toneladas(): void
    {
        $chica = Zona::factory()->create();
        $grande = Zona::factory()->create();

        $this->pesaje('2026-06-05', 2000, ['zona_id' => $chica->id, 'turno' => 'Diurna']);
        $this->pesaje('2026-06-06', 9000, ['zona_id' => $grande->id, 'turno' => 'Diurna']);

        $zonas = $this->service->generar(Carbon::parse('2026-06-01'), Carbon::parse('2026-06-30'))['zonas'];

        $this->assertSame($grande->nombre, $zonas->first()['nombre']);
        $this->assertGreaterThanOrEqual($zonas->last()['toneladas'], $zonas->first()['toneladas']);
    }

    // ── Por vehículo ──────────────────────────────────────────────────

    #[Test]
    public function vehiculos_agrupan_por_tipo_y_calculan_metricas(): void
    {
        $tipoA = TipoVehiculo::factory()->create(['nombre' => 'Compactador']);
        $tipoB = TipoVehiculo::factory()->create(['nombre' => 'Volcador']);
        $vA = Vehiculo::factory()->create(['tipo_vehiculo_id' => $tipoA->id]);
        $vB = Vehiculo::factory()->create(['tipo_vehiculo_id' => $tipoB->id]);

        $this->pesaje('2026-06-05', 6000, ['vehiculo_id' => $vA->id]);
        $this->pesaje('2026-06-06', 4000, ['vehiculo_id' => $vA->id]);
        $this->pesaje('2026-06-07', 2000, ['vehiculo_id' => $vB->id]);

        $vehiculos = $this->service->generar(Carbon::parse('2026-06-01'), Carbon::parse('2026-06-30'))['vehiculos'];

        $comp = $vehiculos->firstWhere('nombre', 'Compactador');
        $this->assertSame(2, $comp['viajes']);
        $this->assertSame(10.0, $comp['toneladas']);
        $this->assertSame(5000, $comp['kg_viaje']);          // 10 000 / 2
        $this->assertSame(83.3, $comp['porcentaje']);        // 10 000 / 12 000
    }

    #[Test]
    public function vehiculos_vienen_ordenados_desc_por_toneladas(): void
    {
        $tipoA = TipoVehiculo::factory()->create(['nombre' => 'Chico']);
        $tipoB = TipoVehiculo::factory()->create(['nombre' => 'Grande']);
        $vA = Vehiculo::factory()->create(['tipo_vehiculo_id' => $tipoA->id]);
        $vB = Vehiculo::factory()->create(['tipo_vehiculo_id' => $tipoB->id]);

        $this->pesaje('2026-06-05', 1000, ['vehiculo_id' => $vA->id]);
        $this->pesaje('2026-06-06', 9000, ['vehiculo_id' => $vB->id]);

        $vehiculos = $this->service->generar(Carbon::parse('2026-06-01'), Carbon::parse('2026-06-30'))['vehiculos'];

        $this->assertSame('Grande', $vehiculos->first()['nombre']);
    }

    // ── Filtros ───────────────────────────────────────────────────────

    #[Test]
    public function filtra_por_zona(): void
    {
        $zonaA = Zona::factory()->create();
        $zonaB = Zona::factory()->create();

        $this->pesaje('2026-06-05', 5000, ['zona_id' => $zonaA->id, 'turno' => 'Diurna']);
        $this->pesaje('2026-06-06', 3000, ['zona_id' => $zonaB->id, 'turno' => 'Diurna']);

        $reporte = $this->service->generar(
            Carbon::parse('2026-06-01'),
            Carbon::parse('2026-06-30'),
            ['zona_id' => $zonaA->id]
        );

        $this->assertSame(1, $reporte['kpis']['total']);
        $this->assertSame(5.0, $reporte['kpis']['toneladas']);
        $this->assertCount(1, $reporte['zonas']);
        $this->assertSame($zonaA->nombre, $reporte['zonas']->first()['nombre']);
    }

    #[Test]
    public function excluye_pesajes_fuera_del_rango_de_fechas(): void
    {
        $this->pesaje('2026-06-10', 5000);   // dentro
        $this->pesaje('2026-05-31', 9000);   // antes del rango
        $this->pesaje('2026-07-01', 7000);   // después del rango

        $kpis = $this->service->generar(Carbon::parse('2026-06-01'), Carbon::parse('2026-06-30'))['kpis'];

        $this->assertSame(1, $kpis['total']);
        $this->assertSame(5.0, $kpis['toneladas']);
    }
}
