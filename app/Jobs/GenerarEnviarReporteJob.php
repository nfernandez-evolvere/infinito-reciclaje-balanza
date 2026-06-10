<?php

namespace App\Jobs;

use App\Exports\ReporteExcelExport;
use App\Mail\ReporteAlertaMail;
use App\Mail\ReporteMensualMail;
use App\Models\Alerta;
use App\Models\Organizacion;
use App\Models\ReporteConfiguracion;
use App\Models\ReporteProgramado;
use App\Services\ConclusionesAIService;
use App\Services\DashboardService;
use App\Services\PdfService;
use App\Services\ReporteGeneradoService;
use App\Services\ReporteService;
use App\Services\ReporteSnapshotService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class GenerarEnviarReporteJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public readonly int $programadoId,
    ) {}

    public function handle(ReporteService $reporteService, PdfService $pdfService, ReporteGeneradoService $generadoService, DashboardService $dashboardService, ReporteSnapshotService $snapshotService): void
    {
        Log::info('GenerarEnviarReporteJob: iniciando', ['programado_id' => $this->programadoId]);

        $programado = ReporteProgramado::withoutGlobalScopes()->findOrFail($this->programadoId);
        $config = ReporteConfiguracion::withoutGlobalScopes()
            ->where('organizacion_id', $programado->organizacion_id)
            ->first();

        // El job corre fuera del ciclo HTTP: el middleware ResolveOrganizacion no se
        // ejecuta, por lo que app('organizacion') no está bound y el global scope de
        // BelongsToOrganizacion no filtra. Bindeamos aquí para que paraReporte() y
        // todas las queries de Pesaje, Zona, etc. queden acotadas a la organización.
        $organizacion = Organizacion::find($programado->organizacion_id);
        if ($organizacion) {
            app()->instance('organizacion', $organizacion);
        }

        [$desde, $hasta] = $this->calcularPeriodo($programado->frecuencia);

        $esAlertas = $programado->tipo === 'alertas';
        $tipo = $programado->tipo;
        $periodo = ucfirst($desde->translatedFormat('F Y'));

        $reporte = $reporteService->generar($desde, $hasta);
        $reporte['config'] = $config;
        $reporte['conclusiones'] = [];

        if ($esAlertas) {
            // Alertas únicas del período (una por evento, deduplicadas por titulo+fecha)
            $alertas = Alerta::withoutGlobalScopes()
                ->where('organizacion_id', $programado->organizacion_id)
                ->whereDate('fecha_deteccion', '>=', $desde->toDateString())
                ->whereDate('fecha_deteccion', '<=', $hasta->toDateString())
                ->with(['zona'])
                ->orderBy('fecha_deteccion')
                ->orderBy('tipo')
                ->get()
                ->unique(fn ($a) => "{$a->tipo}|{$a->titulo}|{$a->fecha_deteccion->toDateString()}")
                ->values();

            $reporte['alertas'] = $alertas;

            Log::info('GenerarEnviarReporteJob: generando PDF de alertas');
            $pdfContent = $pdfService->fromView('modules.admin.reportes.pdf-presentacion', compact('reporte', 'tipo'));

            $mailable = new ReporteAlertaMail(
                periodo: $periodo,
                municipalidad: $config?->municipalidad_nombre ?? 'Municipalidad',
                pdfContent: $pdfContent,
                filename: 'alertas_'.$desde->format('Y-m').'.pdf',
                totalAlertas: $alertas->count(),
            );
        } else {
            $formatos = $programado->formatos();
            $adjuntos = [];

            // PDF — solo si está seleccionado. Las conclusiones IA solo se usan
            // en el PDF, así que la llamada a la API se evita cuando no se genera.
            if (in_array('pdf', $formatos, true)) {
                Log::info('GenerarEnviarReporteJob: evaluando AI', [
                    'ai_enabled'     => $config?->ai_enabled,
                    'ai_api_key_set' => $config !== null && ! empty($config->ai_api_key),
                    'ai_modelo'      => $config?->ai_modelo,
                    'ai_prompt_set'  => $config !== null && ! empty($config->ai_prompt),
                ]);

                $analisisTexto = null;

                if ($config?->ai_enabled && $config?->ai_api_key) {
                    Log::info('GenerarEnviarReporteJob: llamando API de AI');
                    $ai = new ConclusionesAIService($config->ai_api_key, $config->ai_modelo ?? 'gemini-2.5-flash', $config->ai_prompt ?? '');
                    $analisisTexto = $ai->generarAnalisis($reporte['kpis'], $reporte['zonas'], $desde->translatedFormat('F Y'));
                    $reporte['conclusiones'] = [
                        'analisis' => $analisisTexto,
                        'modelo'   => $config->ai_modelo ?? 'gemini-2.5-flash',
                    ];
                    Log::info('GenerarEnviarReporteJob: AI completada', [
                        'analisis_chars' => strlen($analisisTexto),
                    ]);
                } else {
                    Log::info('GenerarEnviarReporteJob: AI omitida (deshabilitada o sin API key)');
                }

                // Mapa de calor por zona para las páginas de choropleth del PDF.
                $reporte['mapaZonas'] = $dashboardService->metricasPorZona($desde, $hasta);

                Log::info('GenerarEnviarReporteJob: generando PDF');
                $adjuntos[] = [
                    'content'  => $pdfService->fromView('modules.admin.reportes.pdf-presentacion', compact('reporte', 'tipo')),
                    'filename' => 'informe_'.$desde->format('Y-m').'.pdf',
                    'mime'     => 'application/pdf',
                ];
            }

            // Excel — reutiliza los pivots del reporte municipal. El detalle se
            // aplana a escalares: es lo que consume el export y lo que se congela
            // en el snapshot (preserva la tara/neto del momento).
            if (in_array('excel', $formatos, true)) {
                Log::info('GenerarEnviarReporteJob: generando Excel');
                $reporte['pivots'] = $reporteService->pivotsParaExcel($reporte['detalle'], $desde, $hasta);
                $reporte['kg_netos_total'] = (int) $reporte['detalle']->sum('peso_neto_kg');
                $reporte['detalle'] = $reporteService->detalleParaExcel($reporte['detalle']);
                $adjuntos[] = [
                    'content'  => (new ReporteExcelExport($reporte))->contents(),
                    'filename' => 'informe_'.$desde->format('Y-m').'.xlsx',
                    'mime'     => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ];
            }

            $mailable = new ReporteMensualMail(
                periodo: $periodo,
                municipalidad: $config?->municipalidad_nombre ?? 'Municipalidad',
                adjuntos: $adjuntos,
            );
        }

        Log::info('GenerarEnviarReporteJob: enviando email', ['destinatarios' => $programado->destinatarios]);
        foreach ($programado->destinatarios as $email) {
            Mail::to($email)->send($mailable);
        }

        // Actualizar timestamps
        $programado->update([
            'ultimo_envio_at'  => now(),
            'proximo_envio_at' => $this->calcularProximoEnvio($programado->frecuencia),
        ]);

        // Registrar el envío en el historial: preserva la narrativa IA si la hubo
        // y congela el snapshot para re-descargar el reporte idéntico al enviado.
        $generadoService->registrarEnvio(
            $programado,
            $desde,
            $hasta,
            $programado->destinatarios,
            $analisisTexto ?? null,
            $snapshotService->capturar($reporte),
        );

        Log::info('GenerarEnviarReporteJob: completado', ['programado_id' => $this->programadoId]);
    }

    /**
     * Tras agotar los reintentos, deja constancia del fallo en el historial para
     * que el admin vea que el envío programado no llegó (y por qué).
     */
    public function failed(Throwable $exception): void
    {
        $programado = ReporteProgramado::withoutGlobalScopes()->find($this->programadoId);

        if (! $programado) {
            return;
        }

        [$desde, $hasta] = $this->calcularPeriodo($programado->frecuencia);

        app(ReporteGeneradoService::class)->registrarFallo(
            $programado,
            $desde,
            $hasta,
            $exception->getMessage(),
        );
    }

    private function calcularPeriodo(string $frecuencia): array
    {
        return match ($frecuencia) {
            'diaria'    => [now()->subDay()->startOfDay(),    now()->subDay()->endOfDay()],
            'semanal'   => [now()->subDays(7)->startOfDay(),  now()->endOfDay()],
            'quincenal' => [now()->subDays(15)->startOfDay(), now()->endOfDay()],
            default     => [now()->subDays(30)->startOfDay(), now()->endOfDay()], // mensual
        };
    }

    private function calcularProximoEnvio(string $frecuencia): Carbon
    {
        return match ($frecuencia) {
            'diaria'    => now()->addDay()->setTime(8, 0),
            'semanal'   => now()->next(Carbon::MONDAY)->setTime(8, 0),
            'quincenal' => $this->proximoQuincenal(),
            default     => now()->addMonthNoOverflow()->startOfMonth()->setTime(8, 0), // mensual
        };
    }

    private function proximoQuincenal(): Carbon
    {
        return now()->day < 15
            ? now()->setDay(15)->setTime(8, 0)
            : now()->addMonthNoOverflow()->startOfMonth()->setTime(8, 0);
    }
}
