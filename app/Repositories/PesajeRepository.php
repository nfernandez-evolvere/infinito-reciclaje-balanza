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

    public function kpisDelTurno(): array
    {
        $pesajes = Pesaje::delTurno()->get();

        return [
            'total'          => $pesajes->count(),
            'toneladas_netas' => round($pesajes->sum('peso_neto_kg') / 1000, 1),
            'promedio_kg'    => $pesajes->count() ? (int) round($pesajes->avg('peso_neto_kg')) : 0,
            'en_predio'      => $pesajes->where('estado', 'En predio')->count(),
        ];
    }
}
