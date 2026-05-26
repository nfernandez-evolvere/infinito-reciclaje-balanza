<?php

namespace App\Repositories;

use App\Models\Pesaje;
use Illuminate\Database\Eloquent\Collection;

class PesajeRepository
{
    public function create(array $data): Pesaje
    {
        return Pesaje::create($data);
    }

    public function update(Pesaje $pesaje, array $data): Pesaje
    {
        $pesaje->update($data);
        return $pesaje;
    }

    public function findOrFail(int $id): Pesaje
    {
        return Pesaje::with(['vehiculo.tipoVehiculo', 'tipoServicio', 'zona', 'operador'])->findOrFail($id);
    }

    public function delTurnoConRelaciones(): Collection
    {
        return Pesaje::with(['vehiculo.tipoVehiculo', 'tipoServicio', 'zona', 'operador'])
            ->delTurno()
            ->orderByDesc('created_at')
            ->get();
    }

    public function filtrado(array $filtros): Collection
    {
        return Pesaje::with(['vehiculo', 'tipoServicio', 'zona', 'operador'])
            ->when($filtros['desde'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($filtros['hasta'] ?? null, fn ($q, $h) => $q->whereDate('created_at', '<=', $h))
            ->when($filtros['patente'] ?? null, fn ($q, $p) => $q->whereHas('vehiculo', fn ($v) => $v->where('patente', 'like', '%' . $p . '%')))
            ->when($filtros['estado'] ?? null, fn ($q, $e) => $q->where('estado', $e))
            ->when($filtros['operario_id'] ?? null, fn ($q, $id) => $q->where('operador_id', $id))
            ->orderByDesc('created_at')
            ->get();
    }

    public function kpisDe(Collection $pesajes): array
    {
        return [
            'total'           => $pesajes->count(),
            'toneladas_netas' => round($pesajes->sum('peso_neto_kg') / 1000, 1),
            'promedio_kg'     => $pesajes->count() ? (int) round($pesajes->avg('peso_neto_kg')) : 0,
            'en_predio'       => $pesajes->where('estado', 'En predio')->count(),
        ];
    }

    public function kpisDelTurno(): array
    {
        $pesajes = Pesaje::delTurno()->get(['peso_neto_kg', 'estado']);

        return [
            'total'           => $pesajes->count(),
            'toneladas_netas' => round($pesajes->sum('peso_neto_kg') / 1000, 1),
            'promedio_kg'     => $pesajes->count() ? (int) round($pesajes->avg('peso_neto_kg')) : 0,
            'en_predio'       => $pesajes->where('estado', 'En predio')->count(),
        ];
    }

    public function ultimoDelTurno(): ?Pesaje
    {
        return Pesaje::with('vehiculo')->delTurno()->latest()->first();
    }
}
