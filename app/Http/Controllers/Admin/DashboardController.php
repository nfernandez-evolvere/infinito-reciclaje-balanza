<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
    ) {}

    public function __invoke(): View
    {
        $inicioMes = today()->startOfMonth();

        return view('modules.admin.dashboard', [
            'kpisDia'             => $this->dashboardService->kpisDelDia(),
            'kpisMes'             => $this->dashboardService->kpisDelMes(),
            'evolucion7'          => $this->dashboardService->evolucionDiaria(7),
            'evolucion15'         => $this->dashboardService->evolucionDiaria(15),
            'evolucion90'         => $this->dashboardService->evolucionDiaria(90),
            'desgloseVehiculo'    => $this->dashboardService->desgloseByTipoVehiculo(),
            'desgloseZona'        => $this->dashboardService->desgloseByZona(),
            'desgloseVehiculoMes' => $this->dashboardService->desgloseByTipoVehiculo($inicioMes, today()),
            'desgloseZonaMes'     => $this->dashboardService->desgloseByZona($inicioMes, today()),
            'alertas'             => $this->dashboardService->alertasActivas(),
        ]);
    }

    public function data(): JsonResponse
    {
        $inicioMes = today()->startOfMonth();

        return response()->json([
            'kpisDia'             => $this->dashboardService->kpisDelDia(),
            'kpisMes'             => $this->dashboardService->kpisDelMes(),
            'evolucion7'          => $this->dashboardService->evolucionDiaria(7),
            'evolucion15'         => $this->dashboardService->evolucionDiaria(15),
            'evolucion90'         => $this->dashboardService->evolucionDiaria(90),
            'desgloseVehiculo'    => $this->dashboardService->desgloseByTipoVehiculo(),
            'desgloseZona'        => $this->dashboardService->desgloseByZona(),
            'desgloseVehiculoMes' => $this->dashboardService->desgloseByTipoVehiculo($inicioMes, today()),
            'desgloseZonaMes'     => $this->dashboardService->desgloseByZona($inicioMes, today()),
            'alertas'             => $this->dashboardService->alertasActivas(),
        ]);
    }
}
