<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TipoServicio;
use App\Models\ZonaServicio;
use Illuminate\Http\JsonResponse;

class ServicioZonasController extends Controller
{
    public function __invoke(TipoServicio $servicio): JsonResponse
    {
        $tipoSugerido = $servicio->tipoVehiculoSugerido?->nombre;

        $zonas = ZonaServicio::with(['zona', 'turnos'])
            ->where('tipo_servicio_id', $servicio->id)
            ->whereHas('zona', fn ($q) => $q->where('activo', true))
            ->get()
            ->map(fn ($zs) => [
                'id'     => $zs->zona->id,
                'nombre' => $zs->zona->nombre,
                'turnos' => $zs->turnos->pluck('turno')->values()->all(),
            ]);

        return response()->json([
            'tipo_vehiculo_sugerido' => $tipoSugerido,
            'zonas'                  => $zonas,
        ]);
    }
}
