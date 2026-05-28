<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ReporteExcelExport;
use App\Http\Controllers\Controller;
use App\Models\TipoServicio;
use App\Models\TipoVehiculo;
use App\Models\Zona;
use App\Services\ReporteService;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    public function __construct(
        protected ReporteService $reporteService,
    ) {}

    public function index(Request $request): View
    {
        $zonas         = Zona::activos()->orderBy('nombre')->get();
        $tiposServicio = TipoServicio::activos()->orderBy('nombre')->get();
        $tiposVehiculo = TipoVehiculo::activos()->orderBy('nombre')->get();

        $reporte = null;

        if ($request->filled('desde') && $request->filled('hasta')) {
            $desde = Carbon::parse($request->input('desde'));
            $hasta = Carbon::parse($request->input('hasta'));

            if ($desde->lte($hasta)) {
                $reporte = $this->reporteService->generar(
                    $desde,
                    $hasta,
                    array_filter($request->only(['zona_id', 'tipo_servicio_id', 'tipo_vehiculo_id']))
                );
            }
        }

        return view('modules.admin.reportes.index', compact('reporte', 'zonas', 'tiposServicio', 'tiposVehiculo'));
    }

    public function exportPdf(Request $request)
    {
        $request->validate([
            'desde' => ['required', 'date'],
            'hasta' => ['required', 'date', 'after_or_equal:desde'],
        ]);

        $desde = Carbon::parse($request->input('desde'));
        $hasta = Carbon::parse($request->input('hasta'));

        $reporte = $this->reporteService->generar(
            $desde,
            $hasta,
            array_filter($request->only(['zona_id', 'tipo_servicio_id', 'tipo_vehiculo_id']))
        );

        $filename = 'reporte_' . $desde->format('Y-m-d') . '_' . $hasta->format('Y-m-d') . '.pdf';

        try {
            $pdf = SnappyPdf::loadView('modules.admin.reportes.pdf', compact('reporte'))
                ->setOption('page-size', 'A4')
                ->setOption('orientation', 'Portrait')
                ->setOption('margin-top', 10)
                ->setOption('margin-bottom', 10)
                ->setOption('margin-left', 15)
                ->setOption('margin-right', 15)
                ->setOption('encoding', 'UTF-8');

            return $pdf->download($filename);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['pdf' => 'El binario wkhtmltopdf no está instalado. Instalalo desde wkhtmltopdf.org y configurá WKHTML_PDF_BINARY en .env']);
        }
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $request->validate([
            'desde' => ['required', 'date'],
            'hasta' => ['required', 'date', 'after_or_equal:desde'],
        ]);

        $desde = Carbon::parse($request->input('desde'));
        $hasta = Carbon::parse($request->input('hasta'));

        $reporte = $this->reporteService->generar(
            $desde,
            $hasta,
            array_filter($request->only(['zona_id', 'tipo_servicio_id', 'tipo_vehiculo_id']))
        );

        $filename = 'reporte_' . $desde->format('Y-m-d') . '_' . $hasta->format('Y-m-d') . '.xlsx';

        return (new ReporteExcelExport($reporte))->download($filename);
    }
}
