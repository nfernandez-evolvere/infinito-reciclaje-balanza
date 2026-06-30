<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\TipoServicioRepository;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
        protected TipoServicioRepository $tipoServicioRepository,
    ) {}

    public function index(): View
    {
        // La lista de servicios alimenta el selector del mapa de calor (incluye los
        // que aún no tienen zonas). Es estática, así que solo va en la carga inicial,
        // no en el refresh JSON de data().
        $servicios = $this->tipoServicioRepository->activos()
            ->map(fn ($s) => ['id' => $s->id, 'nombre' => $s->nombre])
            ->values();

        return view('modules.admin.dashboard', array_merge(
            $this->buildDashboardData(),
            ['servicios' => $servicios],
        ));
    }

    public function data(Request $request): JsonResponse
    {
        $response = $this->buildDashboardData();

        if ($request->filled('desde') && $request->filled('hasta')) {
            try {
                $desde = Carbon::parse($request->string('desde'))->startOfDay();
                $hasta = Carbon::parse($request->string('hasta'))->endOfDay();

                if ($desde <= $hasta && $hasta->lte(now()->endOfDay())) {
                    $response['kpisRango'] = $this->dashboardService->kpisDelRango($desde, $hasta);
                    $response['evolucionRango'] = $this->dashboardService->evolucionDelRango($desde, $hasta);
                    $response['desgloseVehiculoRango'] = $this->dashboardService->desgloseByTipoVehiculo($desde, $hasta);
                    $response['desgloseZonaRango'] = $this->dashboardService->desgloseByZona($desde, $hasta);
                    $response['metricasPorZonaRango'] = $this->dashboardService->metricasPorZona($desde, $hasta);
                }
            } catch (\Exception) {
                // fechas inválidas, se ignoran
            }
        }

        return response()->json($response);
    }

    private function buildDashboardData(): array
    {
        $inicioMes = today()->startOfMonth();

        return [
            'kpisDia'             => $this->dashboardService->kpisDelDia(),
            'kpisMes'             => $this->dashboardService->kpisDelMes(),
            'evolucion7'          => $this->dashboardService->evolucionDiaria(7),
            'evolucion15'         => $this->dashboardService->evolucionDiaria(15),
            'evolucion90'         => $this->dashboardService->evolucionDiaria(90),
            'desgloseVehiculo'    => $this->dashboardService->desgloseByTipoVehiculo(),
            'desgloseZona'        => $this->dashboardService->desgloseByZona(),
            'desgloseVehiculoMes' => $this->dashboardService->desgloseByTipoVehiculo($inicioMes, today()),
            'desgloseZonaMes'     => $this->dashboardService->desgloseByZona($inicioMes, today()),
            'metricasPorZonaDia'  => $this->dashboardService->metricasPorZona(today(), today()),
            'metricasPorZonaMes'  => $this->dashboardService->metricasPorZona($inicioMes, today()),
            'alertas'             => $this->dashboardService->alertasActivas(),
        ];
    }
}
