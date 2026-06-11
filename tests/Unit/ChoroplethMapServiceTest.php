<?php

namespace Tests\Unit;

use App\Services\ChoroplethMapService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ChoroplethMapServiceTest extends TestCase
{
    private ChoroplethMapService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ChoroplethMapService;
    }

    #[Test]
    public function empty_collection_yields_no_map(): void
    {
        $result = $this->service->mapData(new Collection, 'toneladas');

        $this->assertFalse($result['hayMapa']);
        $this->assertSame([], $result['mapa']);
        $this->assertSame([], $result['filas']);
        $this->assertSame([], $result['buckets']);
    }

    #[Test]
    public function zones_without_geometry_are_listed_but_not_mapped(): void
    {
        $zonas = new Collection([
            $this->zona('Sin polígono', ['toneladas' => 50.0], geo: false),
        ]);

        $result = $this->service->mapData($zonas, 'toneladas');

        $this->assertFalse($result['hayMapa']);
        $this->assertSame([], $result['mapa']);
        $this->assertCount(1, $result['filas']);
        $this->assertFalse($result['filas'][0]['tiene_geometria']);
    }

    #[Test]
    public function buckets_split_positive_values_into_five_even_steps(): void
    {
        $result = $this->service->mapData($this->cincoZonas(), 'toneladas');

        $this->assertTrue($result['hayMapa']);
        $this->assertCount(5, $result['buckets']);
        $this->assertSame('#ffffb2', $result['buckets'][0]['color']);
        $this->assertSame('#bd0026', $result['buckets'][4]['color']);

        // min=10, max=50, step=8 → primer rango 10–18, último 42–50.
        $this->assertSame('10,00–18,00', $result['buckets'][0]['label']);
        $this->assertSame('42,00–50,00', $result['buckets'][4]['label']);
    }

    #[Test]
    public function map_polygons_are_sorted_desc_and_coloured_by_metric(): void
    {
        $mapa = $this->service->mapData($this->cincoZonas(), 'toneladas')['mapa'];

        $this->assertCount(5, $mapa);
        // Ordenado desc: la zona de 50 t va primero con el color más oscuro.
        $this->assertSame('Zona 5', $mapa[0]['nombre']);
        $this->assertSame('#bd0026', $mapa[0]['color']);
        $this->assertSame('Zona 1', $mapa[4]['nombre']);
        $this->assertSame('#ffffb2', $mapa[4]['color']);
        // El geojson se pasa tal cual para que Leaflet lo dibuje.
        $this->assertSame('FeatureCollection', $mapa[0]['geojson']['type']);
    }

    #[Test]
    public function ranking_rows_format_value_and_subtitle(): void
    {
        $filas = $this->service->mapData($this->cincoZonas(), 'toneladas')['filas'];

        $this->assertSame('Zona 5', $filas[0]['nombre']);
        $this->assertSame('50,00 t', $filas[0]['valor']);
        $this->assertSame('25 viajes · 50,00 t', $filas[0]['sub']);
        $this->assertSame('#bd0026', $filas[0]['color']);
    }

    #[Test]
    public function zone_with_geometry_but_no_activity_is_painted_grey(): void
    {
        $zonas = $this->cincoZonas()->push(
            $this->zona('Inactiva', ['toneladas' => 0.0], lngBase: -58.70)
        );

        $mapa = $this->service->mapData($zonas, 'toneladas')['mapa'];

        $inactiva = collect($mapa)->firstWhere('nombre', 'Inactiva');
        $this->assertNotNull($inactiva);
        $this->assertSame('#cbd5e1', $inactiva['color']);
    }

    #[Test]
    public function metric_without_positive_values_yields_no_map(): void
    {
        $zonas = new Collection([
            $this->zona('Centro', ['toneladas' => 50.0, 'per_capita' => null]),
        ]);

        $result = $this->service->mapData($zonas, 'per_capita');

        $this->assertFalse($result['hayMapa']);
        $this->assertSame([], $result['buckets']);
    }

    #[Test]
    public function metric_metadata_matches_the_requested_metric(): void
    {
        $result = $this->service->mapData($this->cincoZonas(), 'pesajes');

        $this->assertSame('Viajes', $result['metrica']['label']);
        $this->assertSame('viajes', $result['metrica']['unidad']);
        $this->assertSame(0, $result['metrica']['decimales']);
    }

    /**
     * Cinco zonas con toneladas 10, 20, 30, 40, 50 — una por cada paso de la rampa.
     */
    private function cincoZonas(): Collection
    {
        return new Collection([
            $this->zona('Zona 1', ['toneladas' => 10.0, 'pesajes' => 5], lngBase: -58.90),
            $this->zona('Zona 2', ['toneladas' => 20.0, 'pesajes' => 10], lngBase: -58.85),
            $this->zona('Zona 3', ['toneladas' => 30.0, 'pesajes' => 15], lngBase: -58.80),
            $this->zona('Zona 4', ['toneladas' => 40.0, 'pesajes' => 20], lngBase: -58.75),
            $this->zona('Zona 5', ['toneladas' => 50.0, 'pesajes' => 25], lngBase: -58.70),
        ]);
    }

    /**
     * Fila con la forma que devuelve DashboardService::metricasPorZona().
     *
     * @param  array<string, int|float|null>  $metricas
     * @return array<string, mixed>
     */
    private function zona(string $nombre, array $metricas, bool $geo = true, float $lngBase = -58.84): array
    {
        return [
            'id'              => crc32($nombre),
            'nombre'          => $nombre,
            'tiene_geometria' => $geo,
            'geojson'         => $geo ? [
                'type'     => 'FeatureCollection',
                'features' => [[
                    'type'       => 'Feature',
                    'properties' => [],
                    'geometry'   => [
                        'type'        => 'Polygon',
                        'coordinates' => [[
                            [$lngBase, -27.47],
                            [$lngBase + 0.02, -27.47],
                            [$lngBase + 0.02, -27.45],
                            [$lngBase, -27.45],
                            [$lngBase, -27.47],
                        ]],
                    ],
                ]],
            ] : null,
            'centro'     => null,
            'hectareas'  => null,
            'habitantes' => null,
            'metricas'   => array_merge(
                ['toneladas' => null, 'pesajes' => 0, 'per_capita' => null, 'densidad' => null],
                $metricas,
            ),
        ];
    }
}
