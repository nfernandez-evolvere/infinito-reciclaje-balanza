<?php

namespace App\Http\Controllers\Operador;

use App\Http\Controllers\Controller;
use App\Models\TipoServicio;
use App\Models\ZonaServicio;
use App\Models\ZonaServicioTurno;
use Illuminate\Http\JsonResponse;

class ServicioZonasController extends Controller
{
    public function __invoke(TipoServicio $servicio): JsonResponse
    {
        $tipoSugerido = $servicio->tipoVehiculoSugerido?->nombre;

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

        $zonas = $zonaServicios->map(fn ($zs) => [
            'id'     => $zs->zona->id,
            'nombre' => $zs->zona->nombre,
            'turnos' => $turnosPorZona[(string) $zs->zona_id] ?? [],
        ]);

        return response()->json([
            'tipo_vehiculo_sugerido' => $tipoSugerido,
            'zonas'                  => $zonas,
        ]);
    }
}
