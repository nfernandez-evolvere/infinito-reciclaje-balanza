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
        $total   = $pesajes->count();
        $kgTotal = $pesajes->sum('peso_neto_kg');
        $diasOp  = $pesajes->groupBy(fn ($p) => $p->created_at->toDateString())->count();
        $diasRango = (int) $desde->diffInDays($hasta) + 1;

        return [
            'total'              => $total,
            'toneladas'          => round($kgTotal / 1000, 2),
            'dias_op'            => $diasOp,
            'dias_rango'         => $diasRango,
            'promedio_ton_dia'   => $diasOp > 0 ? round(($kgTotal / $diasOp) / 1000, 2) : 0,
            'promedio_kg_viaje'  => $total > 0 ? (int) round($kgTotal / $total) : 0,
        ];
    }

    private function calcularEvolucion(Collection $pesajes, Carbon $desde, Carbon $hasta): array
    {
        $diasRango = (int) $desde->diffInDays($hasta) + 1;
        $formato   = $diasRango <= 15 ? 'D d/m' : 'd/m';

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
            ->groupBy(fn ($p) => $p->zona_id . '|' . ($p->turno ?? ''))
            ->map(function ($grupo) use ($total) {
                $count  = $grupo->count();
                $sumaKg = $grupo->sum('peso_neto_kg');
                $zona   = $grupo->first()->zona;
                $turno  = $grupo->first()->turno;

                return [
                    'nombre'      => $zona?->nombre ?? '—',
                    'turno'       => $turno,
                    'viajes'      => $count,
                    'toneladas'   => round($sumaKg / 1000, 2),
                    'kg_viaje'    => (int) round($sumaKg / $count),
                    'porcentaje'  => $total > 0 ? round(($sumaKg / $total) * 100, 1) : 0,
                    'kg_ha'       => ($zona?->hectareas > 0) ? round($sumaKg / $zona->hectareas, 1) : null,
                    'kg_hab'      => ($zona?->habitantes > 0) ? round($sumaKg / $zona->habitantes, 2) : null,
                ];
            })
            ->sortByDesc('toneladas')
            ->values();
    }

    private function calcularPorVehiculo(Collection $pesajes): Collection
    {
        $total = $pesajes->sum('peso_neto_kg');

        return $pesajes
            ->filter(fn ($p) => $p->vehiculo?->tipo_vehiculo_id !== null)
            ->groupBy(fn ($p) => $p->vehiculo->tipo_vehiculo_id)
            ->map(function ($grupo) use ($total) {
                $count  = $grupo->count();
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
