<?php

namespace App\Http\Controllers\Operador;

use App\Http\Controllers\Controller;
use App\Http\Requests\EgresoPesajeRequest;
use App\Models\Pesaje;
use App\Services\PesajeService;
use Illuminate\Http\RedirectResponse;

class EgresoPesajeController extends Controller
{
    public function __construct(protected PesajeService $pesajeService) {}

    public function __invoke(EgresoPesajeRequest $request, Pesaje $pesaje): RedirectResponse
    {
        $this->pesajeService->marcarEgreso($pesaje, $request->validated());

        return redirect()->route('historial')
            ->with('toast', ['message' => 'Egreso registrado.', 'description' => '', 'variant' => 'success']);
    }
}
