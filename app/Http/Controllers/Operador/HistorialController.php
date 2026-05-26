<?php

namespace App\Http\Controllers\Operador;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\PesajeRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistorialController extends Controller
{
    public function __construct(protected PesajeRepository $pesajeRepository) {}

    public function __invoke(Request $request): View
    {
        $filtros = [
            'desde'       => $request->input('desde', today()->toDateString()),
            'hasta'       => $request->input('hasta', today()->toDateString()),
            'patente'     => $request->input('patente') ?: null,
            'estado'      => $request->input('estado') ?: null,
            'operario_id' => $request->input('operario_id') ?: null,
        ];

        $pesajes      = $this->pesajeRepository->filtrado($filtros);
        $kpis         = $this->pesajeRepository->kpisDe($pesajes);
        $kpisHoy      = $this->pesajeRepository->kpisDelTurno();
        $ultimoPesaje = $this->pesajeRepository->ultimoDelTurno();
        $operarios    = User::whereHas('organizaciones', fn ($q) => $q->where('organizaciones.id', app('organizacion')->id))
            ->where('role', 'operador')
            ->orderBy('name')
            ->get();

        return view('modules.operador.historial', compact('pesajes', 'kpis', 'kpisHoy', 'ultimoPesaje', 'filtros', 'operarios'));
    }
}
