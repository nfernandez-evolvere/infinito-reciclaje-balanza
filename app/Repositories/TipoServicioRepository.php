<?php

namespace App\Repositories;

use App\Models\TipoServicio;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class TipoServicioRepository
{
    /** @return Collection<int, TipoServicio> */
    public function activos(): Collection
    {
        return TipoServicio::activos()->orderBy('nombre')->get();
    }

    /**
     * Mapa id => nombre para los ids dados. Pensado para resolver etiquetas de auditoría.
     *
     * @param  iterable<int, int|string>  $ids
     * @return SupportCollection<int, string>
     */
    public function nombresPorIds(iterable $ids): SupportCollection
    {
        return TipoServicio::whereIn('id', $ids)->pluck('nombre', 'id');
    }

    public function activosConTiposVehiculo(): Collection
    {
        return TipoServicio::activos()->with('tiposVehiculo')->get();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return TipoServicio::query()
            ->with([
                'tiposVehiculo',
                'zonas' => fn ($q) => $q->orderBy('nombre'),
                'zonas.turnos',
                'zonas.horarios',
            ])
            ->when(
                ! empty($filters['nombre']),
                fn ($q) => $q->where('nombre', 'like', '%'.$filters['nombre'].'%')
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
