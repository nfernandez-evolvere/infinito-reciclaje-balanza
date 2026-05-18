<?php

namespace App\Repositories;

use App\Models\TipoServicio;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TipoServicioRepository
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return TipoServicio::query()
            ->with('tipoVehiculo')
            ->when(
                !empty($filters['nombre']),
                fn ($q) => $q->where('nombre', 'like', '%' . $filters['nombre'] . '%')
            )
            ->when(
                isset($filters['tipo_vehiculo_id']) && $filters['tipo_vehiculo_id'] !== '',
                fn ($q) => $q->where('tipo_vehiculo_sugerido_id', (int) $filters['tipo_vehiculo_id'])
            )
            ->when(
                isset($filters['activo']) && $filters['activo'] !== '',
                fn ($q) => $q->where('activo', (bool) $filters['activo'])
            )
            ->orderBy('nombre')
            ->paginate($perPage)
            ->appends(array_filter($filters, fn ($v) => $v !== '' && $v !== null));
    }

    public function create(array $data): TipoServicio
    {
        return TipoServicio::create($data);
    }

    public function update(TipoServicio $tipoServicio, array $data): TipoServicio
    {
        $tipoServicio->update($data);
        return $tipoServicio;
    }

    public function deactivate(TipoServicio $tipoServicio): void
    {
        $tipoServicio->update(['activo' => false]);
    }

    public function activate(TipoServicio $tipoServicio): void
    {
        $tipoServicio->update(['activo' => true]);
    }

    public function delete(TipoServicio $tipoServicio): void
    {
        $tipoServicio->delete();
    }
}
