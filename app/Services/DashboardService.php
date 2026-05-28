<?php

namespace App\Services;

use App\Models\Pesaje;
use App\Models\TipoVehiculo;
use App\Models\Zona;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class DashboardService
{
    public function kpisDelDia(): array
    {
        $pesajes = Pesaje::whereDate('created_at', today())->where('estado', '!=', 'Cancelado')->get(['peso_neto_kg', 'estado', 'created_at']);

        $total     = $pesajes->count();
        $kgTotal   = $pesajes->sum('peso_neto_kg');
        $toneladas = round($kgTotal / 1000, 2);
        $promedio  = $total > 0 ? round($pesajes->avg('peso_neto_kg') / 1000, 2) : 0;

        $ultimo        = $pesajes->sortByDesc('created_at')->first();
        $ultimoHaceMin = $ultimo ? (int) $ultimo->created_at->diffInMinutes(now()) : null;

        ['hectareas' => $ha, 'habitantes' => $hab] = $this->totalesZonas();

        $kgPorHa   = $ha > 0 ? round($kgTotal / $ha, 1) : null;
        $kgPorPers = $hab > 0 ? round($kgTotal / $hab, 2) : null;

        $ant         = Pesaje::whereDate('created_at', today()->subMonth())->where('estado', '!=', 'Cancelado')->get(['peso_neto_kg']);
        $antCount    = $ant->count();
        $antKg       = $ant->sum('peso_neto_kg');
        $antTons     = round($antKg / 1000, 2);
        $antPromedio = $antCount > 0 ? round($antKg / $antCount / 1000, 2) : null;
        $antKgHa     = $ha > 0 ? round($antKg / $ha, 1) : null;
        $antKgPers   = $hab > 0 ? round($antKg / $hab, 2) : null;

        $pct = fn ($curr, $base) => ($base !== null && $base > 0)
            ? round((($curr - $base) / $base) * 100, 1)
            : null;

        return [
            'total'           => $total,
            'toneladas'       => $toneladas,
            'promedio'        => $promedio,
            'ultimo_hace_min' => $ultimoHaceMin,
            'kg_por_ha'       => $kgPorHa,
            'kg_por_persona'  => $kgPorPers,

            'delta'                     => $pct($total, $antCount ?: null),
            'delta_base'                => $antCount,
            'delta_toneladas'           => $pct($toneladas, $antTons ?: null),
            'delta_toneladas_base'      => $antTons,
            'delta_promedio'            => $pct($promedio, $antPromedio),
            'delta_promedio_base'       => $antPromedio,
            'delta_kg_por_ha'           => $pct($kgPorHa, $antKgHa),
            'delta_kg_por_ha_base'      => $antKgHa,
            'delta_kg_por_persona'      => $pct($kgPorPers, $antKgPers),
            'delta_kg_por_persona_base' => $antKgPers,
        ];
    }

    public function kpisDelMes(): array
    {
        $inicioMes = today()->startOfMonth();

        $pesajes = Pesaje::whereDate('created_at', '>=', $inicioMes)->where('estado', '!=', 'Cancelado')->get(['peso_neto_kg', 'created_at']);

        $total   = $pesajes->count();
        $kgTotal = $pesajes->sum('peso_neto_kg');
        $tons    = round($kgTotal / 1000, 2);
        $diasOp  = $pesajes->groupBy(fn ($p) => $p->created_at->toDateString())->count();

        $ant = Pesaje::whereDate('created_at', '>=', today()->subMonth()->startOfMonth())
            ->whereDate('created_at', '<=', today()->subMonth())
            ->where('estado', '!=', 'Cancelado')
            ->get(['peso_neto_kg', 'created_at']);

        $antTotal  = $ant->count();
        $antKg     = $ant->sum('peso_neto_kg');
        $antTons   = round($antKg / 1000, 2);
        $antDiasOp = $ant->groupBy(fn ($p) => $p->created_at->toDateString())->count();

        ['hectareas' => $ha, 'habitantes' => $hab] = $this->totalesZonas();

        $kgPorHa   = $ha > 0 ? round($kgTotal / $ha, 1) : null;
        $kgPorPers = $hab > 0 ? round($kgTotal / $hab, 2) : null;
        $antKgHa   = $ha > 0 ? round($antKg / $ha, 1) : null;
        $antKgPers = $hab > 0 ? round($antKg / $hab, 2) : null;

        $pct = fn ($curr, $base) => ($base !== null && $base > 0)
            ? round((($curr - $base) / $base) * 100, 1)
            : null;

        return [
            'total'      => $total,
            'toneladas'  => $tons,
            'dias_op'    => $diasOp,
            'kg_por_ha'  => $kgPorHa,
            'kg_por_persona' => $kgPorPers,

            'delta'                     => $pct($total, $antTotal ?: null),
            'delta_base'                => $antTotal,
            'delta_toneladas'           => $pct($tons, $antTons ?: null),
            'delta_toneladas_base'      => $antTons,
            'delta_dias_op'             => $pct($diasOp, $antDiasOp ?: null),
            'delta_dias_op_base'        => $antDiasOp,
            'delta_kg_por_ha'           => $pct($kgPorHa, $antKgHa),
            'delta_kg_por_ha_base'      => $antKgHa,
            'delta_kg_por_persona'      => $pct($kgPorPers, $antKgPers),
            'delta_kg_por_persona_base' => $antKgPers,
        ];
    }

    public function evolucionDiaria(int $dias = 7): array
    {
        $desde   = today()->subDays($dias - 1);
        $hasta   = today();
        $formato = $dias <= 15 ? 'D d/m' : 'd/m';

        $pesajesPorDia = Pesaje::whereDate('created_at', '>=', $desde)
            ->where('estado', '!=', 'Cancelado')
            ->get(['peso_neto_kg', 'created_at'])
            ->groupBy(fn ($p) => $p->created_at->toDateString())
            ->map(fn ($grupo) => round($grupo->sum('peso_neto_kg') / 1000, 2));

        $promedio = $pesajesPorDia->isNotEmpty()
            ? round($pesajesPorDia->avg(), 1)
            : 0;

        $datos = collect(CarbonPeriod::create($desde, $hasta))
            ->map(fn (Carbon $dia) => [
                'fecha'     => $dia->translatedFormat($formato),
                'toneladas' => $pesajesPorDia[$dia->toDateString()] ?? 0,
            ])
            ->values()
            ->all();

        return ['datos' => $datos, 'promedio' => $promedio];
    }

    public function desgloseByZona(?Carbon $desde = null, ?Carbon $hasta = null): Collection
    {
        $desde = $desde ?? today();
        $hasta = $hasta ?? today();

        $pesajes = Pesaje::with('zona')
            ->whereDate('created_at', '>=', $desde)
            ->whereDate('created_at', '<=', $hasta)
            ->where('estado', '!=', 'Cancelado')
            ->get(['zona_id', 'peso_neto_kg', 'turno']);

        $total = $pesajes->sum('peso_neto_kg');

        $agrupados = $pesajes
            ->filter(fn ($p) => $p->zona_id !== null)
            ->groupBy(fn ($p) => $p->zona_id . '|' . ($p->turno ?? ''))
            ->map(function ($grupo) use ($total) {
                $turno  = $grupo->first()->turno;
                $count  = $grupo->count();
                $sumaKg = $grupo->sum('peso_neto_kg');

                $zona = $grupo->first()->zona;
                return [
                    'zona_id'      => $grupo->first()->zona_id,
                    'nombre'       => ($zona?->nombre ?? '—') . ($turno ? ' ' . $turno : ''),
                    'turno'        => $turno,
                    'pesajes'      => $count,
                    'toneladas'    => round($sumaKg / 1000, 2),
                    'kg_por_viaje' => number_format((int) round($sumaKg / $count), 0, ',', '.'),
                    'porcentaje'   => $total > 0 ? round(($sumaKg / $total) * 100, 1) : 0,
                    'kg_por_ha'    => ($zona?->hectareas > 0) ? round($sumaKg / $zona->hectareas, 1) : null,
                    'kg_por_hab'   => ($zona?->habitantes > 0) ? round($sumaKg / $zona->habitantes, 2) : null,
                ];
            });

        $zonasConPesajes = $pesajes->filter(fn ($p) => $p->zona_id !== null)->pluck('zona_id')->unique();

        $zonasSinPesajes = Zona::activos()
            ->whereNotIn('id', $zonasConPesajes)
            ->get()
            ->map(fn ($zona) => [
                'zona_id'      => $zona->id,
                'nombre'       => $zona->nombre,
                'turno'        => null,
                'pesajes'      => 0,
                'toneladas'    => 0.0,
                'kg_por_viaje' => '—',
                'porcentaje'   => 0,
                'kg_por_ha'    => null,
                'kg_por_hab'   => null,
            ]);

        return $agrupados->values()->concat($zonasSinPesajes)->sortByDesc('toneladas')->values();
    }

    public function desgloseByTipoVehiculo(?Carbon $desde = null, ?Carbon $hasta = null): Collection
    {
        $desde = $desde ?? today();
        $hasta = $hasta ?? today();

        $pesajes = Pesaje::with('vehiculo.tipoVehiculo')
            ->whereDate('created_at', '>=', $desde)
            ->whereDate('created_at', '<=', $hasta)
            ->where('estado', '!=', 'Cancelado')
            ->get(['vehiculo_id', 'peso_neto_kg']);

        $total = $pesajes->sum('peso_neto_kg');

        $agrupados = $pesajes
            ->filter(fn ($p) => $p->vehiculo?->tipo_vehiculo_id !== null)
            ->groupBy(fn ($p) => $p->vehiculo->tipo_vehiculo_id)
            ->map(function ($grupo) use ($total) {
                $count  = $grupo->count();
                $sumaKg = $grupo->sum('peso_neto_kg');
                return [
                    'nombre'       => $grupo->first()->vehiculo->tipoVehiculo?->nombre ?? '—',
                    'pesajes'      => $count,
                    'toneladas'    => round($sumaKg / 1000, 2),
                    'kg_por_viaje' => number_format((int) round($sumaKg / $count), 0, ',', '.'),
                    'porcentaje'   => $total > 0 ? round(($sumaKg / $total) * 100, 1) : 0,
                ];
            });

        return TipoVehiculo::activos()
            ->get()
            ->map(fn ($tipo) => $agrupados->get($tipo->id, [
                'nombre'       => $tipo->nombre,
                'pesajes'      => 0,
                'toneladas'    => 0.0,
                'kg_por_viaje' => '—',
                'porcentaje'   => 0,
            ]))
            ->sortByDesc('toneladas')
            ->values();
    }

    public function evolucionDelRango(Carbon $desde, Carbon $hasta): array
    {
        $diasRango = (int) ($desde->diffInDays($hasta) + 1);
        $formato   = $diasRango <= 15 ? 'D d/m' : 'd/m';

        $pesajesPorDia = Pesaje::whereDate('created_at', '>=', $desde)
            ->whereDate('created_at', '<=', $hasta)
            ->where('estado', '!=', 'Cancelado')
            ->get(['peso_neto_kg', 'created_at'])
            ->groupBy(fn ($p) => $p->created_at->toDateString())
            ->map(fn ($grupo) => round($grupo->sum('peso_neto_kg') / 1000, 2));

        $promedio = $pesajesPorDia->isNotEmpty()
            ? round($pesajesPorDia->avg(), 1)
            : 0;

        $datos = collect(CarbonPeriod::create($desde, $hasta))
            ->map(fn (Carbon $dia) => [
                'fecha'     => $dia->translatedFormat($formato),
                'toneladas' => $pesajesPorDia[$dia->toDateString()] ?? 0,
            ])
            ->values()
            ->all();

        return ['datos' => $datos, 'promedio' => $promedio];
    }

    public function kpisDelRango(Carbon $desde, Carbon $hasta): array
    {
        $pesajes = Pesaje::whereDate('created_at', '>=', $desde)
            ->whereDate('created_at', '<=', $hasta)
            ->where('estado', '!=', 'Cancelado')
            ->get(['peso_neto_kg', 'created_at']);

        $total      = $pesajes->count();
        $toneladas  = round($pesajes->sum('peso_neto_kg') / 1000, 2);
        $diasOp     = $pesajes->groupBy(fn ($p) => $p->created_at->toDateString())->count();
        $diasRango  = (int) ($desde->diffInDays($hasta) + 1);
        $promedioDia = $diasOp > 0 ? round(($pesajes->sum('peso_neto_kg') / $diasOp) / 1000, 2) : 0;

        ['hectareas' => $ha, 'habitantes' => $hab] = $this->totalesZonas();
        $kgTotal = $pesajes->sum('peso_neto_kg');

        return [
            'total'          => $total,
            'toneladas'      => $toneladas,
            'dias_op'        => $diasOp,
            'dias_rango'     => $diasRango,
            'promedio_dia'   => $promedioDia,
            'kg_por_ha'      => $ha > 0 ? round($kgTotal / $ha, 1) : null,
            'kg_por_persona' => $hab > 0 ? round($kgTotal / $hab, 2) : null,
        ];
    }

    public function alertasActivas(): int
    {
        // Sprint 6: módulo de alarmas. Por ahora siempre 0.
        return 0;
    }

    private function totalesZonas(): array
    {
        $zonas = Zona::activos()->get(['hectareas', 'habitantes']);
        return [
            'hectareas'  => (float) $zonas->sum('hectareas'),
            'habitantes' => (int) $zonas->sum('habitantes'),
        ];
    }
}
