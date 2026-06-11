<?php

namespace App\Services;

use App\Models\ReporteGenerado;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Congela y reconstruye el contenido de un reporte. `capturar()` toma el array
 * `$reporte` en vivo (con modelos Eloquent y objetos Carbon) y devuelve un array
 * 100% serializable a JSON para guardarlo en `reportes_generados.snapshot`.
 * `rehidratar()` hace el camino inverso: rearma el `$reporte` que consumen la
 * vista PDF y el export Excel, sin tocar los pesajes vivos.
 *
 * Es format-agnóstico: serializa solo las claves presentes (un PDF no trae
 * pivots/detalle; un Excel no trae mapa de calor) y al rehidratar completa las
 * ausentes con defaults vacíos, de modo que la vista/Excel nunca rompan.
 */
class ReporteSnapshotService
{
    /**
     * @param  array<string, mixed>  $reporte
     * @return array<string, mixed>
     */
    public function capturar(array $reporte): array
    {
        $snapshot = [
            'kpis'           => $reporte['kpis'] ?? [],
            'evolucion'      => $reporte['evolucion'] ?? ['datos' => [], 'promedio' => 0, 'maximo' => 0, 'minimo' => 0],
            'zonas'          => $this->aArray($reporte['zonas'] ?? []),
            'vehiculos'      => $this->aArray($reporte['vehiculos'] ?? []),
            'mapaZonas'      => $this->aArray($reporte['mapaZonas'] ?? []),
            'kg_netos_total' => (int) ($reporte['kg_netos_total'] ?? 0),
            'config'         => $this->serializarConfig($reporte['config'] ?? null),
            'conclusiones'   => $reporte['conclusiones'] ?? [],
        ];

        // El detalle solo se congela si ya viene aplanado (camino Excel). En el
        // camino PDF queda como Collection de modelos y no se guarda.
        if (is_array($reporte['detalle'] ?? null)) {
            $snapshot['detalle'] = $reporte['detalle'];
        }

        if (isset($reporte['pivots'])) {
            $snapshot['pivots'] = $this->serializarPivots($reporte['pivots']);
        }

        if (isset($reporte['alertas'])) {
            $snapshot['alertas'] = $this->serializarAlertas($reporte['alertas']);
        }

        return $snapshot;
    }

    /**
     * @return array<string, mixed>
     */
    public function rehidratar(ReporteGenerado $generado): array
    {
        $s = $generado->snapshot ?? [];

        $reporte = [
            'desde'          => $generado->periodo_desde->copy()->startOfDay(),
            'hasta'          => $generado->periodo_hasta->copy()->endOfDay(),
            'filtros'        => $generado->filtrosNormalizados(),
            'kpis'           => $s['kpis'] ?? [],
            'evolucion'      => $s['evolucion'] ?? ['datos' => [], 'promedio' => 0, 'maximo' => 0, 'minimo' => 0],
            'zonas'          => collect($s['zonas'] ?? []),
            'vehiculos'      => collect($s['vehiculos'] ?? []),
            'mapaZonas'      => collect($s['mapaZonas'] ?? []),
            'detalle'        => $s['detalle'] ?? [],
            'kg_netos_total' => (int) ($s['kg_netos_total'] ?? 0),
            'config'         => $this->rehidratarConfig($s['config'] ?? null),
            'conclusiones'   => $s['conclusiones'] ?? [],
        ];

        if (isset($s['pivots'])) {
            $reporte['pivots'] = $this->rehidratarPivots($s['pivots']);
        }

        if (isset($s['alertas'])) {
            $reporte['alertas'] = $this->rehidratarAlertas($s['alertas']);
        }

        return $reporte;
    }

    // ── Serialización (vivo → JSON) ──────────────────────────────────────────

    /**
     * @param  Collection<int, mixed>|array<mixed>  $value
     * @return array<mixed>
     */
    private function aArray($value): array
    {
        return $value instanceof Collection ? $value->toArray() : (array) $value;
    }

