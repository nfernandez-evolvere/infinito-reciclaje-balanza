<?php

namespace App\Http\Controllers\Operador;

use App\Http\Controllers\Controller;
use App\Repositories\PesajeRepository;
use Illuminate\View\View;

class HistorialController extends Controller
{
    public function __construct(protected PesajeRepository $pesajeRepository) {}

    public function __invoke(): View
    {
        $pesajes = $this->pesajeRepository->delTurnoConRelaciones();
        $kpis    = $this->pesajeRepository->kpisDelTurno();

        return view('modules.operador.historial', compact('pesajes', 'kpis'));
    }
}
