<?php

namespace App\Services;

use Illuminate\Support\Collection;

/**
 * Prepara los datos del mapa de calor (choropleth) para el PDF.
 *
 * El PDF se renderiza con Browsershot (Chromium real), así que el mapa Leaflet
 * con tiles de OpenStreetMap se dibuja en el cliente igual que en la web. Este
 * servicio solo calcula, server-side, lo que el mapa y la tabla de ranking
 * necesitan: el color de cada zona según la métrica (misma rampa YlOrRd y misma
 * lógica de buckets que resources/js/alpine/admin/mapa-calor.js), los buckets de
 * la leyenda y las filas del ranking ya ordenadas.
 */
class ChoroplethMapService
{
    /** Rampa de calor YlOrRd (5 pasos) — claro = poco, oscuro = mucho. */
    private const RAMP = ['#ffffb2', '#fecc5c', '#fd8d3c', '#f03b20', '#bd0026'];

    /** Zona con geometría pero sin actividad en el rango (slate-300). */
    private const SIN_DATOS = '#cbd5e1';

    /**
     * Las 4 métricas del mapa de calor, en el mismo orden que la web.
     *
     * @var array<string, array{label: string, unidad: string, decimales: int}>
     */
    public const METRICAS = [
        'toneladas'  => ['label' => 'Toneladas',  'unidad' => 't',      'decimales' => 2],
        'pesajes'    => ['label' => 'Viajes',     'unidad' => 'viajes', 'decimales' => 0],
        'per_capita' => ['label' => 'Per cápita', 'unidad' => 'kg/hab', 'decimales' => 2],
        'densidad'   => ['label' => 'Densidad',   'unidad' => 'kg/ha',  'decimales' => 1],
    ];

    /**
     * Datos del mapa de una métrica: leyenda, polígonos coloreados para Leaflet y
     * filas del ranking (todas las zonas, ordenadas por la métrica desc).
     *
     * @param  Collection  $zonas  filas de DashboardService::metricasPorZona()
     * @return array{
     *     hayMapa: bool,
     *     metrica: array{label: string, unidad: string, decimales: int},
     *     buckets: array<int, array{color: string, label: string}>,
     *     mapa: array<int, array{nombre: string, geojson: mixed, color: string}>,
     *     filas: array<int, array{nombre: string, tiene_geometria: bool, color: string, valor: string, sub: string}>
     * }
     */
    public function mapData(Collection $zonas, string $metric): array
    {
        $meta = self::METRICAS[$metric] ?? self::METRICAS['toneladas'];

        $conGeo = $zonas->filter(fn ($z) => ($z['tiene_geometria'] ?? false) && ! empty($z['geojson']));

        [$min, $max, $buckets] = $this->computeBuckets($conGeo, $metric, $meta['decimales']);

        $ordenadas = $zonas
            ->sortByDesc(fn ($z) => $z['metricas'][$metric] ?? -1)
            ->values();

        $mapa = $ordenadas
            ->filter(fn ($z) => ($z['tiene_geometria'] ?? false) && ! empty($z['geojson']))
            ->map(fn ($z) => [
                'nombre'  => (string) $z['nombre'],
                'geojson' => $z['geojson'],
                'color'   => $this->colorFor($z['metricas'][$metric] ?? null, $min, $max),
            ])
            ->values()
            ->all();

        $filas = $ordenadas->map(function ($z) use ($metric, $min, $max, $meta) {
            $valor = $z['metricas'][$metric] ?? null;
            $pesajes = (int) ($z['metricas']['pesajes'] ?? 0);
            $toneladas = (float) ($z['metricas']['toneladas'] ?? 0);

            return [
                'nombre'          => (string) $z['nombre'],
                'tiene_geometria' => (bool) ($z['tiene_geometria'] ?? false),
                'color'           => $this->colorFor($valor, $min, $max),
                'valor'           => $valor === null ? '—' : $this->fmt((float) $valor, $meta['decimales']).' '.$meta['unidad'],
                'sub'             => $pesajes.' viajes · '.$this->fmt($toneladas, 2).' t',
            ];
        })->all();

        return [
            'hayMapa' => $conGeo->isNotEmpty() && $buckets !== [],
            'metrica' => $meta,
            'buckets' => $buckets,
            'mapa'    => $mapa,
            'filas'   => $filas,
        ];
    }

    /**
     * Min/max sobre los valores positivos y la leyenda de 5 buckets equiespaciados.
     * Misma lógica que computeBuckets() del componente Alpine.
     *
     * @return array{0: float, 1: float, 2: array<int, array{color: string, label: string}>}
     */
    private function computeBuckets(Collection $conGeo, string $metric, int $dec): array
    {
        $valores = $conGeo
            ->map(fn ($z) => $z['metricas'][$metric] ?? null)
            ->filter(fn ($v) => $v !== null && $v > 0)
            ->map(fn ($v) => (float) $v)
            ->values();

        if ($valores->isEmpty()) {
            return [0.0, 0.0, []];
        }

        $min = (float) $valores->min();
        $max = (float) $valores->max();

        if ($min === $max) {
            return [$min, $max, [[
                'color' => self::RAMP[count(self::RAMP) - 1],
                'label' => $this->fmt($min, $dec),
            ]]];
        }

        $pasos = count(self::RAMP);
        $step = ($max - $min) / $pasos;

        $buckets = [];
        foreach (self::RAMP as $i => $color) {
            $lo = $min + $step * $i;
            $hi = $i === $pasos - 1 ? $max : $min + $step * ($i + 1);
            $buckets[] = ['color' => $color, 'label' => $this->fmt($lo, $dec).'–'.$this->fmt($hi, $dec)];
        }

        return [$min, $max, $buckets];
    }

    /**
     * Color del polígono para un valor: gris si no hay actividad, si no el bucket
     * de la rampa. Idéntico a colorFor() del componente Alpine.
     */
    private function colorFor(mixed $value, float $min, float $max): string
    {
        if ($value === null || (float) $value <= 0) {
            return self::SIN_DATOS;
        }

        $pasos = count(self::RAMP);

        if ($max === $min) {
            return self::RAMP[$pasos - 1];
        }

        $step = ($max - $min) / $pasos;
        $idx = (int) floor(((float) $value - $min) / $step);

        return self::RAMP[max(0, min($idx, $pasos - 1))];
    }

    private function fmt(float $value, int $dec): string
    {
        return number_format($value, $dec, ',', '.');
    }
}
