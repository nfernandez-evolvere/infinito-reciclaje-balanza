<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ReporteExcelExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreReporteProgramadoRequest;
use App\Http\Requests\Admin\UpdateReporteProgramadoRequest;
use App\Http\Requests\Admin\UpdateReporteConfiguracionRequest;
use App\Jobs\GenerarEnviarReporteJob;
use App\Models\ReporteProgramado;
use App\Repositories\ReporteConfiguracionRepository;
use App\Repositories\ReporteDestinatarioRepository;
use App\Repositories\ReporteProgramadoRepository;
use App\Services\ConclusionesAIService;
use App\Services\PdfService;
use App\Services\ReporteConfiguracionService;
use App\Services\ReporteProgramadoService;
use App\Services\ReporteService;
use App\Services\SvgChartService;
use App\Repositories\TipoServicioRepository;
use App\Repositories\TipoVehiculoRepository;
use App\Repositories\ZonaRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    public function __construct(
        protected ReporteService $reporteService,
        protected ZonaRepository $zonaRepository,
        protected TipoVehiculoRepository $tipoVehiculoRepository,
        protected SvgChartService $svgChartService,
        protected PdfService $pdfService,
        protected ReporteProgramadoRepository $programadoRepository,
        protected ReporteProgramadoService $programadoService,
        protected ReporteConfiguracionRepository $configuracionRepository,
        protected ReporteConfiguracionService $configuracionService,
        protected ReporteDestinatarioRepository $destinatarioRepository,
        protected TipoServicioRepository $tipoServicioRepository,
    ) {}

    // ── Index ──────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'programados');

        $zonas         = $this->zonaRepository->activos();
        $tiposServicio = $this->tipoServicioRepository->activos();
        $tiposVehiculo = $this->tipoVehiculoRepository->activos();
        $programados   = $this->programadoRepository->allOrdered();
        $config        = $this->configuracionRepository->first() ?? new \App\Models\ReporteConfiguracion();

        $filters = [
            'desde'            => $request->input('desde'),
            'hasta'            => $request->input('hasta'),
            'zona_id'          => $request->input('zona_id'),
            'tipo_servicio_id' => $request->input('tipo_servicio_id'),
            'tipo_vehiculo_id' => $request->input('tipo_vehiculo_id'),
        ];

        $activeFilters = count(array_filter([
            $filters['zona_id'],
            $filters['tipo_servicio_id'],
            $filters['tipo_vehiculo_id'],
        ]));

        $reporte = null;

        if ($request->filled('desde') && $request->filled('hasta')) {
            $desde = Carbon::parse($filters['desde']);
            $hasta = Carbon::parse($filters['hasta']);

            if ($desde->lte($hasta)) {
                $reporte = $this->reporteService->generar(
                    $desde,
                    $hasta,
                    array_filter($request->only(['zona_id', 'tipo_servicio_id', 'tipo_vehiculo_id']))
                );
            }
        }

        return view('modules.admin.reportes.index', compact(
            'tab', 'reporte', 'zonas', 'tiposServicio', 'tiposVehiculo', 'filters', 'activeFilters',
            'programados', 'config'
        ));
    }

    // ── Exports ────────────────────────────────────────────────────────────

    public function exportExcel(Request $request): StreamedResponse
    {
        $request->validate([
            'desde' => ['required', 'date'],
            'hasta' => ['required', 'date', 'after_or_equal:desde'],
        ]);

        $reporte  = $this->generarReporte($request);
        $filename = 'reporte_' . $request->desde . '_' . $request->hasta . '.xlsx';

        return (new ReporteExcelExport($reporte))->download($filename);
    }

    public function exportPdfPresentacion(Request $request): Response
    {
        $request->validate([
            'desde' => ['required', 'date'],
            'hasta' => ['required', 'date', 'after_or_equal:desde'],
        ]);

        $desde  = Carbon::parse($request->input('desde'));
        $hasta  = Carbon::parse($request->input('hasta'));
        $reporte = $this->reporteService->generar(
            $desde,
            $hasta,
            array_filter($request->only(['zona_id', 'tipo_servicio_id', 'tipo_vehiculo_id']))
        );

        $config = $this->configuracionRepository->first();

        $conclusiones = [];
        if ($config?->ai_enabled && $config?->ai_api_key) {
            $aiService = new ConclusionesAIService($config->ai_api_key, $config->ai_modelo, $config->ai_prompt ?? '');
            $conclusiones = [
                'analisis' => $aiService->generarAnalisis($reporte['kpis'], $reporte['zonas'], $desde->translatedFormat('F Y')),
            ];
        }

        $reporte['config']       = $config;
        $reporte['conclusiones'] = $conclusiones;

        $filename   = 'informe_' . $desde->format('Y-m') . '.pdf';
        $pdfContent = $this->pdfService->fromView('modules.admin.reportes.pdf-presentacion', compact('reporte'));

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // ── Programados ────────────────────────────────────────────────────────

    public function storeProgramado(StoreReporteProgramadoRequest $request): RedirectResponse
    {
        $programado = $this->programadoService->create($request->validated());

        session()->flash('toast', [
            'message'     => 'Reporte programado creado.',
            'description' => "\"{$programado->nombre}\" quedó activo.",
            'variant'     => 'success',
        ]);

        return redirect()->route('admin.reportes.index', ['tab' => 'programados']);
    }

    public function updateProgramado(UpdateReporteProgramadoRequest $request, ReporteProgramado $programado): RedirectResponse
    {
        $programado = $this->programadoService->update($programado, $request->validated());

        session()->flash('toast', [
            'message'     => 'Cambios guardados.',
            'description' => "\"{$programado->nombre}\" fue actualizado.",
            'variant'     => 'success',
        ]);

        return redirect()->route('admin.reportes.index', ['tab' => 'programados']);
    }

    public function destroyProgramado(ReporteProgramado $programado): RedirectResponse
    {
        $nombre = $programado->nombre;
        $this->programadoService->delete($programado);

        session()->flash('toast', [
            'message'     => 'Reporte programado eliminado.',
            'description' => "\"{$nombre}\" fue removido. Los reportes históricos no se ven afectados.",
            'variant'     => 'destructive',
        ]);

        return redirect()->route('admin.reportes.index', ['tab' => 'programados']);
    }

    public function enviarAhoraProgramado(ReporteProgramado $programado): RedirectResponse
    {
        GenerarEnviarReporteJob::dispatch($programado->id);

        session()->flash('toast', [
            'message'     => 'Envío en cola.',
            'description' => 'El reporte se generará y enviará en los próximos minutos.',
            'variant'     => 'success',
        ]);

        return redirect()->route('admin.reportes.index', ['tab' => 'programados']);
    }

    // ── Configuración ──────────────────────────────────────────────────────

    public function updateConfiguracion(UpdateReporteConfiguracionRequest $request): RedirectResponse
    {
        $this->configuracionService->update($request->validated());

        session()->flash('toast', [
            'message'     => 'Configuración guardada.',
            'description' => 'Los cambios se aplican en el próximo reporte generado.',
            'variant'     => 'success',
        ]);

        return redirect()->route('admin.reportes.index', ['tab' => 'configuracion']);
    }

    // ── Destinatarios (autocomplete) ───────────────────────────────────────

    public function indexDestinatarios(Request $request): JsonResponse
    {
        $destinatarios = $this->destinatarioRepository->search($request->input('q', ''));

        return response()->json($destinatarios);
    }

    // ── Helpers privados ───────────────────────────────────────────────────

    private function generarReporte(Request $request): array
    {
        return $this->reporteService->generar(
            Carbon::parse($request->input('desde')),
            Carbon::parse($request->input('hasta')),
            array_filter($request->only(['zona_id', 'tipo_servicio_id', 'tipo_vehiculo_id']))
        );
    }


}
