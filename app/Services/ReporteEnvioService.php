<?php

namespace App\Services;

use App\Exports\ReporteExcelExportV2;
use App\Mail\ReporteAlertaMail;
use App\Mail\ReporteMensualMail;
use App\Models\ReporteGenerado;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

/**
 * Única vía de envío de reportes programados. Renderiza SIEMPRE desde el
 * snapshot congelado del registro (nunca desde los pesajes vivos): lo que se
 * revisó/generó es bit a bit lo que se envía, y un reintento nunca re-llama
 * a la IA. Los destinatarios salen del registro (congelados al despachar),
 * así el envío funciona aunque el programado haya sido eliminado.
 */
class ReporteEnvioService
{
    public function __construct(
        protected PdfService $pdfService,
        protected ReporteSnapshotService $snapshotService,
        protected ReporteGeneradoService $generadoService,
    ) {}

    public function enviar(ReporteGenerado $generado): void
    {
        if ($generado->snapshot === null) {
            throw new RuntimeException('El reporte no tiene datos congelados para enviar.');
        }

        // Los informes mensuales se congelan en v2 (snapshot['version'] === 2);
        // alertas es el único tipo que sigue el camino v1.
        $reporte = ((int) ($generado->snapshot['version'] ?? 1) === 2)
            ? $this->snapshotService->rehidratarV2($generado)
            : $this->snapshotService->rehidratar($generado);

        $mailable = $this->construirMailable($generado, $reporte);

        foreach ($generado->destinatarios ?? [] as $email) {
            Mail::to($email)->send($mailable);
        }

        $this->generadoService->marcarEnviado($generado);

        // El envío real recién ocurrió: ultimo_envio_at se actualiza acá, no
        // al generar. Si el programado fue eliminado (FK nullOnDelete), se omite.
        $generado->programado()->withoutGlobalScopes()->first()?->update(['ultimo_envio_at' => now()]);
    }

    /**
     * @param  array<string, mixed>  $reporte
     */
    private function construirMailable(ReporteGenerado $generado, array $reporte): ReporteAlertaMail|ReporteMensualMail
    {
        $desde = $reporte['desde'];
        $periodo = ucfirst($desde->translatedFormat('F Y'));
        $municipalidad = $reporte['config']->municipalidad_nombre ?? 'Municipalidad';
        $tipo = $generado->tipo;

        if ($tipo === 'alertas') {
            return new ReporteAlertaMail(
                periodo: $periodo,
                municipalidad: $municipalidad,
                pdfContent: $this->pdfService->fromView('modules.admin.reportes.pdf-presentacion', compact('reporte', 'tipo')),
                filename: 'alertas_'.$desde->format('Y-m').'.pdf',
                totalAlertas: count($reporte['alertas'] ?? []),
            );
        }

        // El informe mensual siempre se congela en v2 (alertas es el único tipo
        // que sigue el camino v1, y siempre en PDF — ver construirMailable arriba).
        $adjuntos = [];

        foreach (explode('+', $generado->formato) as $formato) {
            $adjuntos[] = $formato === 'excel'
                ? [
                    'content'  => (new ReporteExcelExportV2($reporte))->contents(),
                    'filename' => 'reporte_'.$desde->format('Y-m').'.xlsx',
                    'mime'     => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]
                : [
                    'content'  => $this->pdfService->fromView('modules.admin.reportes.pdf-presentacion-v2', compact('reporte', 'tipo')),
                    'filename' => 'reporte_'.$desde->format('Y-m').'.pdf',
                    'mime'     => 'application/pdf',
                ];
        }

        return new ReporteMensualMail(
            periodo: $periodo,
            municipalidad: $municipalidad,
            adjuntos: $adjuntos,
        );
    }
}
