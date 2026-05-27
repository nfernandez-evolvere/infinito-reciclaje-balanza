<?php

namespace App\Http\Controllers\Operador;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePesajeRequest;
use App\Repositories\TipoServicioRepository;
use App\Services\PesajeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PesajeController extends Controller
{
    public function __construct(
        protected PesajeService $pesajeService,
        protected TipoServicioRepository $tipoServicioRepository,
    ) {}

    public function __invoke(): View
    {
        $servicios = $this->tipoServicioRepository->activosConVehiculoSugerido();

        return view('modules.operador.balanza', [
            'servicios'  => $servicios,
            'formAction' => route('pesajes.store'),
            'cancelUrl'  => route('historial'),
        ]);
    }

    public function store(StorePesajeRequest $request): RedirectResponse
    {
        $pesaje = $this->pesajeService->crear($request->validated(), auth()->user());
        return redirect()->route('pesajes.show', $pesaje);
    }
}
