<?php

namespace App\Http\Controllers\Operador;

use App\Http\Controllers\Controller;
use App\Repositories\VehiculoRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehiculoBuscarController extends Controller
{
    public function __construct(protected VehiculoRepository $vehiculoRepository) {}

    public function __invoke(Request $request): JsonResponse
    {
        $q = trim($request->query('q', ''));

        if ($q === '') {
            return response()->json([]);
        }

        $vehiculos = $this->vehiculoRepository->buscar($q);

        return response()->json(
            $vehiculos->map(fn ($v) => [
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
