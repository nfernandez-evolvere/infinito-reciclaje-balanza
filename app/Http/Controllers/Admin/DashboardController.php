<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function data(Request $request): JsonResponse
    {
        $inicioMes = today()->startOfMonth();

        $response = [
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
        ];

        if ($request->filled('desde') && $request->filled('hasta')) {
            try {
                $desde = Carbon::parse($request->string('desde'))->startOfDay();
                $hasta = Carbon::parse($request->string('hasta'))->endOfDay();

                if ($desde <= $hasta && $hasta->lte(now()->endOfDay())) {
                    $response['kpisRango']             = $this->dashboardService->kpisDelRango($desde, $hasta);
                    $response['evolucionRango']        = $this->dashboardService->evolucionDelRango($desde, $hasta);
                    $response['desgloseVehiculoRango'] = $this->dashboardService->desgloseByTipoVehiculo($desde, $hasta);
                    $response['desgloseZonaRango']     = $this->dashboardService->desgloseByZona($desde, $hasta);
                }
            } catch (\Exception) {
                // fechas inválidas, se ignoran
            }
        }

        return response()->json($response);
    }
}
