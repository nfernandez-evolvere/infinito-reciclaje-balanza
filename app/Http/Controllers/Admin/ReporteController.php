<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ReporteExcelExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExportReporteRequest;
use App\Http\Requests\Admin\StoreReporteProgramadoRequest;
use App\Http\Requests\Admin\UpdateReporteConfiguracionRequest;
use App\Http\Requests\Admin\UpdateReporteProgramadoRequest;
use App\Jobs\GenerarEnviarReporteJob;
use App\Models\Alerta;
use App\Models\ReporteConfiguracion;
use App\Models\ReporteGenerado;
use App\Models\ReporteProgramado;
use App\Repositories\ReporteConfiguracionRepository;
use App\Repositories\ReporteDestinatarioRepository;
use App\Repositories\ReporteGeneradoRepository;
use App\Repositories\ReporteProgramadoRepository;
use App\Repositories\TipoServicioRepository;
use App\Repositories\TipoVehiculoRepository;
use App\Repositories\ZonaRepository;
use App\Services\ConclusionesAIService;
use App\Services\PdfService;
use App\Services\ReporteConfiguracionService;
use App\Services\ReporteGeneradoService;
use App\Services\ReporteProgramadoService;
use App\Services\ReporteService;
use App\Services\SvgChartService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
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
        protected ReporteGeneradoRepository $generadoRepository,
        protected ReporteGeneradoService $generadoService,
    ) {}

    // ── Index ──────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'programados');

        $zonas = $this->zonaRepository->activos();
        $tiposServicio = $this->tipoServicioRepository->activos();
        $tiposVehiculo = $this->tipoVehiculoRepository->activos();
        $programados = $this->programadoRepository->allOrdered();
        $historial = $this->generadoRepository->paginarHistorial()->appends(['tab' => 'historial']);
        $config = $this->configuracionRepository->first() ?? new ReporteConfiguracion;

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
            'programados', 'historial', 'config'
        ));
    }

    // ── Exports ────────────────────────────────────────────────────────────

    public function exportExcel(ExportReporteRequest $request): StreamedResponse
    {
        $desde = Carbon::parse($request->input('desde'));
        $hasta = Carbon::parse($request->input('hasta'));
        $filtros = array_filter($request->only(['zona_id', 'tipo_servicio_id', 'tipo_vehiculo_id']));

        $this->generadoService->registrarDescarga('excel', 'informe_mensual', $desde, $hasta, $filtros);

        return $this->descargarExcel($desde, $hasta, $filtros);
    }

    public function exportPdfPresentacion(ExportReporteRequest $request): Response
    {
        $desde = Carbon::parse($request->input('desde'));
        $hasta = Carbon::parse($request->input('hasta'));
        $tipo = $request->input('tipo', 'informe_mensual');
        $filtros = array_filter($request->only(['zona_id', 'tipo_servicio_id', 'tipo_vehiculo_id']));

        $reporte = $this->construirReportePdf($desde, $hasta, $filtros, $tipo);

        $this->generadoService->registrarDescarga(
            'pdf', $tipo, $desde, $hasta, $filtros, $reporte['conclusiones']['analisis'] ?? null,
        );

        return $this->responderPdf($reporte, $tipo, $desde);
    }

    /**
     * Vuelve a generar y descargar un reporte del historial reusando el período,
     * los filtros y el formato guardados. No registra una entrada nueva: es una
     * re-descarga de algo ya producido. La narrativa IA preservada se reutiliza
     * tal cual (no se vuelve a llamar al modelo).
     */
    public function downloadHistorial(ReporteGenerado $generado): StreamedResponse|Response
    {
        $desde = $generado->periodo_desde->copy()->startOfDay();
        $hasta = $generado->periodo_hasta->copy()->endOfDay();
        $filtros = $generado->filtrosNormalizados();

        if ($generado->formato === 'excel') {
            return $this->descargarExcel($desde, $hasta, $filtros);
        }

        $reporte = $this->construirReportePdf($desde, $hasta, $filtros, $generado->tipo, $generado->conclusiones);

        return $this->responderPdf($reporte, $generado->tipo, $desde);
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

    public function downloadPdfProgramado(ReporteProgramado $programado): Response
    {
        [$desde, $hasta] = $this->calcularPeriodoProgramado($programado->frecuencia);
        $tipo = $programado->tipo;

        $reporte = $this->reporteService->generar($desde, $hasta);
        $config = $this->configuracionRepository->first();

        $reporte['config'] = $config;
        $reporte['conclusiones'] = [];

        if ($tipo === 'alertas') {
            $reporte['alertas'] = $this->consultarAlertas($desde, $hasta);
            $filename = 'alertas_'.$desde->format('Y-m-d').'.pdf';
        } else {
            $filename = 'informe_'.$desde->format('Y-m').'.pdf';
        }

        $pdfContent = $this->pdfService->fromView('modules.admin.reportes.pdf-presentacion', compact('reporte', 'tipo'));

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function downloadExcelProgramado(ReporteProgramado $programado): StreamedResponse
    {
        [$desde, $hasta] = $this->calcularPeriodoProgramado($programado->frecuencia);

        return $this->descargarExcel($desde, $hasta, []);
    }

    // ── Helpers privados ───────────────────────────────────────────────────

    /**
     * Arma y descarga el Excel municipal para un período/filtros dados.
     *
     * @param  array<string, int>  $filtros
     */
    private function descargarExcel(Carbon $desde, Carbon $hasta, array $filtros): StreamedResponse
    {
        $reporte = $this->reporteService->generar($desde, $hasta, $filtros);
        $reporte['config'] = $this->configuracionRepository->first();
        $reporte['pivots'] = $this->reporteService->pivotsParaExcel(
            $reporte['detalle'],
            $reporte['desde'],
            $reporte['hasta'],
        );

        $filename = 'reporte_'.$desde->format('Y-m-d').'_'.$hasta->format('Y-m-d').'.xlsx';

        return (new ReporteExcelExport($reporte))->download($filename);
    }

    /**
     * Arma el array del reporte para la vista PDF: KPIs/zonas/vehículos + config,
     * conclusiones (alertas o IA) y, para alertas, el listado deduplicado. Si se
     * pasa $conclusionesPreservadas, se reutiliza esa narrativa en vez de llamar
     * al modelo de IA (caso historial).
     *
     * @param  array<string, int>  $filtros
     * @return array<string, mixed>
     */
    private function construirReportePdf(
        Carbon $desde,
        Carbon $hasta,
        array $filtros,
        string $tipo,
        ?string $conclusionesPreservadas = null,
    ): array {
        $reporte = $this->reporteService->generar($desde, $hasta, $filtros);
        $config = $this->configuracionRepository->first();

        $reporte['config'] = $config;
        $reporte['conclusiones'] = [];

        if ($tipo === 'alertas') {
            $reporte['alertas'] = $this->consultarAlertas($desde, $hasta);

            return $reporte;
        }

        if ($conclusionesPreservadas !== null) {
            $reporte['conclusiones'] = ['analisis' => $conclusionesPreservadas];
        } elseif ($config?->ai_enabled && $config?->ai_api_key) {
            $aiService = new ConclusionesAIService($config->ai_api_key, $config->ai_modelo, $config->ai_prompt ?? '');
            $reporte['conclusiones'] = [
                'analisis' => $aiService->generarAnalisis($reporte['kpis'], $reporte['zonas'], $desde->translatedFormat('F Y')),
            ];
        }

        return $reporte;
    }

    /**
     * Renderiza el PDF de presentación y lo devuelve como descarga.
     *
     * @param  array<string, mixed>  $reporte
     */
    private function responderPdf(array $reporte, string $tipo, Carbon $desde): Response
    {
        $filename = $tipo === 'alertas'
            ? 'alertas_'.$desde->format('Y-m').'.pdf'
            : 'informe_'.$desde->format('Y-m').'.pdf';

        $pdfContent = $this->pdfService->fromView('modules.admin.reportes.pdf-presentacion', compact('reporte', 'tipo'));

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function calcularPeriodoProgramado(string $frecuencia): array
    {
        return match ($frecuencia) {
            'diaria'    => [now()->subDay()->startOfDay(),    now()->subDay()->endOfDay()],
            'semanal'   => [now()->subDays(7)->startOfDay(),  now()->endOfDay()],
            'quincenal' => [now()->subDays(15)->startOfDay(), now()->endOfDay()],
            default     => [now()->subDays(30)->startOfDay(), now()->endOfDay()],
        };
    }

    private function consultarAlertas(Carbon $desde, Carbon $hasta): Collection
    {
        return Alerta::whereDate('fecha_deteccion', '>=', $desde->toDateString())
            ->whereDate('fecha_deteccion', '<=', $hasta->toDateString())
            ->with(['zona'])
            ->orderBy('fecha_deteccion')
            ->orderBy('tipo')
            ->get()
            ->unique(fn ($a) => "{$a->tipo}|{$a->titulo}|{$a->fecha_deteccion->toDateString()}")
            ->values();
    }
}
