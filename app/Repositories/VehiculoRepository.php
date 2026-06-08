<?php

namespace App\Repositories;

use App\Models\Vehiculo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class VehiculoRepository
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Vehiculo::query()
            ->with('tipoVehiculo')
            ->withCount('pesajes')
            ->when(
                ! empty($filters['patente']),
                fn ($q) => $q->where('patente', 'like', '%'.$filters['patente'].'%')
            )
            ->when(
                ! empty($filters['numero_interno']),
                fn ($q) => $q->where('numero_interno', 'like', '%'.$filters['numero_interno'].'%')
            )
            ->when(
                ! empty($filters['tipo_vehiculo_id']),
                fn ($q) => $q->where('tipo_vehiculo_id', $filters['tipo_vehiculo_id'])
            )
            ->when(
                isset($filters['activo']) && $filters['activo'] !== '',
                fn ($q) => $q->where('activo', (bool) $filters['activo'])
            )
            ->orderBy('patente')
            ->paginate($perPage)
            ->appends(array_filter($filters, fn ($v) => $v !== '' && $v !== null));
    }

    /**
     * @return Collection<int, Vehiculo>
     */
    public function activos(): Collection
    {
        return Vehiculo::activos()->orderBy('patente')->get();
    }

    /**
     * @return Collection<int, Vehiculo>
     */
    public function buscar(string $q, int $limit = 6): Collection
    {
        return Vehiculo::query()
            ->with('tipoVehiculo')
            ->where('activo', true)
            ->where(fn ($query) => $query
                ->where('patente', 'like', '%'.$q.'%')
                ->orWhere('numero_interno', 'like', '%'.$q.'%')
            )
            ->limit($limit)
            ->get();
    }

    public function create(array $data): Vehiculo
    {
        return Vehiculo::create($data);
    }

    public function update(Vehiculo $vehiculo, array $data): Vehiculo
    {
        $vehiculo->update($data);

        return $vehiculo;
    }

    public function deactivate(Vehiculo $vehiculo): void
    {
        $vehiculo->update(['activo' => false]);
    }

    public function activate(Vehiculo $vehiculo): void
    {
        $vehiculo->update(['activo' => true]);
    }

    public function delete(Vehiculo $vehiculo): void
    {
        $vehiculo->delete();
    }
}
