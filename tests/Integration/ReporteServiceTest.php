<?php

namespace Tests\Integration;

use App\Models\Pesaje;
use App\Models\TipoServicio;
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

    // ── Reporte v2 (por servicio / N° interno) ────────────────────────────────

    /** Bloques del Excel v2 sobre el rango dado. */
    private function datosV2(string $desde, string $hasta): array
    {
        $d = Carbon::parse($desde);
        $h = Carbon::parse($hasta);

        return $this->service->datosExcelV2($this->service->generar($d, $h)['detalle'], $d, $h);
    }

    #[Test]
    public function por_servicio_agrupa_por_tipo_servicio_y_calcula_porcentaje(): void
    {
        $dom = TipoServicio::factory()->create(['nombre' => 'Domiciliario']);
        $vol = TipoServicio::factory()->create(['nombre' => 'Voluminosos']);

        $this->pesaje('2026-06-05', 6000, ['tipo_servicio_id' => $dom->id]);
        $this->pesaje('2026-06-06', 4000, ['tipo_servicio_id' => $dom->id]);   // Domiciliario: 10 000
        $this->pesaje('2026-06-07', 20000, ['tipo_servicio_id' => $vol->id]);  // Voluminosos: 20 000

        $porServicio = $this->service->porServicio(
            $this->service->generar(Carbon::parse('2026-06-01'), Carbon::parse('2026-06-30'))['detalle']
        );

        // Ordenado por kg desc: Voluminosos primero.
        $this->assertSame(['Voluminosos', 'Domiciliario'], $porServicio->pluck('nombre')->all());

        $v = $porServicio->firstWhere('nombre', 'Voluminosos');
        $this->assertSame(1, $v['viajes']);
        $this->assertSame(20000, $v['kg']);
        $this->assertSame(20.0, $v['toneladas']);
        $this->assertSame(66.7, $v['porcentaje']);   // 20 000 / 30 000

        $d = $porServicio->firstWhere('nombre', 'Domiciliario');
        $this->assertSame(2, $d['viajes']);
        $this->assertSame(10000, $d['kg']);
        $this->assertSame(33.3, $d['porcentaje']);   // 10 000 / 30 000
    }

    #[Test]
    public function datos_excel_v2_retorna_los_bloques_esperados(): void
    {
        $this->pesaje('2026-06-05', 5000);

        $datos = $this->datosV2('2026-06-01', '2026-06-30');

        $this->assertSame(
            ['tipos', 'fechas', 'resumenPorDia', 'porServicio', 'servicioTipoVehiculo', 'diario', 'porNumeroInterno', 'servicios'],
            array_keys($datos)
        );
    }

    #[Test]
    public function servicio_tipo_vehiculo_cruza_kg_y_viajes_con_fila_total(): void
    {
        $comp = TipoVehiculo::factory()->create(['nombre' => 'Compactador']);
        $volc = TipoVehiculo::factory()->create(['nombre' => 'Volcador']);
        $vComp = Vehiculo::factory()->create(['tipo_vehiculo_id' => $comp->id]);
        $vVolc = Vehiculo::factory()->create(['tipo_vehiculo_id' => $volc->id]);

        $dom = TipoServicio::factory()->create(['nombre' => 'Domiciliario']);
        $vol = TipoServicio::factory()->create(['nombre' => 'Voluminosos']);

        // Domiciliario → Compactador: 2 viajes / 10 000.
        $this->pesaje('2026-06-05', 6000, ['vehiculo_id' => $vComp->id, 'tipo_servicio_id' => $dom->id]);
        $this->pesaje('2026-06-06', 4000, ['vehiculo_id' => $vComp->id, 'tipo_servicio_id' => $dom->id]);
        // Voluminosos → Volcador: 1 viaje / 20 000.
        $this->pesaje('2026-06-07', 20000, ['vehiculo_id' => $vVolc->id, 'tipo_servicio_id' => $vol->id]);

        $datos = $this->datosV2('2026-06-01', '2026-06-30');
        $cruce = $datos['servicioTipoVehiculo'];
        $tipos = collect($datos['tipos']);
        $idComp = $tipos->firstWhere('nombre', 'Compactador')['id'];
        $idVolc = $tipos->firstWhere('nombre', 'Volcador')['id'];

        // Filas ordenadas por kg desc: Voluminosos (20 000) primero.
        $this->assertSame(['Voluminosos', 'Domiciliario'], collect($cruce['filas'])->pluck('nombre')->all());

        $filaDom = collect($cruce['filas'])->firstWhere('nombre', 'Domiciliario');
        $this->assertSame(10000, $filaDom['total_kg']);
        $this->assertSame(2, $filaDom['total_viajes']);
        $this->assertSame(['viajes' => 2, 'kg' => 10000], $filaDom['tipos'][$idComp]);
        $this->assertSame(['viajes' => 0, 'kg' => 0], $filaDom['tipos'][$idVolc]);

        // Fila TOTAL: suma de ambos servicios, por columna.
        $this->assertSame('TOTAL', $cruce['totales']['nombre']);
        $this->assertSame(30000, $cruce['totales']['total_kg']);
        $this->assertSame(3, $cruce['totales']['total_viajes']);
        $this->assertSame(['viajes' => 2, 'kg' => 10000], $cruce['totales']['tipos'][$idComp]);
        $this->assertSame(['viajes' => 1, 'kg' => 20000], $cruce['totales']['tipos'][$idVolc]);
    }

    #[Test]
    public function por_numero_interno_cuenta_viajes_por_vehiculo_y_dia(): void
    {
        $tipo = TipoVehiculo::factory()->create(['nombre' => 'Compactador']);
        $vehiculo = Vehiculo::factory()->create(['tipo_vehiculo_id' => $tipo->id, 'numero_interno' => '7042']);

        // 2 viajes el 05, 1 el 07, ninguno el 06.
        $this->pesaje('2026-06-05', 1000, ['vehiculo_id' => $vehiculo->id]);
        $this->pesaje('2026-06-05', 1000, ['vehiculo_id' => $vehiculo->id]);
        $this->pesaje('2026-06-07', 1000, ['vehiculo_id' => $vehiculo->id]);

        $filas = $this->datosV2('2026-06-05', '2026-06-07')['porNumeroInterno']['filas'];

        $this->assertCount(1, $filas);
        $fila = $filas[0];
        $this->assertSame('7042', $fila['interno']);
        $this->assertSame('Compactador', $fila['tipo']);
        $this->assertSame(3, $fila['total']);
        $this->assertSame(2, $fila['dias']['2026-06-05']);
        $this->assertSame(0, $fila['dias']['2026-06-06']);
        $this->assertSame(1, $fila['dias']['2026-06-07']);
    }

    #[Test]
    public function zonas_por_servicio_desglosa_zonas_con_porcentaje_del_servicio(): void
    {
        $dom = TipoServicio::factory()->create(['nombre' => 'Domiciliario']);
        $zonaA = Zona::factory()->create(['nombre' => 'Zona 1', 'tipo_servicio_id' => $dom->id]);
        $zonaB = Zona::factory()->create(['nombre' => 'Zona 2', 'tipo_servicio_id' => $dom->id]);

        $this->pesaje('2026-06-05', 6000, ['tipo_servicio_id' => $dom->id, 'zona_id' => $zonaA->id, 'turno' => 'Diurna']);
        $this->pesaje('2026-06-06', 2000, ['tipo_servicio_id' => $dom->id, 'zona_id' => $zonaB->id, 'turno' => 'Diurna']);

        $servicios = $this->datosV2('2026-06-01', '2026-06-30')['servicios'];

        $this->assertCount(1, $servicios);
        $servicio = $servicios[0];
        $this->assertSame('Domiciliario', $servicio['nombre']);
        $this->assertSame(8000, $servicio['kg']);
        $this->assertSame(2, $servicio['viajes']);

        // Zonas ordenadas por kg desc; % relativo al total del servicio (8 000).
        $this->assertSame('Zona 1 DIURNA', $servicio['zonas'][0]['label']);
        $this->assertSame(6000, $servicio['zonas'][0]['kg']);
        $this->assertSame(0.75, $servicio['zonas'][0]['porcentaje']);
        $this->assertSame('Zona 2 DIURNA', $servicio['zonas'][1]['label']);
        $this->assertSame(2000, $servicio['zonas'][1]['kg']);
        $this->assertSame(0.25, $servicio['zonas'][1]['porcentaje']);
    }

    #[Test]
    public function por_servicio_incluye_descripcion_y_conteo_de_zonas(): void
    {
        $dom = TipoServicio::factory()->create(['nombre' => 'Domiciliario', 'descripcion' => 'Residuos de los hogares.']);
        $zonaA = Zona::factory()->create(['tipo_servicio_id' => $dom->id]);
        $zonaB = Zona::factory()->create(['tipo_servicio_id' => $dom->id]);

        $this->pesaje('2026-06-05', 5000, ['tipo_servicio_id' => $dom->id, 'zona_id' => $zonaA->id]);
        $this->pesaje('2026-06-06', 3000, ['tipo_servicio_id' => $dom->id, 'zona_id' => $zonaA->id]);
        $this->pesaje('2026-06-07', 2000, ['tipo_servicio_id' => $dom->id, 'zona_id' => $zonaB->id]);

        $servicio = $this->service->porServicio(
            $this->service->generar(Carbon::parse('2026-06-01'), Carbon::parse('2026-06-30'))['detalle']
        )->firstWhere('nombre', 'Domiciliario');

        $this->assertSame('Residuos de los hogares.', $servicio['descripcion']);
        $this->assertSame(2, $servicio['zonas']);   // dos zonas distintas con actividad
        $this->assertSame(3, $servicio['viajes']);
        $this->assertSame(10000, $servicio['kg']);
    }

    #[Test]
    public function por_semana_agrupa_en_ventanas_de_7_dias_fusionando_el_resto(): void
    {
        $this->pesaje('2026-06-03', 5000);   // semana 1 (01–07)
        $this->pesaje('2026-06-10', 3000);   // semana 2 (08–14)
        $this->pesaje('2026-06-25', 7000);   // semana 4 (22–30)
        $this->pesaje('2026-06-29', 1000);   // semana 4 (resto fusionado)

        $semanas = $this->service->porSemana(
            $this->service->generar(Carbon::parse('2026-06-01'), Carbon::parse('2026-06-30'))['detalle'],
            Carbon::parse('2026-06-01'),
            Carbon::parse('2026-06-30'),
        );

        // 30 días → 4 ventanas: 01-07, 08-14, 15-21, 22-30 (la última absorbe el resto corto).
        $this->assertCount(4, $semanas);

        $this->assertSame(1, $semanas[0]['numero']);
        $this->assertSame('2026-06-01', $semanas[0]['desde']->toDateString());
        $this->assertSame('2026-06-07', $semanas[0]['hasta']->toDateString());
        $this->assertSame(5000, $semanas[0]['kg']);
        $this->assertSame(1, $semanas[0]['viajes']);

        $this->assertSame(3000, $semanas[1]['kg']);
        $this->assertSame('2026-06-08', $semanas[1]['desde']->toDateString());
        $this->assertSame('2026-06-14', $semanas[1]['hasta']->toDateString());

        $this->assertSame(0, $semanas[2]['kg']);   // semana sin actividad
        $this->assertSame(0, $semanas[2]['viajes']);

        // Semana 4 fusionada: 22 al 30, con los dos pesajes.
        $this->assertSame('2026-06-22', $semanas[3]['desde']->toDateString());
        $this->assertSame('2026-06-30', $semanas[3]['hasta']->toDateString());
        $this->assertSame(8000, $semanas[3]['kg']);
        $this->assertSame(2, $semanas[3]['viajes']);
    }

    #[Test]
    public function por_dia_semana_acumula_lunes_a_domingo(): void
    {
        // 06-01 y 06-08 caen el mismo día de la semana; 06-02 el siguiente.
        $this->pesaje('2026-06-01', 5000);
        $this->pesaje('2026-06-08', 3000);
        $this->pesaje('2026-06-02', 2000);

        $result = $this->service->porDiaSemana(
            $this->service->generar(Carbon::parse('2026-06-01'), Carbon::parse('2026-06-30'))['detalle']
        );

        // Siempre los 7 días, Lunes primero y Domingo último.
        $this->assertCount(7, $result);
        $this->assertSame('Lunes', $result[0]['dia']);
        $this->assertSame('Domingo', $result[6]['dia']);

        $nombres = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        $diaUno = $nombres[Carbon::parse('2026-06-01')->dayOfWeekIso - 1];
        $diaDos = $nombres[Carbon::parse('2026-06-02')->dayOfWeekIso - 1];

        $filaUno = collect($result)->firstWhere('dia', $diaUno);
        $this->assertSame(8000, $filaUno['kg']);    // 06-01 + 06-08
        $this->assertSame(2, $filaUno['viajes']);

        $filaDos = collect($result)->firstWhere('dia', $diaDos);
        $this->assertSame(2000, $filaDos['kg']);
        $this->assertSame(1, $filaDos['viajes']);
    }

    #[Test]
    public function vehiculos_operativos_cuenta_vehiculos_distintos(): void
    {
        $vA = Vehiculo::factory()->create();
        $vB = Vehiculo::factory()->create();

        $this->pesaje('2026-06-05', 5000, ['vehiculo_id' => $vA->id]);
        $this->pesaje('2026-06-06', 3000, ['vehiculo_id' => $vA->id]);   // mismo vehículo
        $this->pesaje('2026-06-07', 2000, ['vehiculo_id' => $vB->id]);

        $operativos = $this->service->vehiculosOperativos(
            $this->service->generar(Carbon::parse('2026-06-01'), Carbon::parse('2026-06-30'))['detalle']
        );

        $this->assertSame(2, $operativos);
    }
}
