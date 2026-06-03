<?php

namespace App\Repositories;

use App\Models\TipoServicio;
use App\Models\Zona;
use App\Models\ZonaServicio;
use App\Models\ZonaServicioHorario;
use App\Models\ZonaServicioTurno;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class ZonaRepository
{
    public function activos(): Collection
    {
        return Zona::activos()->orderBy('nombre')->get();
    }

    public function totales(): array
    {
        $zonas = Zona::activos()->get(['hectareas', 'habitantes']);

        return [
            'hectareas'  => (float) $zonas->sum('hectareas'),
            'habitantes' => (int) $zonas->sum('habitantes'),
        ];
    }

    public function activosExcluyendo(array $ids): Collection
    {
        return Zona::activos()
            ->when(! empty($ids), fn ($q) => $q->whereNotIn('id', $ids))
            ->get();
    }

    public function zonasConTurnosPara(TipoServicio $servicio): SupportCollection
    {
        $zonaServicios = ZonaServicio::with('zona')
            ->where('tipo_servicio_id', $servicio->id)
            ->whereHas('zona', fn ($q) => $q->where('activo', true))
            ->get();

        $zonaIds = $zonaServicios->pluck('zona_id');

        $turnosPorZona = ZonaServicioTurno::where('tipo_servicio_id', $servicio->id)
            ->whereIn('zona_id', $zonaIds)
            ->get()
            ->groupBy(fn ($t) => (string) $t->zona_id)
            ->map(fn ($ts) => $ts->pluck('turno')->values()->all());

        return $zonaServicios->map(fn ($zs) => [
            'id'     => $zs->zona->id,
            'nombre' => $zs->zona->nombre,
            'turnos' => $turnosPorZona[(string) $zs->zona_id] ?? [],
        ]);
    }

    public function all(array $filters = []): Collection
    {
        $zonas = Zona::query()
            ->with(['zonaServicios.tipoServicio'])
            ->when(
                ! empty($filters['nombre']),
                fn ($q) => $q->where('nombre', 'like', '%'.$filters['nombre'].'%')
            )
            ->when(
                isset($filters['activo']) && $filters['activo'] !== '',
                fn ($q) => $q->where('activo', (bool) $filters['activo'])
            )
            ->orderBy('nombre')
            ->get();

        // Eloquent's HasMany solo puede matching por una sola columna. Como zona_servicios
        // tiene clave compuesta (zona_id + tipo_servicio_id), el eager loading estándar
        // asigna todos los turnos de zona_id=X a TODAS las ZonaServicio de esa zona.
        // Cargamos turnos y horarios una sola vez y los asignamos con la clave compuesta correcta.
        $zonaIds = $zonas->pluck('id');

        $todosTurnos = ZonaServicioTurno::whereIn('zona_id', $zonaIds)->get();

        $todosHorarios = ZonaServicioHorario::whereIn('zona_id', $zonaIds)
            ->orderBy('dia_semana')
            ->orderBy('franja')
            ->get();

        foreach ($zonas as $zona) {
            foreach ($zona->zonaServicios as $zs) {
                $zs->setRelation(
                    'turnos',
                    $todosTurnos->filter(
                        fn ($t) => $t->zona_id === $zs->zona_id
                               && $t->tipo_servicio_id === $zs->tipo_servicio_id
                    )->values()
                );
                $zs->setRelation(
                    'horarios',
                    $todosHorarios->filter(
                        fn ($h) => $h->zona_id === $zs->zona_id
                               && $h->tipo_servicio_id === $zs->tipo_servicio_id
                    )->values()
                );
            }
        }

        return $zonas;
    }

    public function create(array $data): Zona
    {
        return Zona::create($data);
    }

    public function update(Zona $zona, array $data): Zona
    {
        $zona->update($data);

        return $zona;
    }

    public function deactivate(Zona $zona): void
    {
        $zona->update(['activo' => false]);
    }

    public function activate(Zona $zona): void
    {
        $zona->update(['activo' => true]);
    }

    public function delete(Zona $zona): void
    {
        $zona->delete();
    }

    public function assignServicio(Zona $zona, int $tipoServicioId, array $turnos, array $horarios): ZonaServicio
    {
        $zonaServicio = ZonaServicio::create([
            'zona_id'          => $zona->id,
            'tipo_servicio_id' => $tipoServicioId,
        ]);

        $this->syncTurnos($zona->id, $tipoServicioId, $turnos);
        $this->syncHorarios($zona->id, $tipoServicioId, $horarios);

        return $zonaServicio;
    }

    public function updateServicio(Zona $zona, int $tipoServicioId, array $turnos, array $horarios): void
    {
        $this->syncTurnos($zona->id, $tipoServicioId, $turnos);
        $this->syncHorarios($zona->id, $tipoServicioId, $horarios);
    }

    public function removeServicio(Zona $zona, int $tipoServicioId): void
    {
        ZonaServicio::where('zona_id', $zona->id)
            ->where('tipo_servicio_id', $tipoServicioId)
            ->delete();
    }

    private function syncTurnos(int $zonaId, int $tipoServicioId, array $turnos): void
    {
        ZonaServicioTurno::where('zona_id', $zonaId)
            ->where('tipo_servicio_id', $tipoServicioId)
            ->delete();

        foreach ($turnos as $turno) {
            ZonaServicioTurno::create([
                'zona_id'          => $zonaId,
                'tipo_servicio_id' => $tipoServicioId,
                'turno'            => $turno,
            ]);
        }
    }

    private function syncHorarios(int $zonaId, int $tipoServicioId, array $horarios): void
    {
        ZonaServicioHorario::where('zona_id', $zonaId)
            ->where('tipo_servicio_id', $tipoServicioId)
            ->delete();

        // $horarios is indexed by day (0-6), each value is array of {inicio, fin}
        foreach ($horarios as $diaIdx => $franjas) {
            foreach ($franjas as $franjaIdx => $franja) {
                if (empty($franja['inicio']) || empty($franja['fin'])) {
                    continue;
                }
                ZonaServicioHorario::create([
                    'zona_id'          => $zonaId,
                    'tipo_servicio_id' => $tipoServicioId,
                    'dia_semana'       => (int) $diaIdx + 1,
                    'franja'           => (int) $franjaIdx + 1,
                    'hora_inicio'      => $franja['inicio'],
                    'hora_fin'         => $franja['fin'],
                ]);
            }
        }
    }
}
