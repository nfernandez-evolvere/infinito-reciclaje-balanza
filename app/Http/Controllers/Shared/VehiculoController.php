<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Repositories\VehiculoRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehiculoController extends Controller
{
    public function __construct(protected VehiculoRepository $vehiculoRepository) {}

    public function activos(): JsonResponse
    {
        return response()->json(
            $this->vehiculoRepository->activos()->map(fn ($v) => [
                'id'      => $v->id,
                'patente' => $v->patente,
                'interno' => $v->numero_interno,
            ])
        );
    }

    public function buscar(Request $request): JsonResponse
    {
        $q = trim($request->query('q', ''));

        if ($q === '') {
            return response()->json([]);
        }

        return response()->json(
            $this->vehiculoRepository->buscar($q)->map(fn ($v) => [
                'id'       => $v->id,
                'patente'  => $v->patente,
                'interno'  => $v->numero_interno,
                'tara'     => $v->tara_kg,
                'tipo'     => $v->tipoVehiculo?->nombre,
                'titular'  => $v->titular,
                'peso_min' => $v->tipoVehiculo?->peso_min_kg,
                'peso_max' => $v->tipoVehiculo?->peso_max_kg,
            ])
        );
    }
}
