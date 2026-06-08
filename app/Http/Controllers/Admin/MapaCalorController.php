<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MapaCalorController extends Controller
{
    public function __construct(
        protected DashboardService $service,
    ) {}

    public function index(Request $request): View
    {
        $hasta = $this->parseFecha($request->input('hasta')) ?? today();
        $desde = $this->parseFecha($request->input('desde')) ?? $hasta->copy()->startOfMonth();

        // Si el rango viene invertido, lo normalizamos al mes en curso del extremo superior.
        if ($desde->gt($hasta)) {
            $desde = $hasta->copy()->startOfMonth();
        }

        $zonas = $this->service->metricasPorZona($desde, $hasta);

        return view('modules.admin.mapa-calor.index', compact('zonas', 'desde', 'hasta'));
    }

    private function parseFecha(?string $valor): ?Carbon
    {
        if (! $valor) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $valor)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
