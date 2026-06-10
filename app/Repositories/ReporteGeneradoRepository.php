<?php

namespace App\Repositories;

use App\Models\ReporteGenerado;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReporteGeneradoRepository
{
    public function create(array $data): ReporteGenerado
    {
        return ReporteGenerado::create($data);
    }

    /**
     * Historial paginado de la organización activa (más reciente primero).
     * El scope de organización lo aplica el trait BelongsToOrganizacion.
     * Excluye la columna snapshot (JSON pesado que la lista no usa) y expone
     * el flag tiene_snapshot para que la UI decida si ofrece descargas.
     */
    public function paginarHistorial(int $porPagina = 15): LengthAwarePaginator
    {
        return ReporteGenerado::with('usuario:id,name', 'revisadoPor:id,name')
            ->select([
                'id', 'organizacion_id', 'usuario_id', 'reporte_programado_id',
                'origen', 'tipo', 'formato', 'periodo_desde', 'periodo_hasta',
                'filtros', 'destinatarios', 'estado', 'error', 'conclusiones',
                'revisado_por_id', 'revisado_at', 'enviado_at', 'motivo_descarte',
                'created_at', 'updated_at',
            ])
            ->selectRaw('CASE WHEN snapshot IS NULL THEN 0 ELSE 1 END AS tiene_snapshot')
            ->latest()
            ->paginate($porPagina);
    }

    public function contarPendientesRevision(): int
    {
        return ReporteGenerado::where('estado', ReporteGenerado::ESTADO_EN_REVISION)->count();
    }

    /**
     * Transición de estado con lock optimista: el UPDATE solo aplica si el
     * registro sigue en alguno de los estados de partida. Devuelve false si
     * otro usuario/job ganó la carrera (p. ej. doble aprobación concurrente).
     * Sin global scopes: se usa también desde los jobs, fuera del ciclo HTTP.
     *
     * @param  list<string>  $desdeEstados
     * @param  array<string, mixed>  $atributos
     */
    public function transicionar(ReporteGenerado $generado, array $desdeEstados, array $atributos): bool
    {
        // El query builder no aplica los casts del modelo: el snapshot (array)
        // se serializa a mano antes del UPDATE.
        if (is_array($atributos['snapshot'] ?? null)) {
            $atributos['snapshot'] = json_encode($atributos['snapshot']);
        }

        $actualizado = ReporteGenerado::withoutGlobalScopes()
            ->whereKey($generado->id)
            ->whereIn('estado', $desdeEstados)
            ->update($atributos) === 1;

        if ($actualizado) {
            $generado->refresh();
        }

        return $actualizado;
    }

    /**
     * Marca como fallidos los registros varados en estados de procesamiento
     * (worker muerto sin pasar por failed()): sin esto quedarían en
     * "Generando…"/"Enviando…" para siempre, sin posibilidad de reintento.
     * Corre desde el scheduler, fuera de HTTP: sin global scopes.
     */
    public function marcarVaradosComoFallidos(int $horas = 2): int
    {
        return ReporteGenerado::withoutGlobalScopes()
            ->whereIn('estado', [ReporteGenerado::ESTADO_GENERANDO, ReporteGenerado::ESTADO_ENVIANDO])
            ->where('updated_at', '<', now()->subHours($horas))
            ->update([
                'estado' => ReporteGenerado::ESTADO_FALLIDO,
                'error'  => 'Procesamiento interrumpido. Reintentá desde el historial.',
            ]);
    }
}
