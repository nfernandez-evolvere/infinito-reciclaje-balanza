<?php

namespace App\Jobs;

use App\Mail\ReporteMensualMail;
use App\Models\ReporteConfiguracion;
use App\Models\ReporteProgramado;
use App\Services\ConclusionesAIService;
use App\Services\ReporteService;
use App\Services\SvgChartService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Mpdf\Mpdf;

class GenerarEnviarReporteJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public readonly int $programadoId,
    ) {}

    public function handle(ReporteService $reporteService, SvgChartService $svgChartService): void
    {
        $programado = ReporteProgramado::withoutGlobalScopes()->findOrFail($this->programadoId);
        $config     = ReporteConfiguracion::withoutGlobalScopes()
            ->where('organizacion_id', $programado->organizacion_id)
            ->first();

        [$desde, $hasta] = $this->calcularPeriodo($programado->opciones['periodo'] ?? 'mes_anterior');

        $reporte = $reporteService->generar($desde, $hasta);

        // IA (si está configurada)
        $conclusiones = [];
        if ($config?->ai_enabled && $config?->ai_api_key) {
            $ai = new ConclusionesAIService($config->ai_api_key, $config->ai_modelo ?? 'gemini-2.5-flash', $config->ai_prompt ?? '');
            $conclusiones = [
                'analisis' => $ai->generarAnalisis($reporte['kpis'], $reporte['zonas'], $desde->translatedFormat('F Y')),
            ];
        }

        $reporte['config']       = $config;
        $reporte['conclusiones'] = $conclusiones;

        // Generar SVGs
        $svgEvolucion   = $svgChartService->barVertical($reporte['evolucion']['datos'], 720, 200);
        $svgVehiculosData = $reporte['vehiculos']->map(fn ($v) => ['nombre' => $v['nombre'], 'valor' => $v['viajes']])->all();
        $svgVehiculos   = $svgChartService->barHorizontal($svgVehiculosData, 240, 180);
        $svgDensidadData = $reporte['zonas']->filter(fn ($z) => $z['kg_ha'] !== null)->sortByDesc('kg_ha')->take(20)
            ->map(fn ($z) => ['nombre' => $z['nombre'], 'valor' => $z['kg_ha']])->values()->all();
        $svgDensidad    = $svgChartService->barHorizontal($svgDensidadData, 240, 320);

        // Generar PDF
        $html = view('modules.admin.reportes.pdf-presentacion', compact(
            'reporte', 'svgEvolucion', 'svgVehiculos', 'svgDensidad'
        ))->render();

        $mpdf = new Mpdf([
            'format'       => 'A4-L',
            'margin_top'   => 0, 'margin_bottom' => 0,
            'margin_left'  => 0, 'margin_right'  => 0,
            'default_font' => 'dejavusans',
            'tempDir'      => storage_path('app/mpdf-tmp'),
        ]);
        $mpdf->WriteHTML($html);

        $filename   = 'informe_' . $desde->format('Y-m') . '.pdf';
        $pdfContent = $mpdf->Output($filename, 'S');

        // Enviar email
        $mailable = new ReporteMensualMail(
            periodo: ucfirst($desde->translatedFormat('F Y')),
            municipalidad: $config?->municipalidad_nombre ?? 'Municipalidad',
            pdfContent: $pdfContent,
            filename: $filename,
        );

        foreach ($programado->destinatarios as $email) {
            Mail::to($email)->send($mailable);
        }

        // Actualizar timestamps
        $programado->update([
            'ultimo_envio_at' => now(),
            'proximo_envio_at' => $this->calcularProximoEnvio($programado->cron_expresion),
        ]);
    }

    private function calcularPeriodo(string $periodo): array
    {
        return match($periodo) {
            'mes_actual'   => [now()->startOfMonth(), now()->endOfMonth()],
            'semana_actual' => [now()->startOfWeek(), now()->endOfWeek()],
            default        => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
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
