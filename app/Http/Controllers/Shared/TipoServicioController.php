<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\TipoServicio;
use App\Repositories\ZonaRepository;
use Illuminate\Http\JsonResponse;

class TipoServicioController extends Controller
{
    public function __construct(protected ZonaRepository $zonaRepository) {}

    public function zonas(TipoServicio $servicio): JsonResponse
    {
        return response()->json([
            'tipos_vehiculo_sugeridos' => $servicio->tiposVehiculo->pluck('nombre')->values(),
            'zonas'                    => $this->zonaRepository->zonasConTurnosPara($servicio),
        ]);
    }
}