    /**
     * Solo los campos de marca que se imprimen (nunca la API key de IA).
     *
     * @return array<string, mixed>|null
     */
    private function serializarConfig($config): ?array
    {
        if ($config === null) {
            return null;
        }

        return [
            'municipalidad_nombre' => $config->municipalidad_nombre,
            'intro_empresa'        => $config->intro_empresa,
            'servicios'            => $config->servicios,
        ];
    }

    /**
     * Las fechas Carbon dentro de los pivots se guardan como ISO 8601; la
     * Collection `tipos` se aplana a array.
     *
     * @param  array<string, mixed>  $pivots
     * @return array<string, mixed>
     */
    private function serializarPivots(array $pivots): array
    {
        $pivots['tipos'] = $this->aArray($pivots['tipos'] ?? []);

        if (isset($pivots['diario']['filas'])) {
            foreach ($pivots['diario']['filas'] as &$fila) {
                if (($fila['fecha'] ?? null) instanceof Carbon) {
                    $fila['fecha'] = $fila['fecha']->toIso8601String();
                }
            }
            unset($fila);
        }

        if (isset($pivots['zonaDia']['fechas'])) {
            $pivots['zonaDia']['fechas'] = array_map(
                fn ($d) => $d instanceof Carbon ? $d->toIso8601String() : $d,
                $pivots['zonaDia']['fechas'],
            );
        }

        return $pivots;
    }

    /**
     * @param  Collection<int, mixed>|iterable<mixed>  $alertas
     * @return list<array<string, mixed>>
     */
    private function serializarAlertas($alertas): array
    {
        return collect($alertas)->map(fn ($a) => [
            'tipo'            => $a->tipo,
            'titulo'          => $a->titulo,
            'descripcion'     => $a->descripcion,
            'fecha_deteccion' => $a->fecha_deteccion?->toIso8601String(),
            'leida'           => (bool) $a->leida,
            'zona_nombre'     => $a->zona?->nombre,
        ])->all();
    }

    // ── Rehidratación (JSON → consumible por vista/Excel) ────────────────────

    private function rehidratarConfig(?array $config): ?object
    {
        return $config === null ? null : (object) $config;
    }

    /**
     * @param  array<string, mixed>  $pivots
     * @return array<string, mixed>
     */
    private function rehidratarPivots(array $pivots): array
    {
        $pivots['tipos'] = collect($pivots['tipos'] ?? []);

        if (isset($pivots['diario']['filas'])) {
            foreach ($pivots['diario']['filas'] as &$fila) {
                if (isset($fila['fecha'])) {
                    $fila['fecha'] = Carbon::parse($fila['fecha']);
                }
            }
            unset($fila);
        }

        if (isset($pivots['zonaDia']['fechas'])) {
            $pivots['zonaDia']['fechas'] = array_map(
                fn ($d) => Carbon::parse($d),
                $pivots['zonaDia']['fechas'],
            );
        }

        return $pivots;
    }

    /**
     * @param  list<array<string, mixed>>  $alertas
     * @return Collection<int, object>
     */
    private function rehidratarAlertas(array $alertas): Collection
    {
        return collect($alertas)->map(fn (array $a) => $this->objetoAlerta($a));
    }

    /**
     * Una alerta rehidratada como objeto ligero (la vista accede por propiedad:
     * `$alerta->fecha_deteccion->format(...)`, `$alerta->zona->nombre`).
     *
     * @param  array<string, mixed>  $a
     */
    private function objetoAlerta(array $a): object
    {
        return (object) [
            'tipo'            => $a['tipo'] ?? null,
            'titulo'          => $a['titulo'] ?? null,
            'descripcion'     => $a['descripcion'] ?? null,
            'fecha_deteccion' => isset($a['fecha_deteccion']) ? Carbon::parse($a['fecha_deteccion']) : null,
            'leida'           => (bool) ($a['leida'] ?? false),
            'zona'            => ! empty($a['zona_nombre']) ? (object) ['nombre' => $a['zona_nombre']] : null,
        ];
    }
}
