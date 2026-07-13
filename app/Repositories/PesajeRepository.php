<?php

namespace App\Repositories;

use App\Models\Pesaje;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PesajeRepository
{
    public function enFechaSinCancelados(Carbon $fecha, array $columns = ['peso_neto_kg', 'created_at']): Collection
    {
        return Pesaje::whereDate('created_at', $fecha)
            ->where('estado', '!=', 'Cancelado')
            ->get($columns);
    }

    public function enRangoSinCancelados(Carbon $desde, Carbon $hasta, array $columns = ['peso_neto_kg', 'created_at']): Collection
    {
        return Pesaje::whereDate('created_at', '>=', $desde)
            ->whereDate('created_at', '<=', $hasta)
            ->where('estado', '!=', 'Cancelado')
            ->get($columns);
    }

    public function paraDesglosePorZona(Carbon $desde, Carbon $hasta): Collection
    {
        return Pesaje::with('zona.tipoServicio')
            ->whereDate('created_at', '>=', $desde)
            ->whereDate('created_at', '<=', $hasta)
            ->where('estado', '!=', 'Cancelado')
            ->get(['zona_id', 'peso_neto_kg', 'turno']);
    }

    public function paraDesglosePorVehiculo(Carbon $desde, Carbon $hasta): Collection
    {
        return Pesaje::with('vehiculo.tipoVehiculo')
            ->whereDate('created_at', '>=', $desde)
            ->whereDate('created_at', '<=', $hasta)
            ->where('estado', '!=', 'Cancelado')
            ->get(['vehiculo_id', 'peso_neto_kg']);
    }

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

    public function filtrado(array $filtros, int $perPage = 20, string $pageName = 'page'): LengthAwarePaginator
    {
        return $this->buildQuery($filtros)
            ->with(['vehiculo.tipoVehiculo', 'tipoServicio', 'zona', 'operador'])
            ->paginate($perPage, ['*'], $pageName)
            ->withQueryString();
    }

    public function kpisFiltrado(array $filtros): array
    {
        $stats = $this->buildQuery($filtros)
            ->where('estado', '!=', 'Cancelado')
            ->reorder()
            ->selectRaw('COUNT(*) as total, SUM(peso_neto_kg) as total_neto')
            ->first();

        $enPredio = $this->buildQuery($filtros)
            ->where('estado', 'En predio')
            ->count();

        $total = (int) ($stats->total ?? 0);
        $totalNeto = (int) ($stats->total_neto ?? 0);

        return [
            'total'           => $total,
            'toneladas_netas' => round($totalNeto / 1000, 1),
            // Promedio derivado de SUM/COUNT (no AVG): AVG sobre columna entera trunca
            // en SQL Server. Así redondea igual que kpisDelTurno y es independiente del motor.
            'promedio_kg' => $total ? (int) round($totalNeto / $total) : 0,
            'en_predio'   => $enPredio,
        ];
    }

    public function kpisDelTurno(): array
    {
        $pesajes = Pesaje::delTurno()->where('estado', '!=', 'Cancelado')->get(['peso_neto_kg', 'estado']);

        return [
            'total'           => $pesajes->count(),
            'toneladas_netas' => round($pesajes->sum('peso_neto_kg') / 1000, 1),
            'promedio_kg'     => $pesajes->count() ? (int) round($pesajes->avg('peso_neto_kg')) : 0,
            'en_predio'       => $pesajes->where('estado', 'En predio')->count(),
        ];
    }

    public function ultimoDelTurno(): ?Pesaje
    {
        return Pesaje::with('vehiculo')->delTurno()->where('estado', '!=', 'Cancelado')->latest()->first();
    }

    public function paraReporte(Carbon $desde, Carbon $hasta, array $filtros = []): Collection
    {
        return Pesaje::with(['zona.tipoServicio', 'vehiculo.tipoVehiculo', 'tipoServicio', 'operador'])
            ->whereDate('created_at', '>=', $desde)
            ->whereDate('created_at', '<=', $hasta)
            ->where('estado', '!=', 'Cancelado')
            ->when($filtros['zona_id'] ?? null, fn ($q, $id) => $q->where('zona_id', $id))
            ->when($filtros['tipo_servicio_id'] ?? null, fn ($q, $id) => $q->where('tipo_servicio_id', $id))
            ->when(
                $filtros['tipo_vehiculo_id'] ?? null,
                fn ($q, $id) => $q->whereHas('vehiculo', fn ($v) => $v->where('tipo_vehiculo_id', $id))
            )
            ->when($filtros['solo_alerta'] ?? false, fn ($q) => $q->where('alerta_peso', true))
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Recalcula en bloque la tara y el neto de los pesajes no cancelados de un vehículo,
     * registrando una entrada de auditoría por cada campo modificado.
     *
     * Set-based: tres sentencias independientes del volumen de pesajes
     * (dos INSERT ... SELECT de auditoría y un UPDATE masivo), en lugar de
     * iterar y emitir queries por fila. Portable entre SQLite (tests) y SQL Server (prod).
     *
     * @return int Cantidad de pesajes actualizados.
     */
    public function recalcularPorCambioDeTara(int $vehiculoId, int $taraNueva, string $motivo, int $usuarioId): int
    {
        $tara = (int) $taraNueva;
        $uid = (int) $usuarioId;
        // ISO 8601 con 'T': como string-binding en INSERT...SELECT/UPDATE raw, el formato
        // 'Y-m-d H:i:s' es ambiguo para SQL Server (DATEFORMAT dmy lo lee fuera de rango).
        $now = now()->format('Y-m-d\TH:i:s');

        // Nuevo neto: max(0, bruto - tara). $tara es entero, seguro de interpolar.
        $netoNuevo = "(CASE WHEN peso_bruto_kg - {$tara} < 0 THEN 0 ELSE peso_bruto_kg - {$tara} END)";

        // Filtro común: pesajes del vehículo, no cancelados, cuya tara realmente cambia.
        $afectados = fn () => DB::table('pesajes')
            ->where('vehiculo_id', $vehiculoId)
            ->where('estado', '!=', 'Cancelado')
            ->where('peso_tara_kg', '!=', $tara);

        $columnasLog = ['pesaje_id', 'campo', 'valor_anterior', 'valor_nuevo', 'motivo', 'usuario_id', 'created_at', 'updated_at'];

        // 1) Auditoría de la tara — captura el valor anterior antes del UPDATE.
        DB::table('pesajes_log')->insertUsing(
            $columnasLog,
            $afectados()->selectRaw(
                "id, 'peso_tara_kg', peso_tara_kg, ?, ?, ?, ?, ?",
                [$tara, $motivo, $uid, $now, $now],
            ),
        );

        // 2) Auditoría del neto — solo donde el neto efectivamente cambia.
        DB::table('pesajes_log')->insertUsing(
            $columnasLog,
            $afectados()
                ->whereRaw("peso_neto_kg != {$netoNuevo}")
                ->selectRaw(
                    "id, 'peso_neto_kg', peso_neto_kg, {$netoNuevo}, ?, ?, ?, ?",
                    [$motivo, $uid, $now, $now],
                ),
        );

        // 3) UPDATE masivo: aplica la nueva tara y el neto recalculado.
        return $afectados()->update([
            'peso_tara_kg' => $tara,
            'peso_neto_kg' => DB::raw($netoNuevo),
            'editado'      => true,
            'updated_at'   => $now,
        ]);
    }

    private function buildQuery(array $filtros): Builder
    {
        return Pesaje::query()
            ->when($filtros['desde'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($filtros['hasta'] ?? null, fn ($q, $h) => $q->whereDate('created_at', '<=', $h))
            ->when($filtros['patente'] ?? null, fn ($q, $p) => $q->whereHas('vehiculo', fn ($v) => $v->where('patente', 'like', '%'.$p.'%')))
            ->when($filtros['estado'] ?? null, fn ($q, $e) => $e === 'Activos'
                ? $q->where('estado', '!=', 'Cancelado')
                : $q->where('estado', $e)
            )
            ->when($filtros['operario_id'] ?? null, fn ($q, $id) => $q->where('operador_id', $id))
            ->when($filtros['zona_id'] ?? null, fn ($q, $id) => $q->where('zona_id', $id))
            ->when($filtros['tipo_servicio_id'] ?? null, fn ($q, $id) => $q->where('tipo_servicio_id', $id))
            ->when(
                $filtros['tipo_vehiculo_id'] ?? null,
                fn ($q, $id) => $q->whereHas('vehiculo', fn ($v) => $v->where('tipo_vehiculo_id', $id))
            )
            ->when($filtros['solo_alerta'] ?? null, fn ($q) => $q->where('alerta_peso', true))
            ->when($filtros['solo_editados'] ?? null, fn ($q) => $q->where('editado', true))
            // Universo de "Modificaciones": pesajes editados o cancelados, con sub-filtro por tipo.
            ->when($filtros['modificaciones'] ?? null, function ($q) use ($filtros) {
                match ($filtros['tipo'] ?? null) {
                    'editado'   => $q->where('editado', true),
                    'cancelado' => $q->where('estado', 'Cancelado'),
                    default     => $q->where(fn ($sub) => $sub->where('editado', true)->orWhere('estado', 'Cancelado')),
                };
            })
            ->orderBy('created_at', $filtros['direction'] ?? 'desc');
    }
}
