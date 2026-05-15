<?php

namespace App\Repositories;

use App\Models\TipoVehiculo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TipoVehiculoRepository
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return TipoVehiculo::query()
            ->when(
                !empty($filters['nombre']),
                fn ($q) => $q->where('nombre', 'like', '%' . $filters['nombre'] . '%')
            )
            ->when(
                isset($filters['peso_min']) && $filters['peso_min'] !== '',
                fn ($q) => $q->where('peso_min_kg', '>=', (int) $filters['peso_min'])
            )
            ->when(
                isset($filters['peso_max']) && $filters['peso_max'] !== '',
                fn ($q) => $q->where('peso_max_kg', '<=', (int) $filters['peso_max'])
            )
            ->when(
                isset($filters['activo']) && $filters['activo'] !== '',
                fn ($q) => $q->where('activo', (bool) $filters['activo'])
            )
            ->orderBy('nombre')
            ->paginate($perPage)
            ->appends(array_filter($filters, fn ($v) => $v !== '' && $v !== null));
    }

    public function find(int $id): TipoVehiculo
    {
        return TipoVehiculo::findOrFail($id);
    }

    public function create(array $data): TipoVehiculo
    {
        return TipoVehiculo::create($data);
    }

    public function update(TipoVehiculo $tipoVehiculo, array $data): TipoVehiculo
    {
        $tipoVehiculo->update($data);
        return $tipoVehiculo;
    }

    public function deactivate(TipoVehiculo $tipoVehiculo): void
    {
        $tipoVehiculo->update(['activo' => false]);
    }

    public function activate(TipoVehiculo $tipoVehiculo): void
    {
        $tipoVehiculo->update(['activo' => true]);
    }

    public function delete(TipoVehiculo $tipoVehiculo): void
    {
        $tipoVehiculo->delete();
    }
}
