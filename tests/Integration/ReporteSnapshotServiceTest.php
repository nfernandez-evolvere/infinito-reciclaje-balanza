<?php

namespace Tests\Integration;

use App\Models\ReporteConfiguracion;
use App\Models\ReporteGenerado;
use App\Services\ReporteSnapshotService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReporteSnapshotServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): ReporteSnapshotService
    {
        return app(ReporteSnapshotService::class);
    }

    /** Un reporte "vivo" estilo Excel: colecciones, modelo config, Carbon en pivots. */
    private function reporteVivo(): array
    {
        $stat = ['total_viajes' => 0, 'total_kg' => 0, 'tipos' => []];

        return [
            'kpis'      => ['total' => 2, 'toneladas' => 5.5, 'dias_op' => 1, 'dias_rango' => 31, 'promedio_ton_dia' => 5.0, 'promedio_kg_viaje' => 2500],
            'evolucion' => ['datos' => [['fecha' => '10/03', 'viajes' => 2, 'toneladas' => 5.0]], 'promedio' => 5.0, 'maximo' => 5.0, 'minimo' => 5.0],
            'zonas'     => collect([['nombre' => 'Centro', 'turno' => 'Diurna', 'viajes' => 2, 'toneladas' => 5.0, 'kg_viaje' => 2500, 'porcentaje' => 100.0, 'kg_ha' => null, 'kg_hab' => null]]),
            'vehiculos' => collect([['nombre' => 'Compactador', 'viajes' => 2, 'toneladas' => 5.0, 'kg_viaje' => 2500, 'porcentaje' => 100.0]]),
            'config'    => ReporteConfiguracion::create([
                'municipalidad_nombre' => 'Muni Test',
                'intro_empresa'        => 'Una intro.',
                'servicios'            => [['titulo' => 'Recolección', 'descripcion' => 'desc']],
                'ai_enabled'           => true,
                'ai_api_key'           => 'secreto-no-debe-filtrarse',
            ]),
            'conclusiones'   => [],
            'kg_netos_total' => 5000,
            'detalle'        => [[
                'fecha' => '10/03/2026', 'hora' => '08:00', 'patente' => 'ABC123',
                'tipo_vehiculo' => 'Compactador', 'tipo_servicio' => '—', 'zona' => 'Centro',
                'turno' => 'Diurna', 'operador' => '—', 'peso_bruto_kg' => 5800,
                'peso_tara_kg' => 800, 'peso_neto_kg' => 5000, 'estado' => 'Cerrado',
                'editado' => false, 'alerta_peso' => false,
            ]],
            'pivots' => [
                'tipos'  => collect([['id' => 1, 'nombre' => 'Compactador']]),
                'diario' => [
                    'filas'    => [['fecha' => Carbon::parse('2026-03-10'), 'total_viajes' => 2, 'total_kg' => 5000, 'tipos' => [1 => ['viajes' => 2, 'kg' => 5000]]]],
                    'totales'  => ['total_viajes' => 2, 'total_kg' => 5000, 'tipos' => [1 => ['viajes' => 2, 'kg' => 5000]]],
                    'promedio' => $stat, 'maximo' => $stat, 'minimo' => $stat,
                ],
                'zonaTipo' => ['filas' => [], 'totales' => ['total_viajes' => 0, 'total_kg' => 0, 'tipos' => [], 'porcentaje' => 0.0]],
                'zonaDia'  => ['fechas' => [Carbon::parse('2026-03-10')], 'filas' => [], 'totales' => ['dias' => [], 'total' => 0]],
            ],
        ];
    }

    #[Test]
    public function capturar_serializes_collections_carbon_and_config_without_leaking_the_api_key(): void
    {
        $snapshot = $this->service()->capturar($this->reporteVivo());

        // Colecciones aplanadas a arrays.
        $this->assertIsArray($snapshot['zonas']);
        $this->assertIsArray($snapshot['pivots']['tipos']);
        $this->assertSame('Centro', $snapshot['zonas'][0]['nombre']);

        // Carbon en pivots → string ISO 8601.
        $this->assertIsString($snapshot['pivots']['diario']['filas'][0]['fecha']);
        $this->assertStringStartsWith('2026-03-10', $snapshot['pivots']['diario']['filas'][0]['fecha']);
        $this->assertIsString($snapshot['pivots']['zonaDia']['fechas'][0]);

        // Config: solo campos de marca, nunca la API key.
        $this->assertSame('Muni Test', $snapshot['config']['municipalidad_nombre']);
        $this->assertArrayNotHasKey('ai_api_key', $snapshot['config']);
        $this->assertArrayNotHasKey('ai_enabled', $snapshot['config']);

        // Detalle aplanado se conserva intacto.
        $this->assertSame(5000, $snapshot['detalle'][0]['peso_neto_kg']);
    }

    #[Test]
    public function rehidratar_reconstructs_carbon_collections_and_config_object(): void
    {
        $snapshot = $this->service()->capturar($this->reporteVivo());

        $generado = ReporteGenerado::create([
            'origen'        => 'manual',
            'tipo'          => 'informe_mensual',
            'formato'       => 'excel',
            'periodo_desde' => '2026-03-01',
            'periodo_hasta' => '2026-03-31',
            'estado'        => 'generado',
            'snapshot'      => $snapshot,
        ]);

        // fresh(): fuerza el ida-y-vuelta real por la base (JSON → array).
        $reporte = $this->service()->rehidratar($generado->fresh());

        $this->assertInstanceOf(Carbon::class, $reporte['desde']);
        $this->assertSame('2026-03-01', $reporte['desde']->toDateString());
        $this->assertInstanceOf(Carbon::class, $reporte['hasta']);

        $this->assertInstanceOf(Collection::class, $reporte['zonas']);
        $this->assertInstanceOf(Collection::class, $reporte['vehiculos']);
        $this->assertInstanceOf(Collection::class, $reporte['pivots']['tipos']);
        $this->assertSame('Compactador', $reporte['pivots']['tipos']->first()['nombre']);

        // Fechas de pivots reconstruidas como Carbon.
        $this->assertInstanceOf(Carbon::class, $reporte['pivots']['diario']['filas'][0]['fecha']);
        $this->assertSame('2026-03-10', $reporte['pivots']['diario']['filas'][0]['fecha']->toDateString());
        $this->assertInstanceOf(Carbon::class, $reporte['pivots']['zonaDia']['fechas'][0]);

        // Claves de tipo (int) sobreviven el ida-y-vuelta JSON.
        $this->assertSame(5000, $reporte['pivots']['diario']['filas'][0]['tipos'][1]['kg']);

        // Config como objeto accesible por propiedad (como lo consumen vista/Excel).
        $this->assertIsObject($reporte['config']);
        $this->assertSame('Muni Test', $reporte['config']->municipalidad_nombre);

        // KPIs y detalle congelados intactos.
        $this->assertSame(5.5, $reporte['kpis']['toneladas']);
        $this->assertSame(5000, $reporte['detalle'][0]['peso_neto_kg']);
        $this->assertSame(5000, $reporte['kg_netos_total']);
    }

    #[Test]
    public function round_trip_preserves_alertas_as_objects_with_carbon_and_zona(): void
    {
        $reporte = [
            'kpis'      => ['total' => 0, 'toneladas' => 0.0, 'dias_op' => 0, 'dias_rango' => 31, 'promedio_ton_dia' => 0, 'promedio_kg_viaje' => 0],
            'evolucion' => ['datos' => [], 'promedio' => 0, 'maximo' => 0, 'minimo' => 0],
            'zonas'     => collect(),
            'vehiculos' => collect(),
            'config'    => null,
            'conclusiones' => [],
            'alertas'   => collect([
                (object) [
                    'tipo'            => 'peso_fuera_rango',
                    'titulo'          => 'Peso fuera de rango — ABC123',
                    'descripcion'     => 'Detalle de la alerta.',
                    'fecha_deteccion' => Carbon::parse('2026-03-15'),
                    'leida'           => true,
                    'zona'            => (object) ['nombre' => 'Zona Norte'],
                ],
            ]),
        ];

        $generado = ReporteGenerado::create([
            'origen'        => 'programado',
            'tipo'          => 'alertas',
            'formato'       => 'pdf',
            'periodo_desde' => '2026-03-01',
            'periodo_hasta' => '2026-03-31',
            'estado'        => 'enviado',
            'snapshot'      => $this->service()->capturar($reporte),
        ]);

        $rehidratado = $this->service()->rehidratar($generado->fresh());

        $this->assertInstanceOf(Collection::class, $rehidratado['alertas']);
        $this->assertCount(1, $rehidratado['alertas']);

        $alerta = $rehidratado['alertas']->first();
        $this->assertSame('peso_fuera_rango', $alerta->tipo);
        $this->assertSame('Peso fuera de rango — ABC123', $alerta->titulo);
        $this->assertInstanceOf(Carbon::class, $alerta->fecha_deteccion);
        $this->assertSame('2026-03-15', $alerta->fecha_deteccion->toDateString());
        $this->assertTrue($alerta->leida);
        $this->assertSame('Zona Norte', $alerta->zona->nombre);

        // Reporte de alertas no arrastra pivots/detalle de Excel.
        $this->assertArrayNotHasKey('pivots', $rehidratado);
        $this->assertSame([], $rehidratado['detalle']);
    }
}
