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
use Mpdf\Mpdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    public function __construct(
        protected ReporteService $reporteService,
        protected ZonaRepository $zonaRepository,
        protected TipoVehiculoRepository $tipoVehiculoRepository,
        protected SvgChartService $svgChartService,
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
        $tab = $request->input('tab', 'generar');

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

        $svgEvolucion    = $this->svgChartService->barVertical($reporte['evolucion']['datos'], 720, 200);
        $svgVehiculosData = $reporte['vehiculos']->map(fn ($v) => [
            'nombre' => $v['nombre'],
            'valor'  => $v['viajes'],
            'color'  => '#1e3a5f',
        ])->all();
        $svgVehiculos = $this->svgChartService->barHorizontal($svgVehiculosData, 240, 180);

        $svgDensidadData = $reporte['zonas']
            ->filter(fn ($z) => $z['kg_ha'] !== null)
            ->sortByDesc('kg_ha')
            ->take(20)
            ->map(fn ($z) => ['nombre' => $z['nombre'] . ($z['turno'] ? ' ' . substr($z['turno'], 0, 1) : ''), 'valor' => $z['kg_ha']])
            ->values()
            ->all();
        $svgDensidad = $this->svgChartService->barHorizontal($svgDensidadData, 240, 320);

        $html = view('modules.admin.reportes.pdf-presentacion', compact(
            'reporte', 'svgEvolucion', 'svgVehiculos', 'svgDensidad'
        ))->render();

        $mpdf = new Mpdf([
            'format'        => 'A4-L',
            'margin_top'    => 0,
            'margin_bottom' => 0,
            'margin_left'   => 0,
            'margin_right'  => 0,
            'default_font'  => 'dejavusans',
            'tempDir'       => storage_path('app/mpdf-tmp'),
        ]);
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('informe_' . $desde->format('Y-m') . '.pdf', 'S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="informe_' . $desde->format('Y-m') . '.pdf"',
        ]);
    }

    // ── Programados ────────────────────────────────────────────────────────

    public function storeProgramado(StoreReporteProgramadoRequest $request): RedirectResponse
    {
        $programado = $this->programadoService->create($request->validated());

        session()->flash('toast', [
            'message'     => 'Reporte programado creado.',
            'description' => "\"{$programado->nombre}\" está activo y listo para enviar.",
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
            'variant'     => 'default',
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

    private function pdfResponse(string $html, string $filename): Response
    {
        $mpdf = new Mpdf([
            'format'        => 'A4',
            'margin_top'    => 10,
            'margin_bottom' => 10,
            'margin_left'   => 15,
            'margin_right'  => 15,
            'default_font'  => 'dejavusans',
            'tempDir'       => storage_path('app/mpdf-tmp'),
        ]);
        $mpdf->WriteHTML($html);

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
