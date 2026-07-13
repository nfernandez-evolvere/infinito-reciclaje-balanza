<?php

namespace App\Services;

use App\Models\ReporteGenerado;
use App\Support\ReporteSecciones;
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

    // ── Reporte v2 (Excel por servicio / PDF institucional) ──────────────────
    //
    // El snapshot v2 lleva `version => 2` como discriminador (downloadHistorial lo
    // lee para enrutar la re-descarga a los generadores v2). Serializa solo las
    // claves del formato descargado: el camino Excel trae `datosV2` + `detalle`
    // aplanado; el camino PDF trae `semanas`/`diaSemana`/`porServicio`/`zonasServicio`.
    // Las fechas Carbon anidadas se codifican con un walk recursivo genérico.

    /**
     * @param  array<string, mixed>  $reporte
     * @return array<string, mixed>
     */
    public function capturarV2(array $reporte): array
    {
        $s = [
            'version'        => 2,
            'secciones'      => $reporte['secciones'] ?? null,
            'kpis'           => $reporte['kpis'] ?? [],
            'evolucion'      => $reporte['evolucion'] ?? ['datos' => [], 'promedio' => 0, 'maximo' => 0, 'minimo' => 0],
            'zonas'          => $this->aArray($reporte['zonas'] ?? []),
            'vehiculos'      => $this->aArray($reporte['vehiculos'] ?? []),
            'mapaZonas'      => $this->aArray($reporte['mapaZonas'] ?? []),
            'kg_netos_total' => (int) ($reporte['kg_netos_total'] ?? 0),
            'config'         => $this->serializarConfig($reporte['config'] ?? null),
            'conclusiones'   => $reporte['conclusiones'] ?? [],
        ];

        // Camino Excel: detalle aplanado + bloques del export v2.
        if (is_array($reporte['detalle'] ?? null)) {
            $s['detalle'] = $reporte['detalle'];
        }
        if (isset($reporte['datosV2'])) {
            $s['datosV2'] = $this->encodeFechas($reporte['datosV2']);
        }

        // Camino PDF: bloques del informe institucional.
        if (isset($reporte['semanas'])) {
            $s['semanas'] = $this->encodeFechas($reporte['semanas']);
        }
        if (array_key_exists('diaSemana', $reporte)) {
            $s['diaSemana'] = $reporte['diaSemana'];
        }
        if (array_key_exists('flotaActiva', $reporte)) {
            $s['flotaActiva'] = $reporte['flotaActiva'];
        }
        if (isset($reporte['porServicio'])) {
            $s['porServicio'] = $this->aArray($reporte['porServicio']);
        }
        if (isset($reporte['zonasServicio'])) {
            $s['zonasServicio'] = $this->encodeFechas($reporte['zonasServicio']);
        }

        return $s;
    }

    /**
     * @return array<string, mixed>
     */
    public function rehidratarV2(ReporteGenerado $generado): array
    {
        $s = $generado->snapshot ?? [];

        $reporte = [
            'desde'   => $generado->periodo_desde->copy()->startOfDay(),
            'hasta'   => $generado->periodo_hasta->copy()->endOfDay(),
            'filtros' => $generado->filtrosNormalizados(),
            // Secciones congeladas al generar: la re-descarga/reenvío reproduce el
            // documento idéntico aunque la configuración general cambie después.
            // Snapshots previos a la opción no traen la clave → todas (via sanitizar).
            'secciones'      => ReporteSecciones::sanitizar($s['secciones'] ?? null),
            'kpis'           => $s['kpis'] ?? [],
            'evolucion'      => $s['evolucion'] ?? ['datos' => [], 'promedio' => 0, 'maximo' => 0, 'minimo' => 0],
            'zonas'          => collect($s['zonas'] ?? []),
            'vehiculos'      => collect($s['vehiculos'] ?? []),
            'mapaZonas'      => collect($s['mapaZonas'] ?? []),
            'kg_netos_total' => (int) ($s['kg_netos_total'] ?? 0),
            'config'         => $this->rehidratarConfig($s['config'] ?? null),
            'conclusiones'   => $s['conclusiones'] ?? [],
            'detalle'        => $s['detalle'] ?? [],
        ];

        if (isset($s['datosV2'])) {
            $datos = $this->decodeFechas($s['datosV2']);
            // El export v2 usa datosV2['tipos'] como Collection (->count(), foreach).
            $datos['tipos'] = collect($datos['tipos'] ?? []);
            $reporte['datosV2'] = $datos;
        }
        if (isset($s['semanas'])) {
            $reporte['semanas'] = $this->decodeFechas($s['semanas']);
        }
        if (array_key_exists('diaSemana', $s)) {
            $reporte['diaSemana'] = $s['diaSemana'];
        }
        if (array_key_exists('flotaActiva', $s)) {
            $reporte['flotaActiva'] = $s['flotaActiva'];
        }
        if (isset($s['porServicio'])) {
            $reporte['porServicio'] = collect($s['porServicio']);
        }
        if (isset($s['zonasServicio'])) {
            $reporte['zonasServicio'] = $this->decodeFechas($s['zonasServicio']);
        }

        return $reporte;
    }

    /**
     * Codifica recursivamente los Carbon de una estructura a `['__c' => ISO8601]`,
     * aplanando Collections a array. El resto de escalares pasa tal cual.
     */
    private function encodeFechas(mixed $value): mixed
    {
        if ($value instanceof Carbon) {
            return ['__c' => $value->toIso8601String()];
        }
        if ($value instanceof Collection) {
            $value = $value->all();
        }
        if (is_array($value)) {
            return array_map(fn ($v) => $this->encodeFechas($v), $value);
        }

        return $value;
    }

    /** Inverso de encodeFechas(): reconstruye los Carbon desde `['__c' => ISO8601]`. */
    private function decodeFechas(mixed $value): mixed
    {
        if (is_array($value)) {
            if (array_keys($value) === ['__c']) {
                return Carbon::parse($value['__c']);
            }

            return array_map(fn ($v) => $this->decodeFechas($v), $value);
        }

        return $value;
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
