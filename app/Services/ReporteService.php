<?php

namespace App\Services;

use App\Repositories\PesajeRepository;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class ReporteService
{
    public function __construct(
        protected PesajeRepository $pesajeRepository,
    ) {}

    public function generar(Carbon $desde, Carbon $hasta, array $filtros = []): array
    {
        $pesajes = $this->pesajeRepository->paraReporte($desde, $hasta, $filtros);

        return [
            'desde'     => $desde,
            'hasta'     => $hasta,
            'filtros'   => $filtros,
            'kpis'      => $this->calcularKpis($pesajes, $desde, $hasta),
            'evolucion' => $this->calcularEvolucion($pesajes, $desde, $hasta),
            'zonas'     => $this->calcularPorZona($pesajes),
            'vehiculos' => $this->calcularPorVehiculo($pesajes),
            'detalle'   => $pesajes,
        ];
    }

    private function calcularKpis(Collection $pesajes, Carbon $desde, Carbon $hasta): array
    {
        $total = $pesajes->count();
        $kgTotal = $pesajes->sum('peso_neto_kg');
        $diasOp = $pesajes->groupBy(fn ($p) => $p->created_at->toDateString())->count();
        $diasRango = (int) $desde->diffInDays($hasta) + 1;

        return [
            'total'             => $total,
            'toneladas'         => round($kgTotal / 1000, 2),
            'dias_op'           => $diasOp,
            'dias_rango'        => $diasRango,
            'promedio_ton_dia'  => $diasOp > 0 ? round(($kgTotal / $diasOp) / 1000, 2) : 0,
            'promedio_kg_viaje' => $total > 0 ? (int) round($kgTotal / $total) : 0,
        ];
    }

    private function calcularEvolucion(Collection $pesajes, Carbon $desde, Carbon $hasta): array
    {
        $diasRango = (int) $desde->diffInDays($hasta) + 1;
        $formato = $diasRango <= 15 ? 'D d/m' : 'd/m';

        $porDia = $pesajes->groupBy(fn ($p) => $p->created_at->toDateString());

        $datos = collect(CarbonPeriod::create($desde, $hasta))
            ->map(fn (Carbon $dia) => [
                'fecha'     => $dia->translatedFormat($formato),
                'viajes'    => isset($porDia[$dia->toDateString()]) ? $porDia[$dia->toDateString()]->count() : 0,
                'toneladas' => isset($porDia[$dia->toDateString()])
                    ? round($porDia[$dia->toDateString()]->sum('peso_neto_kg') / 1000, 2)
                    : 0,
            ])
            ->values()
            ->all();

        $toneladasPorDia = collect($datos)->pluck('toneladas')->filter(fn ($t) => $t > 0);

        return [
            'datos'    => $datos,
            'promedio' => $toneladasPorDia->isNotEmpty() ? round($toneladasPorDia->avg(), 2) : 0,
            'maximo'   => $toneladasPorDia->isNotEmpty() ? $toneladasPorDia->max() : 0,
            'minimo'   => $toneladasPorDia->isNotEmpty() ? $toneladasPorDia->min() : 0,
        ];
    }

    private function calcularPorZona(Collection $pesajes): Collection
    {
        $total = $pesajes->sum('peso_neto_kg');

        $conZona = $pesajes->filter(fn ($p) => $p->zona_id !== null);

        return $conZona
            ->groupBy(fn ($p) => $p->zona_id.'|'.($p->turno ?? ''))
            ->map(function ($grupo) use ($total) {
                $count = $grupo->count();
                $sumaKg = $grupo->sum('peso_neto_kg');
                $zona = $grupo->first()->zona;
                $turno = $grupo->first()->turno;

                return [
                    'nombre'     => $zona?->nombre ?? '—',
                    'turno'      => $turno,
                    'viajes'     => $count,
                    'toneladas'  => round($sumaKg / 1000, 2),
                    'kg_viaje'   => (int) round($sumaKg / $count),
                    'porcentaje' => $total > 0 ? round(($sumaKg / $total) * 100, 1) : 0,
                    'kg_ha'      => ($zona?->hectareas > 0) ? round($sumaKg / $zona->hectareas, 1) : null,
                    'kg_hab'     => ($zona?->habitantes > 0) ? round($sumaKg / $zona->habitantes, 2) : null,
                ];
            })
            ->sortByDesc('toneladas')
            ->values();
    }

    // ── Pivots para el reporte municipal (Excel) ─────────────────────────────
    //
    // Estos métodos NO forman parte de generar(): son cálculos más pesados que
    // solo el export Excel necesita. Se computan en memoria sobre la misma
    // colección de pesajes (ya cargada con zona y vehiculo.tipoVehiculo), sin
    // queries adicionales. Mantenerlos fuera de generar() evita encarecer la
    // vista web y el PDF, y preserva la estructura que valida ReporteServiceTest.

    /**
     * Bloques cruzados que imitan la hoja "Dashboard" del reporte municipal:
     * desglose diario por tipo de vehículo, tabla zona × tipo y matriz zona × día.
     *
     * @return array{tipos: Collection, diario: array, zonaTipo: array, zonaDia: array}
     */
    public function pivotsParaExcel(Collection $pesajes, Carbon $desde, Carbon $hasta): array
    {
        $tipos = $this->tiposPresentes($pesajes);

        return [
            'tipos'    => $tipos,
            'diario'   => $this->calcularDiarioPorTipo($pesajes, $desde, $hasta, $tipos),
            'zonaTipo' => $this->calcularZonaTipo($pesajes, $tipos),
            'zonaDia'  => $this->calcularZonaDia($pesajes, $desde, $hasta),
        ];
    }

    /**
     * Tipos de vehículo presentes en el período, ordenados por kg netos desc.
     * Definen las columnas de los pivots cruzados (dinámicas, no hardcodeadas).
     *
     * @return Collection<int, array{id: int, nombre: string}>
     */
    private function tiposPresentes(Collection $pesajes): Collection
    {
        return $pesajes
            ->filter(fn ($p) => $p->vehiculo?->tipo_vehiculo_id !== null)
            ->groupBy(fn ($p) => $p->vehiculo->tipo_vehiculo_id)
            ->map(fn ($grupo) => [
                'id'     => (int) $grupo->first()->vehiculo->tipo_vehiculo_id,
                'nombre' => (string) ($grupo->first()->vehiculo->tipoVehiculo->nombre ?? '—'),
                'kg'     => $grupo->sum('peso_neto_kg'),
            ])
            ->sortByDesc('kg')
            ->values()
            ->map(fn ($t) => ['id' => $t['id'], 'nombre' => $t['nombre']]);
    }

    /**
     * Desglose diario con columnas Viajes/KG por cada tipo de vehículo, más las
     * filas de estadística TOTALES / PROMEDIO / MÁXIMO / MÍNIMO calculadas solo
     * sobre los días operativos (con al menos un viaje), columna por columna.
     */
    private function calcularDiarioPorTipo(Collection $pesajes, Carbon $desde, Carbon $hasta, Collection $tipos): array
    {
        $tipoIds = $tipos->pluck('id')->all();
        $porDia = $pesajes->groupBy(fn ($p) => $p->created_at->toDateString());

        $filas = collect(CarbonPeriod::create($desde, $hasta))
            ->map(function (Carbon $dia) use ($porDia, $tipoIds) {
                $delDia = $porDia[$dia->toDateString()] ?? collect();

                return [
                    'fecha'        => $dia->copy(),
                    'total_viajes' => $delDia->count(),
                    'total_kg'     => (int) $delDia->sum('peso_neto_kg'),
                    'tipos'        => $this->desglosarPorTipo($delDia, $tipoIds),
                ];
            })
            ->values();

        $operativos = $filas->filter(fn ($f) => $f['total_viajes'] > 0)->values();

        return [
            'filas'    => $filas->all(),
            'totales'  => $this->statsPorColumna($operativos, $tipoIds, 'sum'),
            'promedio' => $this->statsPorColumna($operativos, $tipoIds, 'avg'),
            'maximo'   => $this->statsPorColumna($operativos, $tipoIds, 'max'),
            'minimo'   => $this->statsPorColumna($operativos, $tipoIds, 'min'),
        ];
    }

    /**
     * Tabla zona × tipo: una fila por (zona, turno) con Viajes/KG por tipo,
     * totales de la fila y % del total general. Ordenada por kg netos desc.
     * El porcentaje se devuelve como fracción (0–1) para el formato 0.0% de Excel.
     */
    private function calcularZonaTipo(Collection $pesajes, Collection $tipos): array
    {
        $tipoIds = $tipos->pluck('id')->all();
        $granTotal = $pesajes->sum('peso_neto_kg');

        $filas = $pesajes
            ->filter(fn ($p) => $p->zona_id !== null)
            ->groupBy(fn ($p) => $p->zona_id.'|'.($p->turno ?? ''))
            ->map(function ($grupo) use ($tipoIds, $granTotal) {
                $totalKg = (int) $grupo->sum('peso_neto_kg');

                return [
                    'label'        => $this->etiquetaZona((string) ($grupo->first()->zona->nombre ?? '—'), $grupo->first()->turno),
                    'total_viajes' => $grupo->count(),
                    'total_kg'     => $totalKg,
                    'tipos'        => $this->desglosarPorTipo($grupo, $tipoIds),
                    'porcentaje'   => $granTotal > 0 ? $totalKg / $granTotal : 0.0,
                ];
            })
            ->sortByDesc('total_kg')
            ->values();

        return [
            'filas'   => $filas->all(),
            'totales' => $this->totalizarZonaTipo($filas, $tipoIds, $granTotal),
        ];
    }

    /**
     * Matriz zona × día: una fila por (zona, turno) con los kg netos de cada día
     * del período en columnas, más el total de la fila. Incluye la fila de
     * totales por día. Ordenada por total de kg desc.
     */
    private function calcularZonaDia(Collection $pesajes, Carbon $desde, Carbon $hasta): array
    {
        $fechas = collect(CarbonPeriod::create($desde, $hasta))->map(fn (Carbon $d) => $d->copy())->values();
        $claves = $fechas->map(fn (Carbon $d) => $d->toDateString())->all();

        $filas = $pesajes
            ->filter(fn ($p) => $p->zona_id !== null)
            ->groupBy(fn ($p) => $p->zona_id.'|'.($p->turno ?? ''))
            ->map(function ($grupo) use ($claves) {
                $porDia = $grupo->groupBy(fn ($p) => $p->created_at->toDateString());

                $dias = [];
                foreach ($claves as $k) {
                    $dias[$k] = isset($porDia[$k]) ? (int) $porDia[$k]->sum('peso_neto_kg') : 0;
                }

                return [
                    'label' => $this->etiquetaZona((string) ($grupo->first()->zona->nombre ?? '—'), $grupo->first()->turno),
                    'dias'  => $dias,
                    'total' => array_sum($dias),
                ];
            })
            ->sortByDesc('total')
            ->values();

        $totalesDia = [];
        foreach ($claves as $k) {
            $totalesDia[$k] = (int) $filas->sum(fn ($f) => $f['dias'][$k]);
        }

        return [
            'fechas'  => $fechas->all(),
            'filas'   => $filas->all(),
            'totales' => ['dias' => $totalesDia, 'total' => array_sum($totalesDia)],
        ];
    }

    /**
     * Viajes y kg netos de un grupo de pesajes, separados por tipo de vehículo.
     *
     * @param  array<int>  $tipoIds
     * @return array<int, array{viajes: int, kg: int}>
     */
    private function desglosarPorTipo(Collection $grupo, array $tipoIds): array
    {
        $porTipo = [];
        foreach ($tipoIds as $id) {
            // Cast explícito: el driver de SQL Server puede devolver la FK como string.
            $sub = $grupo->filter(fn ($p) => (int) $p->vehiculo?->tipo_vehiculo_id === $id);
            $porTipo[$id] = ['viajes' => $sub->count(), 'kg' => (int) $sub->sum('peso_neto_kg')];
        }

        return $porTipo;
    }

    /**
     * Agrega una columna a la vez (total y cada tipo) sobre los días operativos,
     * según la operación pedida. Para max/min cada columna se evalúa de forma
     * independiente: el máximo de una columna puede provenir de un día distinto
     * al de otra, tal como el reporte de referencia.
     *
     * @param  array<int>  $tipoIds
     */
    private function statsPorColumna(Collection $operativos, array $tipoIds, string $op): array
    {
        $agg = function (Collection $valores) use ($op): int {
            if ($valores->isEmpty()) {
                return 0;
            }

            return (int) match ($op) {
                'sum'   => $valores->sum(),
                'avg'   => round($valores->avg()),
                'max'   => $valores->max(),
                'min'   => $valores->min(),
                default => throw new \InvalidArgumentException("Operación de agregación no soportada: {$op}."),
            };
        };

        $tipos = [];
        foreach ($tipoIds as $id) {
            $tipos[$id] = [
                'viajes' => $agg($operativos->map(fn ($f) => $f['tipos'][$id]['viajes'])),
                'kg'     => $agg($operativos->map(fn ($f) => $f['tipos'][$id]['kg'])),
            ];
        }

        return [
            'total_viajes' => $agg($operativos->map(fn ($f) => $f['total_viajes'])),
            'total_kg'     => $agg($operativos->map(fn ($f) => $f['total_kg'])),
            'tipos'        => $tipos,
        ];
    }

    /**
     * Suma las filas de la tabla zona × tipo para la fila TOTALES.
     *
     * @param  array<int>  $tipoIds
     */
    private function totalizarZonaTipo(Collection $filas, array $tipoIds, int|float $granTotal): array
    {
        $tipos = [];
        foreach ($tipoIds as $id) {
            $tipos[$id] = [
                'viajes' => (int) $filas->sum(fn ($f) => $f['tipos'][$id]['viajes']),
                'kg'     => (int) $filas->sum(fn ($f) => $f['tipos'][$id]['kg']),
            ];
        }

        $totalKg = (int) $filas->sum('total_kg');

        return [
            'total_viajes' => (int) $filas->sum('total_viajes'),
            'total_kg'     => $totalKg,
            'tipos'        => $tipos,
            'porcentaje'   => $granTotal > 0 ? $totalKg / $granTotal : 0.0,
        ];
    }

    /** Etiqueta de fila "ZONA 1 DIURNA": nombre de zona + turno en mayúsculas. */
    private function etiquetaZona(string $nombre, ?string $turno): string
    {
        return $turno ? $nombre.' '.mb_strtoupper($turno) : $nombre;
    }

    private function calcularPorVehiculo(Collection $pesajes): Collection
    {
        $total = $pesajes->sum('peso_neto_kg');

        return $pesajes
            ->filter(fn ($p) => $p->vehiculo?->tipo_vehiculo_id !== null)
            ->groupBy(fn ($p) => $p->vehiculo->tipo_vehiculo_id)
            ->map(function ($grupo) use ($total) {
                $count = $grupo->count();
                $sumaKg = $grupo->sum('peso_neto_kg');

                return [
                    'nombre'     => $grupo->first()->vehiculo->tipoVehiculo?->nombre ?? '—',
                    'viajes'     => $count,
                    'toneladas'  => round($sumaKg / 1000, 2),
                    'kg_viaje'   => (int) round($sumaKg / $count),
                    'porcentaje' => $total > 0 ? round(($sumaKg / $total) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('toneladas')
            ->values();
    }
}
