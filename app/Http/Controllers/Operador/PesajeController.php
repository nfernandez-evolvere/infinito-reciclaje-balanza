<?php

namespace App\Http\Controllers\Operador;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePesajeRequest;
use App\Http\Requests\UpdatePesajeRequest;
use App\Models\Pesaje;
use App\Services\PesajeService;
use Illuminate\Http\RedirectResponse;

class PesajeController extends Controller
{
    public function __construct(protected PesajeService $pesajeService) {}

    public function store(StorePesajeRequest $request): RedirectResponse
    {
        $this->pesajeService->crear($request->validated(), auth()->user());

        return redirect()->route('balanza')
            ->with('toast', ['message' => 'Pesaje guardado.', 'description' => '', 'variant' => 'success']);
    }

    public function update(UpdatePesajeRequest $request, Pesaje $pesaje): RedirectResponse
    {
        $this->pesajeService->editar($pesaje, $request->validated(), auth()->user());

        return redirect()->route('historial')
            ->with('toast', ['message' => 'Cambios guardados.', 'description' => '', 'variant' => 'success']);
    }
}
