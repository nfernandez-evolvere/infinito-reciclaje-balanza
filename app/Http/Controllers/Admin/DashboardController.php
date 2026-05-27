<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
    ) {}

    public function __invoke(): View
    {
        return view('modules.admin.dashboard', [
            'kpisDia'          => $this->dashboardService->kpisDelDia(),
            'kpisMes'          => $this->dashboardService->kpisDelMes(),
            'evolucion7'       => $this->dashboardService->evolucionDiaria(7),
            'evolucion15'      => $this->dashboardService->evolucionDiaria(15),
            'evolucion90'      => $this->dashboardService->evolucionDiaria(90),
            'desgloseZona'     => $this->dashboardService->desgloseByZona(),
            'desgloseVehiculo' => $this->dashboardService->desgloseByTipoVehiculo(),
            'alertas'          => $this->dashboardService->alertasActivas(),
        ]);
    }
}
