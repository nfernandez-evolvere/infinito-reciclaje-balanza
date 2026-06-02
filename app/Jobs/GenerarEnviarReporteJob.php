<?php

namespace App\Jobs;

use App\Mail\ReporteAlertaMail;
use App\Mail\ReporteMensualMail;
use App\Models\ReporteConfiguracion;
use App\Models\ReporteProgramado;
use App\Services\ConclusionesAIService;
use App\Services\PdfService;
use App\Services\ReporteService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GenerarEnviarReporteJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public readonly int $programadoId,
    ) {}

    public function handle(ReporteService $reporteService, PdfService $pdfService): void
    {
        Log::info('GenerarEnviarReporteJob: iniciando', ['programado_id' => $this->programadoId]);

        $programado = ReporteProgramado::withoutGlobalScopes()->findOrFail($this->programadoId);
        $config = ReporteConfiguracion::withoutGlobalScopes()
            ->where('organizacion_id', $programado->organizacion_id)
            ->first();

        [$desde, $hasta] = $this->calcularPeriodo($programado->opciones['periodo'] ?? 'mes_anterior');

        $esAlertas = $programado->tipo === 'alertas';
        $filtros = $esAlertas ? ['solo_alerta' => true] : [];

        $reporte = $reporteService->generar($desde, $hasta, $filtros);

        $conclusiones = [];
        if (! $esAlertas && $config?->ai_enabled && $config?->ai_api_key) {
            $ai = new ConclusionesAIService($config->ai_api_key, $config->ai_modelo ?? 'gemini-2.5-flash', $config->ai_prompt ?? '');
            $conclusiones = [
                'analisis' => $ai->generarAnalisis($reporte['kpis'], $reporte['zonas'], $desde->translatedFormat('F Y')),
            ];
        }

        $reporte['config'] = $config;
        $reporte['conclusiones'] = $conclusiones;

        $periodo = ucfirst($desde->translatedFormat('F Y'));
        $tipo = $programado->tipo;

        Log::info('GenerarEnviarReporteJob: generando PDF');
        if ($esAlertas) {
            $filename = 'alertas_'.$desde->format('Y-m').'.pdf';
            $pdfContent = $pdfService->fromView('modules.admin.reportes.pdf-presentacion', compact('reporte', 'tipo'));
            $mailable = new ReporteAlertaMail(
                periodo: $periodo,
                municipalidad: $config?->municipalidad_nombre ?? 'Municipalidad',
                pdfContent: $pdfContent,
                filename: $filename,
                totalAlertas: $reporte['kpis']['total'],
            );
        } else {
            $filename = 'informe_'.$desde->format('Y-m').'.pdf';
            $pdfContent = $pdfService->fromView('modules.admin.reportes.pdf-presentacion', compact('reporte', 'tipo'));
            $mailable = new ReporteMensualMail(
                periodo: $periodo,
                municipalidad: $config?->municipalidad_nombre ?? 'Municipalidad',
                pdfContent: $pdfContent,
                filename: $filename,
            );
        }

        Log::info('GenerarEnviarReporteJob: enviando email', ['destinatarios' => $programado->destinatarios]);
        foreach ($programado->destinatarios as $email) {
            Mail::to($email)->send($mailable);
        }

        // Actualizar timestamps
        $programado->update([
            'ultimo_envio_at'  => now(),
            'proximo_envio_at' => $this->calcularProximoEnvio($programado->cron_expresion),
        ]);

        Log::info('GenerarEnviarReporteJob: completado', ['programado_id' => $this->programadoId]);
    }

    private function calcularPeriodo(string $periodo): array
    {
        return match ($periodo) {
            'mes_actual'    => [now()->startOfMonth(), now()->endOfMonth()],
            'semana_actual' => [now()->startOfWeek(), now()->endOfWeek()],
            default         => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
        };
    }

    private function calcularProximoEnvio(string $cron): Carbon
    {
        // Parseo básico de cron: "0 8 1 * *" = día 1 de cada mes a las 8am
        $parts = explode(' ', $cron);
        try {
            return Carbon::parse(now()->addMonth()->startOfMonth()->setTime((int) ($parts[1] ?? 8), 0));
        } catch (\Throwable) {
            return now()->addMonth();
        }
    }
}
