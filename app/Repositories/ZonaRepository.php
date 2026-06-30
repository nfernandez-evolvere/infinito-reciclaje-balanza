<?php

namespace App\Repositories;

use App\Models\TipoServicio;
use App\Models\Zona;
use App\Models\ZonaHorario;
use App\Models\ZonaTurno;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class ZonaRepository
{
    public function activos(): Collection
    {
        return Zona::activos()->orderBy('nombre')->get();
    }

    /**
     * Mapa id => nombre para los ids dados. Pensado para resolver etiquetas de auditoría.
     *
     * @param  iterable<int, int|string>  $ids
     * @return SupportCollection<int, string>
     */
    public function nombresPorIds(iterable $ids): SupportCollection
    {
        return Zona::whereIn('id', $ids)->pluck('nombre', 'id');
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

    /**
     * Zonas activas del servicio con sus turnos, para el formulario de pesaje.
     *
     * @return SupportCollection<int, array{id: int, nombre: string, turnos: array<int, string>}>
     */
    public function zonasConTurnosPara(TipoServicio $servicio): SupportCollection
    {
        return Zona::activos()
            ->where('tipo_servicio_id', $servicio->id)
            ->with('turnos')
            ->orderBy('nombre')
            ->get()
            ->map(fn (Zona $zona) => [
                'id'     => $zona->id,
                'nombre' => $zona->nombre,
                'turnos' => $zona->turnos->pluck('turno')->values()->all(),
            ]);
    }

    public function create(array $data, array $turnos = [], array $horarios = []): Zona
    {
        $zona = Zona::create($data);

        $this->syncTurnos($zona->id, $turnos);
        $this->syncHorarios($zona->id, $horarios);

        return $zona;
    }

    public function update(Zona $zona, array $data, array $turnos = [], array $horarios = []): Zona
    {
        $zona->update($data);

        $this->syncTurnos($zona->id, $turnos);
        $this->syncHorarios($zona->id, $horarios);

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

    private function syncTurnos(int $zonaId, array $turnos): void
    {
        ZonaTurno::where('zona_id', $zonaId)->delete();

        foreach ($turnos as $turno) {
            ZonaTurno::create([
                'zona_id' => $zonaId,
                'turno'   => $turno,
            ]);
        }
    }

    private function syncHorarios(int $zonaId, array $horarios): void
    {
        ZonaHorario::where('zona_id', $zonaId)->delete();

        // $horarios is indexed by day (0-6), each value is array of {inicio, fin}
        foreach ($horarios as $diaIdx => $franjas) {
            foreach ($franjas as $franjaIdx => $franja) {
                if (empty($franja['inicio']) || empty($franja['fin'])) {
                    continue;
                }
                ZonaHorario::create([
                    'zona_id'     => $zonaId,
                    'dia_semana'  => (int) $diaIdx + 1,
                    'franja'      => (int) $franjaIdx + 1,
                    'hora_inicio' => $franja['inicio'],
                    'hora_fin'    => $franja['fin'],
                ]);
            }
        }
    }
}
