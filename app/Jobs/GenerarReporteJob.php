<?php

namespace App\Jobs;

use App\Mail\ReportePendienteRevisionMail;
use App\Models\Alerta;
use App\Models\Organizacion;
use App\Models\ReporteConfiguracion;
use App\Models\ReporteGenerado;
use App\Models\ReporteProgramado;
use App\Repositories\UsuarioRepository;
use App\Services\ConclusionesAIService;
use App\Services\DashboardService;
use App\Services\ReporteGeneradoService;
use App\Services\ReporteService;
use App\Services\ReporteSnapshotService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Primera fase del pipeline de reportes programados: genera los datos del
 * período congelado en el registro, llama a la IA si corresponde y persiste
 * el snapshot. No envía nada: según la configuración de revisión, deja el
 * registro en revisión (aprobación manual) o despacha EnviarReporteJob.
 */
class GenerarReporteJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public readonly int $generadoId,
    ) {}

    public function handle(ReporteService $reporteService, ReporteGeneradoService $generadoService, DashboardService $dashboardService, ReporteSnapshotService $snapshotService, UsuarioRepository $usuarioRepository): void
    {
        Log::info('GenerarReporteJob: iniciando', ['generado_id' => $this->generadoId]);

        $generado = ReporteGenerado::withoutGlobalScopes()->findOrFail($this->generadoId);

        // Idempotencia entre attempts: si un intento previo completó la
        // generación pero falló justo en el dispatch del envío, solo se
        // re-despacha — nunca se regenera ni se re-llama a la IA.
        if ($generado->estado === ReporteGenerado::ESTADO_ENVIANDO) {
            EnviarReporteJob::dispatch($generado->id);

            return;
        }

        if ($generado->estado !== ReporteGenerado::ESTADO_GENERANDO) {
            Log::info('GenerarReporteJob: registro fuera de estado generando, se omite', [
                'generado_id' => $generado->id,
                'estado'      => $generado->estado,
            ]);

            return;
        }

        $programado = ReporteProgramado::withoutGlobalScopes()->find($generado->reporte_programado_id);

        if (! $programado) {
            $generadoService->marcarFallo($generado, 'El reporte programado fue eliminado.');

            return;
        }

        $config = ReporteConfiguracion::withoutGlobalScopes()
            ->where('organizacion_id', $generado->organizacion_id)
            ->first();

        // El job corre fuera del ciclo HTTP: el middleware ResolveOrganizacion no se
        // ejecuta, por lo que app('organizacion') no está bound y el global scope de
        // BelongsToOrganizacion no filtra. Bindeamos aquí para que paraReporte() y
        // todas las queries de Pesaje, Zona, etc. queden acotadas a la organización.
        $organizacion = Organizacion::find($generado->organizacion_id);
        if ($organizacion) {
            app()->instance('organizacion', $organizacion);
        }

        // Período congelado en el registro al despachar: un reintento días
        // después reproduce exactamente el mismo rango (nunca se recalcula
        // desde now()).
        $desde = $generado->periodo_desde->copy()->startOfDay();
        $hasta = $generado->periodo_hasta->copy()->endOfDay();

        $tipo = $generado->tipo;
        $reporte = $reporteService->generar($desde, $hasta);
        $reporte['config'] = $config;
        $reporte['conclusiones'] = [];
        $analisisTexto = null;

        if ($tipo === 'alertas') {
            // Alertas únicas del período (una por evento, deduplicadas por titulo+fecha)
            $reporte['alertas'] = Alerta::withoutGlobalScopes()
                ->where('organizacion_id', $generado->organizacion_id)
                ->whereDate('fecha_deteccion', '>=', $desde->toDateString())
                ->whereDate('fecha_deteccion', '<=', $hasta->toDateString())
                ->with(['zona'])
                ->orderBy('fecha_deteccion')
                ->orderBy('tipo')
                ->get()
                ->unique(fn ($a) => "{$a->tipo}|{$a->titulo}|{$a->fecha_deteccion->toDateString()}")
                ->values();
        } else {
            $formatos = explode('+', $generado->formato);

            // Las conclusiones IA solo se imprimen en el PDF, así que la
            // llamada a la API se evita cuando ese formato no está elegido.
            if (in_array('pdf', $formatos, true)) {
                Log::info('GenerarReporteJob: evaluando AI', [
                    'ai_enabled'     => $config?->ai_enabled,
                    'ai_api_key_set' => $config !== null && ! empty($config->ai_api_key),
                    'ai_modelo'      => $config?->ai_modelo,
                    'ai_prompt_set'  => $config !== null && ! empty($config->ai_prompt),
                ]);

                if ($config?->ai_enabled && $config->ai_api_key) {
                    Log::info('GenerarReporteJob: llamando API de AI');
                    $ai = new ConclusionesAIService($config->ai_api_key, $config->ai_modelo ?? 'gemini-2.5-flash', $config->ai_prompt ?? '');
                    $analisisTexto = $ai->generarAnalisis($reporte['kpis'], $reporte['zonas'], $desde->translatedFormat('F Y'));
                    $reporte['conclusiones'] = [
                        'analisis' => $analisisTexto,
                        'modelo'   => $config->ai_modelo ?? 'gemini-2.5-flash',
                    ];
                    Log::info('GenerarReporteJob: AI completada', [
                        'analisis_chars' => strlen($analisisTexto),
                    ]);
                } else {
                    Log::info('GenerarReporteJob: AI omitida (deshabilitada o sin API key)');
                }

                // Mapa de calor por zona para las páginas de choropleth del PDF.
                $reporte['mapaZonas'] = $dashboardService->metricasPorZona($desde, $hasta);
            }

            // Excel — reutiliza los pivots del reporte municipal. El detalle se
            // aplana a escalares: es lo que consume el export y lo que se congela
            // en el snapshot (preserva la tara/neto del momento).
            if (in_array('excel', $formatos, true)) {
                $reporte['pivots'] = $reporteService->pivotsParaExcel($reporte['detalle'], $desde, $hasta);
                $reporte['kg_netos_total'] = (int) $reporte['detalle']->sum('peso_neto_kg');
                $reporte['detalle'] = $reporteService->detalleParaExcel($reporte['detalle']);
            }
        }

        $snapshot = $snapshotService->capturar($reporte);

        if ($programado->requiereRevision($config)) {
            $generadoService->marcarEnRevision($generado, $analisisTexto, $snapshot);
            $this->notificarPendiente($generado, $programado, $usuarioRepository);
            Log::info('GenerarReporteJob: completado, pendiente de revisión', ['generado_id' => $generado->id]);

            return;
        }

        $generadoService->marcarListoParaEnvio($generado, $analisisTexto, $snapshot);

        // Última instrucción: si el dispatch falla, el retry del attempt entra
        // por el guard de idempotencia y solo re-despacha el envío.
        EnviarReporteJob::dispatch($generado->id);

        Log::info('GenerarReporteJob: completado, envío en cola', ['generado_id' => $generado->id]);
    }

    /**
     * Tras agotar los reintentos, marca el registro existente como fallido
     * (update in place, nunca una fila nueva): el admin lo ve en el historial
     * y puede reintentar la generación con el mismo período.
     */
    public function failed(Throwable $exception): void
    {
        $generado = ReporteGenerado::withoutGlobalScopes()->find($this->generadoId);

        if (! $generado) {
            return;
        }

        app(ReporteGeneradoService::class)->marcarFallo($generado, $exception->getMessage());
    }

    /**
     * Aviso a los admins de la organización de que hay un reporte esperando
     * aprobación. Best-effort: un SMTP caído no debe marcar como fallido un
     * reporte bien generado (el badge y el banner de la pantalla cubren la señal).
     */
    private function notificarPendiente(ReporteGenerado $generado, ReporteProgramado $programado, UsuarioRepository $usuarioRepository): void
    {
        try {
            $mailable = new ReportePendienteRevisionMail(
                nombreReporte: $programado->nombre,
                periodo: ucfirst($generado->periodo_desde->translatedFormat('F Y')),
                url: route('admin.reportes.index', ['tab' => 'historial']),
            );

            foreach ($usuarioRepository->adminsDeOrganizacion($generado->organizacion_id) as $admin) {
                Mail::to($admin->email)->send($mailable);
            }
        } catch (Throwable $e) {
            Log::warning('GenerarReporteJob: no se pudo notificar la revisión pendiente', [
                'generado_id' => $generado->id,
                'error'       => $e->getMessage(),
            ]);
        }
    }
}
