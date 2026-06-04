<?php

namespace App\Jobs;

use App\Mail\ReporteAlertaMail;
use App\Mail\ReporteMensualMail;
use App\Models\Alerta;
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
        } else {
            if ($config?->ai_enabled && $config?->ai_api_key) {
                $ai = new ConclusionesAIService($config->ai_api_key, $config->ai_modelo ?? 'gemini-2.5-flash', $config->ai_prompt ?? '');
                $reporte['conclusiones'] = [
                    'analisis' => $ai->generarAnalisis($reporte['kpis'], $reporte['zonas'], $desde->translatedFormat('F Y')),
                ];
            }
        }

        Log::info('GenerarEnviarReporteJob: generando PDF');
        $pdfContent = $pdfService->fromView('modules.admin.reportes.pdf-presentacion', compact('reporte', 'tipo'));

        if ($esAlertas) {
            $filename = 'alertas_'.$desde->format('Y-m').'.pdf';
            $mailable = new ReporteAlertaMail(
                periodo: $periodo,
                municipalidad: $config?->municipalidad_nombre ?? 'Municipalidad',
                pdfContent: $pdfContent,
                filename: $filename,
                totalAlertas: $reporte['alertas']->count(),
            );
        } else {
            $filename = 'informe_'.$desde->format('Y-m').'.pdf';
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
            'proximo_envio_at' => $this->calcularProximoEnvio($programado->frecuencia),
        ]);

        Log::info('GenerarEnviarReporteJob: completado', ['programado_id' => $this->programadoId]);
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
