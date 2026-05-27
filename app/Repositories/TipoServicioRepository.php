<?php

namespace App\Repositories;

use App\Models\TipoServicio;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TipoServicioRepository
{
    public function activos(): Collection
    {
        return TipoServicio::activos()->orderBy('nombre')->get();
    }

    public function activosConVehiculoSugerido(): Collection
    {
        return TipoServicio::activos()->with('tipoVehiculoSugerido')->get();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return TipoServicio::query()
            ->with('tiposVehiculo')
            ->when(
                !empty($filters['nombre']),
                fn ($q) => $q->where('nombre', 'like', '%' . $filters['nombre'] . '%')
            )
            ->when(
                isset($filters['tipo_vehiculo_id']) && $filters['tipo_vehiculo_id'] !== '',
                fn ($q) => $q->whereHas(
                    'tiposVehiculo',
                    fn ($r) => $r->where('tipos_vehiculo.id', (int) $filters['tipo_vehiculo_id'])
                )
            )
            ->when(
                isset($filters['activo']) && $filters['activo'] !== '',
                fn ($q) => $q->where('activo', (bool) $filters['activo'])
            )
            ->orderBy('nombre')
            ->paginate($perPage)
            ->appends(array_filter($filters, fn ($v) => $v !== '' && $v !== null));
    }

    public function create(array $data, array $tipoVehiculoIds = []): TipoServicio
    {
        $tipoServicio = TipoServicio::create($data);
        $tipoServicio->tiposVehiculo()->sync($tipoVehiculoIds);
        return $tipoServicio;
    }

    public function update(TipoServicio $tipoServicio, array $data, array $tipoVehiculoIds = []): TipoServicio
    {
        $tipoServicio->update($data);
        $tipoServicio->tiposVehiculo()->sync($tipoVehiculoIds);
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
