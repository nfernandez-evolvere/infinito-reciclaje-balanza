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
                    'nombre' => $zona?->nombre ?? '—',
                    'turno'  => $turno,
                    // Clasificamos cada zona por su servicio (Zona pertenece a un
                    // TipoServicio): el desglose por zona respeta la jerarquía
                    // servicio → zona en la tabla del informe.
                    'tipo_servicio_id' => $zona?->tipo_servicio_id,
                    'tipo_servicio'    => $zona?->tipoServicio?->nombre ?? 'Sin servicio',
                    'viajes'           => $count,
                    'toneladas'        => round($sumaKg / 1000, 2),
                    'kg_viaje'         => (int) round($sumaKg / $count),
                    'porcentaje'       => $total > 0 ? round(($sumaKg / $total) * 100, 1) : 0,
                    'kg_ha'            => ($zona?->hectareas > 0) ? round($sumaKg / $zona->hectareas, 1) : null,
                    'kg_hab'           => ($zona?->habitantes > 0) ? round($sumaKg / $zona->habitantes, 2) : null,
                ];
            })
            ->sortByDesc('toneladas')
            ->values();
    }

    /**
     * Aplana los pesajes a las filas de la hoja "Base de datos" del Excel: solo
     * escalares (fechas/horas ya formateadas, nombres de relaciones resueltos),
     * sin modelos Eloquent. Es la forma que consume ReporteExcelExportV2 y la
     * que se congela en el snapshot del historial — así la hoja preserva la
     * tara y el neto del momento, aunque luego se recalcule la tara del vehículo.
     *
     * @return list<array<string, mixed>>
     */
    public function detalleParaExcel(Collection $pesajes): array
    {
        return $pesajes->map(fn ($p) => [
            'fecha'         => $p->created_at->format('d/m/Y'),
            'hora'          => $p->created_at->format('H:i'),
            'patente'       => $p->vehiculo?->patente ?? '—',
            'tipo_vehiculo' => $p->vehiculo?->tipoVehiculo?->nombre ?? '—',
            'tipo_servicio' => $p->tipoServicio?->nombre ?? '—',
            'zona'          => $p->zona?->nombre ?? '—',
            'turno'         => $p->turno ?? '—',
            'operador'      => $p->operador?->name ?? '—',
            'peso_bruto_kg' => (int) $p->peso_bruto_kg,
            'peso_tara_kg'  => (int) $p->peso_tara_kg,
            'peso_neto_kg'  => (int) $p->peso_neto_kg,
            'estado'        => $p->estado,
            'editado'       => (bool) $p->editado,
            'alerta_peso'   => (bool) $p->alerta_peso,
        ])->all();
    }

    // ── Cálculos compartidos por los pivots de Excel v2 ──────────────────────
    //
    // Estos métodos NO forman parte de generar(): son cálculos más pesados que
    // solo el export Excel v2 necesita (datosExcelV2, zonasPorServicio). Se
    // computan en memoria sobre la misma colección de pesajes (ya cargada con
    // zona y vehiculo.tipoVehiculo), sin queries adicionales. Mantenerlos fuera
    // de generar() evita encarecer la vista web y el PDF.

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

    /** Etiqueta de fila "ZONA 1 DIURNA": nombre de zona + turno en mayúsculas. */
    private function etiquetaZona(string $nombre, ?string $turno): string
    {
        return $turno ? $nombre.' '.mb_strtoupper($turno) : $nombre;
    }

    // ── Reporte v2 — Excel por servicio / N° interno ─────────────────────────
    //
    // Cálculos en memoria sobre la misma colección de pesajes ya cargada (con
    // zona, tipoServicio y vehiculo.tipoVehiculo), sin queries adicionales.
    // datosExcelV2() es el orquestador que consume el export v2; porServicio()
    // se expone aparte porque también lo usa el PDF v2.

    /**
     * Reparto por tipo de servicio, clasificado por pesaje.tipo_servicio_id (regla
     * canónica: cada total de servicio es la suma de sus zonas). Viajes, kg netos,
     * toneladas, % del total, más la descripción del servicio y la cantidad de zonas
     * distintas con actividad (para la página "¿Qué es cada servicio?" del PDF).
     * Ordenado por kg desc.
     *
     * @return Collection<int, array{tipo_servicio_id: int, nombre: string, descripcion: ?string, zonas: int, viajes: int, kg: int, toneladas: float, porcentaje: float}>
     */
    public function porServicio(Collection $pesajes): Collection
    {
        $total = $pesajes->sum('peso_neto_kg');

        return $pesajes
            ->filter(fn ($p) => $p->tipo_servicio_id !== null)
            ->groupBy(fn ($p) => $p->tipo_servicio_id)
            ->map(function ($grupo) use ($total) {
                $kg = (int) $grupo->sum('peso_neto_kg');

                return [
                    'tipo_servicio_id' => (int) $grupo->first()->tipo_servicio_id,
                    'nombre'           => (string) ($grupo->first()->tipoServicio?->nombre ?? '—'),
                    'descripcion'      => $grupo->first()->tipoServicio?->descripcion,
                    'zonas'            => $grupo->filter(fn ($p) => $p->zona_id !== null)->pluck('zona_id')->unique()->count(),
                    'viajes'           => $grupo->count(),
                    'kg'               => $kg,
                    'toneladas'        => round($kg / 1000, 2),
                    'porcentaje'       => $total > 0 ? round(($kg / $total) * 100, 1) : 0.0,
                ];
            })
            ->sortByDesc('kg')
            ->values();
    }

    /**
     * Ingresos agrupados por semana del período (para "¿Cuánto ingresa por semana?").
     * Ventanas de 7 días desde `$desde`; una ventana final más corta que 7 días se
     * fusiona con la anterior (así un mes de 31 días da 4 semanas: 1-7, 8-14, 15-21,
     * 22-31, como el reporte de referencia).
     *
     * @return list<array{numero: int, desde: Carbon, hasta: Carbon, kg: int, viajes: int}>
     */
    public function porSemana(Collection $pesajes, Carbon $desde, Carbon $hasta): array
    {
        $porDia = $pesajes->groupBy(fn ($p) => $p->created_at->toDateString());

        // Ventanas [inicio, fin] de 7 días desde desde, la última recortada a hasta.
        $ventanas = [];
        $cursor = $desde->copy()->startOfDay();
        while ($cursor->lte($hasta)) {
            $fin = $cursor->copy()->addDays(6);
            if ($fin->gt($hasta)) {
                $fin = $hasta->copy()->startOfDay();
            }
            $ventanas[] = [$cursor->copy(), $fin->copy()];
            $cursor = $fin->copy()->addDay();
        }

        // Fusionar una última ventana corta (< 7 días) con la anterior.
        $n = count($ventanas);
        if ($n > 1 && $ventanas[$n - 1][0]->diffInDays($ventanas[$n - 1][1]) + 1 < 7) {
            $ventanas[$n - 2][1] = $ventanas[$n - 1][1];
            array_pop($ventanas);
        }

        $semanas = [];
        foreach ($ventanas as $i => [$ini, $fin]) {
            $kg = 0;
            $viajes = 0;
            for ($d = $ini->copy(); $d->lte($fin); $d->addDay()) {
                $grupo = $porDia[$d->toDateString()] ?? null;
                if ($grupo) {
                    $kg += (int) $grupo->sum('peso_neto_kg');
                    $viajes += $grupo->count();
                }
            }
            $semanas[] = [
                'numero' => $i + 1,
                'desde'  => $ini,
                'hasta'  => $fin,
                'kg'     => $kg,
                'viajes' => $viajes,
            ];
        }

        return $semanas;
    }

    /**
     * Ingresos acumulados por día de la semana (Lunes→Domingo) sobre todo el período
     * (para "Recolección según los días de la semana"). Siempre devuelve los 7 días,
     * incluidos los que no tuvieron actividad.
     *
     * @return list<array{dia: string, kg: int, viajes: int}>
     */
    public function porDiaSemana(Collection $pesajes): array
    {
        $nombres = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];

        $acum = [];
        foreach (array_keys($nombres) as $iso) {
            $acum[$iso] = ['kg' => 0, 'viajes' => 0];
        }

        foreach ($pesajes as $p) {
            $iso = $p->created_at->dayOfWeekIso;   // 1 (Lunes) … 7 (Domingo)
            $acum[$iso]['kg'] += (int) $p->peso_neto_kg;
            $acum[$iso]['viajes']++;
        }

        return collect($nombres)
            ->map(fn ($nombre, $iso) => [
                'dia'    => $nombre,
                'kg'     => $acum[$iso]['kg'],
                'viajes' => $acum[$iso]['viajes'],
            ])
            ->values()
            ->all();
    }

    /**
     * Cantidad de vehículos distintos que operaron en el período (flota activa;
     * para el bloque "Flota activa" del resumen operativo del PDF).
     */
    public function vehiculosOperativos(Collection $pesajes): int
    {
        return $pesajes
            ->filter(fn ($p) => $p->vehiculo_id !== null)
            ->pluck('vehiculo_id')
            ->unique()
            ->count();
    }

    /**
     * Bloques que consume ReporteExcelExportV2. Computa los tipos presentes una vez
     * y los reusa en el cruce servicio×tipo y en el desglose diario.
     *
     * @return array{tipos: Collection, resumenPorDia: array, porServicio: Collection, servicioTipoVehiculo: array, diario: array, porNumeroInterno: array, servicios: list<array>}
     */
    public function datosExcelV2(Collection $pesajes, Carbon $desde, Carbon $hasta): array
    {
        $tipos = $this->tiposPresentes($pesajes);
        $fechas = collect(CarbonPeriod::create($desde, $hasta))->map(fn (Carbon $d) => $d->copy())->values();

        return [
            'tipos'                => $tipos,
            'fechas'               => $fechas->all(),
            'resumenPorDia'        => $this->resumenPorDia($pesajes, $desde, $hasta),
            'porServicio'          => $this->porServicio($pesajes),
            'servicioTipoVehiculo' => $this->servicioPorTipoVehiculo($pesajes, $tipos),
            'diario'               => $this->calcularDiarioPorTipo($pesajes, $desde, $hasta, $tipos),
            'porNumeroInterno'     => $this->porNumeroInterno($pesajes, $fechas),
            'servicios'            => $this->zonasPorServicio($pesajes, $desde, $hasta),
        ];
    }

    /**
     * Resumen por día para la hoja "Resumen": una fila por día del período con el
     * nombre del día, kg netos y viajes, más los totales. Incluye los días sin
     * actividad (kg/viajes en 0) para que el calendario del mes quede completo.
     *
     * @return array{filas: list<array{fecha: Carbon, dia: string, kg: int, viajes: int}>, total_kg: int, total_viajes: int}
     */
    private function resumenPorDia(Collection $pesajes, Carbon $desde, Carbon $hasta): array
    {
        $porDia = $pesajes->groupBy(fn ($p) => $p->created_at->toDateString());

        $filas = collect(CarbonPeriod::create($desde, $hasta))
            ->map(function (Carbon $dia) use ($porDia) {
                $del = $porDia[$dia->toDateString()] ?? collect();

                return [
                    'fecha'  => $dia->copy(),
                    'dia'    => $dia->translatedFormat('l'),
                    'kg'     => (int) $del->sum('peso_neto_kg'),
                    'viajes' => $del->count(),
                ];
            })
            ->values();

        return [
            'filas'        => $filas->all(),
            'total_kg'     => (int) $filas->sum('kg'),
            'total_viajes' => (int) $filas->sum('viajes'),
        ];
    }

    /**
     * Cruce servicio × tipo de vehículo: por cada servicio, viajes y kg de cada tipo
     * presente y el total de fila; más la fila TOTAL. Cubre los dos bloques del Excel
     * (kg y viajes). Filas ordenadas por kg desc.
     *
     * @param  Collection<int, array{id: int, nombre: string}>  $tipos
     * @return array{filas: list<array{nombre: string, total_viajes: int, total_kg: int, tipos: array}>, totales: array{nombre: string, total_viajes: int, total_kg: int, tipos: array}}
     */
    private function servicioPorTipoVehiculo(Collection $pesajes, Collection $tipos): array
    {
        $tipoIds = $tipos->pluck('id')->all();

        $filas = $pesajes
            ->filter(fn ($p) => $p->tipo_servicio_id !== null)
            ->groupBy(fn ($p) => $p->tipo_servicio_id)
            ->map(fn ($grupo) => [
                'nombre'       => (string) ($grupo->first()->tipoServicio?->nombre ?? '—'),
                'total_viajes' => $grupo->count(),
                'total_kg'     => (int) $grupo->sum('peso_neto_kg'),
                'tipos'        => $this->desglosarPorTipo($grupo, $tipoIds),
            ])
            ->sortByDesc('total_kg')
            ->values();

        $totTipos = [];
        foreach ($tipoIds as $id) {
            $totTipos[$id] = [
                'viajes' => (int) $filas->sum(fn ($f) => $f['tipos'][$id]['viajes']),
                'kg'     => (int) $filas->sum(fn ($f) => $f['tipos'][$id]['kg']),
            ];
        }

        return [
            'filas'   => $filas->all(),
            'totales' => [
                'nombre'       => 'TOTAL',
                'total_viajes' => (int) $filas->sum('total_viajes'),
                'total_kg'     => (int) $filas->sum('total_kg'),
                'tipos'        => $totTipos,
            ],
        ];
    }

    /**
     * Matriz N° interno × día: una fila por vehículo con la cantidad de viajes que
     * hizo cada día del período, su tipo y el total. El vehículo sin número interno
     * (algunos particulares) cae a la patente. Ordenado por total de viajes desc.
     *
     * @param  Collection<int, Carbon>  $fechas
     * @return array{filas: list<array{interno: string, tipo: string, dias: array<string, int>, total: int}>}
     */
    private function porNumeroInterno(Collection $pesajes, Collection $fechas): array
    {
        $claves = $fechas->map(fn (Carbon $d) => $d->toDateString())->all();

        $filas = $pesajes
            ->filter(fn ($p) => $p->vehiculo !== null)
            ->groupBy(fn ($p) => $p->vehiculo_id)
            ->map(function ($grupo) use ($claves) {
                $vehiculo = $grupo->first()->vehiculo;
                $porDia = $grupo->groupBy(fn ($p) => $p->created_at->toDateString());

                $dias = [];
                foreach ($claves as $k) {
                    $dias[$k] = isset($porDia[$k]) ? $porDia[$k]->count() : 0;
                }

                return [
                    'interno' => (string) ($vehiculo->numero_interno ?: $vehiculo->patente ?: '—'),
                    'tipo'    => (string) ($vehiculo->tipoVehiculo?->nombre ?? '—'),
                    'dias'    => $dias,
                    'total'   => $grupo->count(),
                ];
            })
            ->sortByDesc('total')
            ->values();

        return ['filas' => $filas->all()];
    }

    /**
     * Una entrada por servicio presente (clasificado por pesaje.tipo_servicio_id),
     * con su resumen (viajes, kg), el desglose por zona+turno (% relativo al servicio)
     * y la matriz zona × día acotada al servicio. Ordenado por kg desc; alimenta las
     * hojas por servicio del Excel y las páginas de zonas del PDF.
     *
     * @return list<array{tipo_servicio_id: int, nombre: string, viajes: int, kg: int, zonas: list<array>, zonaDia: array}>
     */
    public function zonasPorServicio(Collection $pesajes, Carbon $desde, Carbon $hasta): array
    {
        return $pesajes
            ->filter(fn ($p) => $p->tipo_servicio_id !== null)
            ->groupBy(fn ($p) => $p->tipo_servicio_id)
            ->map(function ($grupo) use ($desde, $hasta) {
                $totalKg = (int) $grupo->sum('peso_neto_kg');

                $zonas = $grupo
                    ->filter(fn ($p) => $p->zona_id !== null)
                    ->groupBy(fn ($p) => $p->zona_id.'|'.($p->turno ?? ''))
                    ->map(function ($sub) use ($totalKg) {
                        $kg = (int) $sub->sum('peso_neto_kg');

                        return [
                            'label'  => $this->etiquetaZona((string) ($sub->first()->zona->nombre ?? '—'), $sub->first()->turno),
                            'viajes' => $sub->count(),
                            'kg'     => $kg,
                            // Fracción (0–1) para el formato 0.0% de Excel.
                            'porcentaje' => $totalKg > 0 ? $kg / $totalKg : 0.0,
                        ];
                    })
                    ->sortByDesc('kg')
                    ->values();

                return [
                    'tipo_servicio_id' => (int) $grupo->first()->tipo_servicio_id,
                    'nombre'           => (string) ($grupo->first()->tipoServicio?->nombre ?? '—'),
                    'viajes'           => $grupo->count(),
                    'kg'               => $totalKg,
                    'zonas'            => $zonas->all(),
                    'zonaDia'          => $this->calcularZonaDia($grupo, $desde, $hasta),
                ];
            })
            ->sortByDesc('kg')
            ->values()
            ->all();
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
