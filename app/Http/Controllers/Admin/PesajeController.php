<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePesajeRequest;
use App\Models\TipoServicio;
use App\Models\User;
use App\Repositories\PesajeRepository;
use App\Services\PesajeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PesajeController extends Controller
{
    public function __construct(
        protected PesajeService $pesajeService,
        protected PesajeRepository $pesajeRepository,
    ) {}

    public function index(Request $request): View
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

        return view('modules.shared.historial', [
            'pesajes'        => $pesajes,
            'kpis'           => $kpis,
            'kpisHoy'        => $kpisHoy,
            'ultimoPesaje'   => $ultimoPesaje,
            'filtros'        => $filtros,
            'operarios'      => $operarios,
            'titulo'         => 'Pesajes',
            'routeHistorial' => route('admin.pesajes.index'),
        ]);
    }

    public function create(): View
    {
        $servicios = TipoServicio::activos()->with('tipoVehiculoSugerido')->get();

        return view('modules.shared.balanza', [
            'servicios'  => $servicios,
            'formAction' => route('admin.pesajes.store'),
            'cancelUrl'  => route('admin.pesajes.index'),
        ]);
    }

    public function store(StorePesajeRequest $request): RedirectResponse
    {
        $this->pesajeService->crear($request->validated(), auth()->user());

        return redirect()->route('admin.pesajes.index')
            ->with('toast', [
                'message'     => 'Pesaje registrado',
                'description' => 'El pesaje fue guardado con éxito.',
                'variant'     => 'success',
            ]);
    }
}
