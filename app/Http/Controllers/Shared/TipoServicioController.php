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
            'tipo_vehiculo_sugerido' => $servicio->tipoVehiculoSugerido?->nombre,
            'zonas'                  => $this->zonaRepository->zonasConTurnosPara($servicio),
        ]);
    }
}
