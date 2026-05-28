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
        $toneladas = round($pesajes->sum('peso_neto_kg') / 1000, 2);
        $promedio  = $total > 0 ? round($pesajes->avg('peso_neto_kg') / 1000, 2) : 0;

        $ultimo = $pesajes->sortByDesc('created_at')->first();
        $ultimoHaceMin = $ultimo ? (int) $ultimo->created_at->diffInMinutes(now()) : null;

        $mesAnteriorHoy = Pesaje::whereDate('created_at', today()->subMonth())->count();
        $deltaDia = $mesAnteriorHoy > 0
            ? round((($total - $mesAnteriorHoy) / $mesAnteriorHoy) * 100, 1)
            : null;

        return [
            'total'          => $total,
            'toneladas'      => $toneladas,
            'promedio'       => $promedio,
            'ultimo_hace_min' => $ultimoHaceMin,
            'delta'          => $deltaDia,
        ];
    }

    public function kpisDelMes(): array
    {
        $inicioMes = today()->startOfMonth();

        $pesajes = Pesaje::whereDate('created_at', '>=', $inicioMes)->where('estado', '!=', 'Cancelado')->get(['peso_neto_kg', 'created_at']);

        $total     = $pesajes->count();
        $toneladas = round($pesajes->sum('peso_neto_kg') / 1000, 2);
        $diasOp    = $pesajes->groupBy(fn ($p) => $p->created_at->toDateString())->count();

        $inicioMesAnterior = today()->subMonth()->startOfMonth();
        $finMesAnterior    = today()->subMonth();

        $mesAnterior = Pesaje::whereDate('created_at', '>=', $inicioMesAnterior)
            ->whereDate('created_at', '<=', $finMesAnterior)
            ->get(['peso_neto_kg']);

        $totalMesAnterior     = $mesAnterior->count();
        $toneladasMesAnterior = round($mesAnterior->sum('peso_neto_kg') / 1000, 2);

        $delta = $totalMesAnterior > 0
            ? round((($total - $totalMesAnterior) / $totalMesAnterior) * 100, 1)
            : null;

        $deltaToneladas = $toneladasMesAnterior > 0
            ? round((($toneladas - $toneladasMesAnterior) / $toneladasMesAnterior) * 100, 1)
            : null;

        return [
            'total'           => $total,
            'toneladas'       => $toneladas,
            'dias_op'         => $diasOp,
            'delta'           => $delta,
            'delta_toneladas' => $deltaToneladas,
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

                return [
                    'zona_id'      => $grupo->first()->zona_id,
                    'nombre'       => ($grupo->first()->zona?->nombre ?? '—') . ($turno ? ' ' . $turno : ''),
                    'turno'        => $turno,
                    'pesajes'      => $count,
                    'toneladas'    => round($sumaKg / 1000, 2),
                    'kg_por_viaje' => number_format((int) round($sumaKg / $count), 0, ',', '.'),
                    'porcentaje'   => $total > 0 ? round(($sumaKg / $total) * 100, 1) : 0,
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

        return [
            'total'        => $total,
            'toneladas'    => $toneladas,
            'dias_op'      => $diasOp,
            'dias_rango'   => $diasRango,
            'promedio_dia' => $promedioDia,
        ];
    }

    public function alertasActivas(): int
    {
        // Sprint 6: módulo de alarmas. Por ahora siempre 0.
        return 0;
    }
}
