<?php

namespace App\Repositories;

use App\Models\Pesaje;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

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

    public function filtrado(array $filtros, int $perPage = 20): LengthAwarePaginator
    {
        return $this->buildQuery($filtros)
            ->with(['vehiculo.tipoVehiculo', 'tipoServicio', 'zona', 'operador'])
            ->paginate($perPage)
            ->withQueryString();
    }

    public function filtradoTodos(array $filtros): Collection
    {
        return $this->buildQuery($filtros)
            ->with(['vehiculo.tipoVehiculo', 'tipoServicio', 'zona', 'operador'])
            ->get();
    }

    public function kpisFiltrado(array $filtros): array
    {
        $stats = $this->buildQuery($filtros)
            ->selectRaw('COUNT(*) as total, SUM(peso_neto_kg) as total_neto, AVG(peso_neto_kg) as avg_neto')
            ->first();

        $enPredio = $this->buildQuery($filtros)
            ->where('estado', 'En predio')
            ->count();

        $total = (int) ($stats->total ?? 0);

        return [
            'total'           => $total,
            'toneladas_netas' => round(($stats->total_neto ?? 0) / 1000, 1),
            'promedio_kg'     => $total ? (int) round($stats->avg_neto) : 0,
            'en_predio'       => $enPredio,
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

    private function buildQuery(array $filtros): Builder
    {
        return Pesaje::query()
            ->when($filtros['desde'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($filtros['hasta'] ?? null, fn ($q, $h) => $q->whereDate('created_at', '<=', $h))
            ->when($filtros['patente'] ?? null, fn ($q, $p) => $q->whereHas('vehiculo', fn ($v) => $v->where('patente', 'like', '%' . $p . '%')))
            ->when($filtros['estado'] ?? null, fn ($q, $e) => $q->where('estado', $e))
            ->when($filtros['operario_id'] ?? null, fn ($q, $id) => $q->where('operador_id', $id))
            ->when($filtros['zona_id'] ?? null, fn ($q, $id) => $q->where('zona_id', $id))
            ->when($filtros['tipo_servicio_id'] ?? null, fn ($q, $id) => $q->where('tipo_servicio_id', $id))
            ->when($filtros['solo_alerta'] ?? null, fn ($q) => $q->where('alerta_peso', true))
            ->when($filtros['solo_editados'] ?? null, fn ($q) => $q->where('editado', true))
            ->orderBy('created_at', $filtros['sort_direction'] ?? 'desc');
    }
}
