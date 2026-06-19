<?php

namespace App\Repositories;

use App\Models\Alerta;
use App\Models\ConfigAlerta;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AlertaRepository
{
    public function countNoLeidas(int $userId): int
    {
        return Alerta::withoutGlobalScopes()
            ->where('user_id', $userId)
            ->where('leida', false)
            ->count();
    }

    /** @return Collection<int, Alerta> */
    public function ultimasNoLeidas(int $userId, int $limit = 5): Collection
    {
        return Alerta::withoutGlobalScopes()
            ->where('user_id', $userId)
            ->where('leida', false)
            ->with(['pesaje.vehiculo', 'zona'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function listar(int $userId, array $filtros = []): LengthAwarePaginator
    {
        $q = Alerta::withoutGlobalScopes()
            ->where('alertas.user_id', $userId)
            ->with(['pesaje.vehiculo', 'zona'])
            ->orderByDesc('alertas.created_at');

        if (! empty($filtros['tipo'])) {
            $q->where('tipo', $filtros['tipo']);
        }

        if (isset($filtros['leida']) && $filtros['leida'] !== '') {
            $q->where('leida', (bool) $filtros['leida']);
        }

        if (! empty($filtros['desde'])) {
            $q->whereDate('fecha_deteccion', '>=', $filtros['desde']);
        }

        if (! empty($filtros['hasta'])) {
            $q->whereDate('fecha_deteccion', '<=', $filtros['hasta']);
        }

        return $q->paginate(20)->withQueryString();
    }

    public function marcarLeida(Alerta $alerta): Alerta
    {
        $alerta->update([
            'leida'    => true,
            'leida_at' => now()->format('Y-m-d\TH:i:s'),
        ]);

        return $alerta;
    }

    public function marcarTodasLeidas(int $userId): int
    {
        return Alerta::withoutGlobalScopes()
            ->where('user_id', $userId)
            ->where('leida', false)
            ->update([
                'leida'    => true,
                'leida_at' => now()->format('Y-m-d\TH:i:s'),
            ]);
    }

    public function create(array $data): Alerta
    {
        return Alerta::create($data);
    }

    /**
     * Crea la notificación in-app de un evento de reporte (campana). Comparte la
     * tabla alertas pero sin pesaje/zona; organizacion_id va explícito porque se
     * invoca desde la cola, donde app('organizacion') puede no estar bound.
     */
    public function crearDeReporte(
        int $organizacionId,
        int $userId,
        string $tipo,
        string $titulo,
        ?string $descripcion,
        int $reporteGeneradoId,
    ): Alerta {
        return Alerta::create([
            'organizacion_id'     => $organizacionId,
            'user_id'             => $userId,
            'tipo'                => $tipo,
            'titulo'              => $titulo,
            'descripcion'         => $descripcion,
            'reporte_generado_id' => $reporteGeneradoId,
            'fecha_deteccion'     => now(),
        ]);
    }

    public function existeHoy(int $organizacionId, string $tipo, Carbon $fecha, ?int $pesajeId = null, ?int $zonaId = null): bool
    {
        $q = Alerta::withoutGlobalScopes()
            ->where('organizacion_id', $organizacionId)
            ->where('tipo', $tipo)
            ->whereDate('fecha_deteccion', $fecha->toDateString());

        if ($pesajeId) {
            $q->where('pesaje_id', $pesajeId);
        }

        if ($zonaId) {
            $q->where('zona_id', $zonaId);
        }

        return $q->exists();
    }

    // Config ——————————————————————————————————————————

    /** @return Collection<string, ConfigAlerta> */
    public function getConfigPorOrg(int $organizacionId): Collection
    {
        return ConfigAlerta::withoutGlobalScopes()
            ->where('organizacion_id', $organizacionId)
            ->get()
            ->keyBy('tipo');
    }

    public function upsertConfig(int $organizacionId, string $tipo, array $data): ConfigAlerta
    {
        return ConfigAlerta::withoutGlobalScopes()->updateOrCreate(
            ['organizacion_id' => $organizacionId, 'tipo' => $tipo],
            $data,
        );
    }

    public function getConfig(int $organizacionId, string $tipo): ?ConfigAlerta
    {
        return ConfigAlerta::withoutGlobalScopes()
            ->where('organizacion_id', $organizacionId)
            ->where('tipo', $tipo)
            ->first();
    }
}
