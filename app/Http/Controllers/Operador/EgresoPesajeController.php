<?php

namespace App\Http\Controllers\Operador;

use App\Http\Controllers\Controller;
use App\Http\Requests\EgresoPesajeRequest;
use App\Repositories\PesajeRepository;
use App\Services\PesajeService;
use Illuminate\Http\RedirectResponse;

class EgresoPesajeController extends Controller
{
    public function __construct(
        protected PesajeService $pesajeService,
        protected PesajeRepository $pesajeRepository,
    ) {}

    public function __invoke(EgresoPesajeRequest $request, int $id): RedirectResponse
    {
        $pesaje = $this->pesajeRepository->findOrFail($id);

        $this->pesajeService->marcarEgreso($pesaje, $request->validated());

        return redirect()->route('historial')
            ->with('toast', ['message' => 'Egreso registrado.', 'description' => '', 'variant' => 'success']);
    }
}
