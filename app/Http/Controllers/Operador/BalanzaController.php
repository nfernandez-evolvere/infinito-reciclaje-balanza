<?php

namespace App\Http\Controllers\Operador;

use App\Http\Controllers\Controller;
use App\Models\TipoServicio;
use Illuminate\View\View;

class BalanzaController extends Controller
{
    public function __invoke(): View
    {
        $servicios = TipoServicio::activos()->with('tipoVehiculoSugerido')->get();

        return view('modules.shared.balanza', [
            'servicios'  => $servicios,
            'formAction' => route('pesajes.store'),
            'cancelUrl'  => route('historial'),
        ]);
    }
}
