<?php

namespace App\Services;

use App\Models\Pesaje;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class DashboardService
{
    public function kpisDelDia(): array
    {
        $pesajes = Pesaje::whereDate('created_at', today())->get(['peso_neto_kg', 'estado', 'created_at']);

        $total    = $pesajes->count();
        $toneladas = round($pesajes->sum('peso_neto_kg') / 1000, 2);
        $promedio  = $total > 0 ? round($pesajes->avg('peso_neto_kg') / 1000, 2) : 0;

        $primerPesaje = $pesajes->sortBy('created_at')->first();
        $horasOp = $primerPesaje
            ? round($primerPesaje->created_at->diffInMinutes(now()) / 60, 1)
            : 0;

        $mesAnteriorHoy = Pesaje::whereDate('created_at', today()->subMonth())->count();
        $deltaDia = $mesAnteriorHoy > 0
            ? round((($total - $mesAnteriorHoy) / $mesAnteriorHoy) * 100, 1)
            : null;

        return [
            'total'      => $total,
            'toneladas'  => $toneladas,
            'promedio'   => $promedio,
            'horas_op'   => $horasOp,
            'delta'      => $deltaDia,
        ];
    }

    public function kpisDelMes(): array
    {
        $inicioMes = today()->startOfMonth();

        $pesajes = Pesaje::whereDate('created_at', '>=', $inicioMes)->get(['peso_neto_kg', 'created_at']);

        $total     = $pesajes->count();
        $toneladas = round($pesajes->sum('peso_neto_kg') / 1000, 2);
        $diasOp    = $pesajes->groupBy(fn ($p) => $p->created_at->toDateString())->count();

        $inicioMesAnterior = today()->subMonth()->startOfMonth();
        $finMesAnterior    = today()->subMonth();
        $totalMesAnterior  = Pesaje::whereDate('created_at', '>=', $inicioMesAnterior)
            ->whereDate('created_at', '<=', $finMesAnterior)
            ->count();

        $delta = $totalMesAnterior > 0
            ? round((($total - $totalMesAnterior) / $totalMesAnterior) * 100, 1)
            : null;

        return [
            'total'      => $total,
            'toneladas'  => $toneladas,
            'dias_op'    => $diasOp,
            'delta'      => $delta,
        ];
    }

    public function evolucionDiaria(int $dias = 7): array
    {
        $desde  = today()->subDays($dias - 1);
        $hasta  = today();
        $formato = $dias <= 15 ? 'D d/m' : 'd/m';

        $pesajesPorDia = Pesaje::whereDate('created_at', '>=', $desde)
            ->get(['peso_neto_kg', 'created_at'])
            ->groupBy(fn ($p) => $p->created_at->toDateString())
            ->map(fn ($grupo) => round($grupo->sum('peso_neto_kg') / 1000, 2));

        return collect(CarbonPeriod::create($desde, $hasta))
            ->map(fn (Carbon $dia) => [
                'fecha'     => $dia->translatedFormat($formato),
                'toneladas' => $pesajesPorDia[$dia->toDateString()] ?? 0,
            ])
            ->values()
            ->all();
    }

    public function desgloseByZona(): Collection
    {
        $pesajes = Pesaje::with('zona')
            ->whereDate('created_at', today())
            ->get(['zona_id', 'peso_neto_kg']);

        $total = $pesajes->sum('peso_neto_kg');

        return $pesajes->groupBy('zona_id')
            ->map(function ($grupo) use ($total) {
                return [
                    'nombre'    => $grupo->first()->zona?->nombre ?? '—',
                    'pesajes'   => $grupo->count(),
                    'toneladas' => round($grupo->sum('peso_neto_kg') / 1000, 2),
                    'porcentaje' => $total > 0
                        ? round(($grupo->sum('peso_neto_kg') / $total) * 100, 1)
                        : 0,
                ];
            })
            ->sortByDesc('toneladas')
            ->values();
    }

    public function desgloseByTipoVehiculo(): Collection
    {
        $pesajes = Pesaje::with('vehiculo.tipoVehiculo')
            ->whereDate('created_at', today())
            ->get(['vehiculo_id', 'peso_neto_kg']);

        $total = $pesajes->sum('peso_neto_kg');

        return $pesajes->groupBy(fn ($p) => $p->vehiculo?->tipo_vehiculo_id)
            ->map(function ($grupo) use ($total) {
                return [
                    'nombre'    => $grupo->first()->vehiculo?->tipoVehiculo?->nombre ?? '—',
                    'pesajes'   => $grupo->count(),
                    'toneladas' => round($grupo->sum('peso_neto_kg') / 1000, 2),
                    'porcentaje' => $total > 0
                        ? round(($grupo->sum('peso_neto_kg') / $total) * 100, 1)
                        : 0,
                ];
            })
            ->sortByDesc('toneladas')
            ->values();
    }

    public function camionesEnPredio(): Collection
    {
        return Pesaje::with(['vehiculo.tipoVehiculo', 'tipoServicio', 'zona', 'operador'])
            ->where('estado', 'En predio')
            ->orderBy('created_at')
            ->get();
    }

    public function alertasActivas(): int
    {
        // Sprint 6: módulo de alarmas. Por ahora siempre 0.
        return 0;
    }
}
