<?php

namespace Tests\Feature\Reporte;

use App\Jobs\EnviarReporteJob;
use App\Mail\ReporteAlertaMail;
use App\Mail\ReporteMensualMail;
use App\Models\ReporteGenerado;
use App\Models\ReporteProgramado;
use App\Services\PdfService;
use App\Services\ReporteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * El job de envío renderiza SIEMPRE desde el snapshot congelado del registro
 * y envía a los destinatarios congelados en él: nunca recalcula datos, nunca
 * re-llama a la IA, y funciona aunque el programado haya sido eliminado.
 */
class EnviarReporteJobTest extends TestCase
{
    use RefreshDatabase;

    private function programado(array $overrides = []): ReporteProgramado
    {
        return ReporteProgramado::create(array_merge([
            'tipo'           => 'informe_mensual',
            'nombre'         => 'Informe mensual',
            'frecuencia'     => 'mensual',
            'cron_expresion' => '0 8 1 * *',
            'destinatarios'  => ['dest1@test.com', 'dest2@test.com'],
            'activo'         => true,
        ], $overrides));
    }

    /** Registro listo para enviar: estado 'enviando' con snapshot congelado. */
    private function generado(ReporteProgramado $programado, array $overrides = []): ReporteGenerado
    {
        return ReporteGenerado::create(array_merge([
            'organizacion_id'       => $programado->organizacion_id,
            'reporte_programado_id' => $programado->id,
            'origen'                => 'programado',
            'tipo'                  => $programado->tipo,
            'formato'               => 'pdf',
            'periodo_desde'         => '2026-05-01',
            'periodo_hasta'         => '2026-05-31',
            'destinatarios'         => $programado->destinatarios,
            'estado'                => ReporteGenerado::ESTADO_ENVIANDO,
            'snapshot'              => $this->snapshot(),
        ], $overrides));
    }

    /** Snapshot mínimo en su forma serializada (como lo persiste capturar()). */
    private function snapshot(array $overrides = []): array
    {
        return array_merge([
            'kpis'           => ['total' => 5, 'toneladas' => 12.5, 'dias_op' => 4, 'dias_rango' => 30, 'promedio_ton_dia' => 3.1, 'promedio_kg_viaje' => 2500],
            'evolucion'      => ['datos' => [], 'promedio' => 0, 'maximo' => 0, 'minimo' => 0],
            'zonas'          => [],
            'vehiculos'      => [],
            'mapaZonas'      => [],
            'kg_netos_total' => 0,
            'config'         => ['municipalidad_nombre' => 'Muni Test', 'intro_empresa' => null, 'servicios' => null],
            'conclusiones'   => [],
        ], $overrides);
    }

    /** Pivots serializados (arrays) con las claves que lee ReporteExcelExport. */
    private function pivotsSerializados(): array
    {
        $stat = ['total_viajes' => 0, 'total_kg' => 0, 'tipos' => []];

        return [
            'tipos'    => [],
            'diario'   => ['filas' => [], 'totales' => $stat, 'promedio' => $stat, 'maximo' => $stat, 'minimo' => $stat],
            'zonaTipo' => ['filas' => [], 'totales' => ['total_viajes' => 0, 'total_kg' => 0, 'tipos' => [], 'porcentaje' => 0.0]],
            'zonaDia'  => ['fechas' => [], 'filas' => [], 'totales' => ['dias' => [], 'total' => 0]],
        ];
    }

    private function mockPdf(): void
    {
        $this->instance(PdfService::class, \Mockery::mock(PdfService::class, function ($m) {
            $m->shouldReceive('fromView')->andReturn('fake-pdf-content');
        }));
    }

    #[Test]
    public function job_sends_mail_per_recipient_from_snapshot_and_marks_enviado(): void
    {
        Mail::fake();
        $this->mockPdf();

        $generado = $this->generado($this->programado());

        EnviarReporteJob::dispatchSync($generado->id);

        Mail::assertSent(ReporteMensualMail::class, 2);
        Mail::assertSent(ReporteMensualMail::class, fn ($m) => $m->hasTo('dest1@test.com') && $m->municipalidad === 'Muni Test');
        Mail::assertSent(ReporteMensualMail::class, fn ($m) => $m->hasTo('dest2@test.com'));

        $generado->refresh();
        $this->assertSame(ReporteGenerado::ESTADO_ENVIADO, $generado->estado);
        $this->assertNotNull($generado->enviado_at);
        $this->assertDatabaseCount('reportes_generados', 1);
    }

    #[Test]
    public function job_uses_recipients_frozen_in_record_not_current_programado_ones(): void
    {
        Mail::fake();
        $this->mockPdf();

        $programado = $this->programado(['destinatarios' => ['original@test.com']]);
        $generado = $this->generado($programado, ['destinatarios' => ['original@test.com']]);

        // El programado cambia DESPUÉS de la generación: el envío aprobado debe
        // respetar lo congelado al despachar, no la configuración actual.
        $programado->update(['destinatarios' => ['nuevo@test.com']]);

        EnviarReporteJob::dispatchSync($generado->id);

        Mail::assertSent(ReporteMensualMail::class, 1);
        Mail::assertSent(ReporteMensualMail::class, fn ($m) => $m->hasTo('original@test.com'));
        Mail::assertNotSent(ReporteMensualMail::class, fn ($m) => $m->hasTo('nuevo@test.com'));
    }

    #[Test]
    public function job_sends_report_even_when_programado_was_deleted(): void
    {
        Mail::fake();
        $this->mockPdf();

        $programado = $this->programado();
        $generado = $this->generado($programado);
        $programado->delete(); // FK nullOnDelete: el registro queda huérfano pero completo

        EnviarReporteJob::dispatchSync($generado->id);

        Mail::assertSent(ReporteMensualMail::class, 2);
        $this->assertSame(ReporteGenerado::ESTADO_ENVIADO, $generado->refresh()->estado);
    }

    #[Test]
    public function job_renders_attachments_according_to_record_formato(): void
    {
        Mail::fake();
        $this->mockPdf();

        $generado = $this->generado($this->programado(), [
            'formato'  => 'pdf+excel',
            'snapshot' => $this->snapshot([
                'pivots'  => $this->pivotsSerializados(),
                'detalle' => [],
            ]),
        ]);

        EnviarReporteJob::dispatchSync($generado->id);

        Mail::assertSent(ReporteMensualMail::class, function (ReporteMensualMail $m) {
            $mimes = array_column($m->adjuntos, 'mime');

            return count($m->adjuntos) === 2
                && in_array('application/pdf', $mimes, true)
                && collect($mimes)->contains(fn ($mime) => str_contains($mime, 'spreadsheetml'))
                && str_ends_with($m->adjuntos[0]['filename'], 'informe_2026-05.pdf')
                && str_ends_with($m->adjuntos[1]['filename'], 'informe_2026-05.xlsx');
        });
    }

    #[Test]
    public function job_reuses_snapshot_conclusions_without_calling_ai(): void
    {
        Mail::fake();
        Http::fake();

        $capturedReporte = null;
        $this->instance(PdfService::class, \Mockery::mock(PdfService::class, function ($m) use (&$capturedReporte) {
            $m->shouldReceive('fromView')->withArgs(function ($view, $data) use (&$capturedReporte) {
                $capturedReporte = $data['reporte'] ?? null;

                return true;
            })->andReturn('pdf');
        }));

        // El envío tampoco debe recalcular datos vivos.
        $this->instance(ReporteService::class, \Mockery::mock(ReporteService::class, function ($m) {
            $m->shouldReceive('generar')->never();
        }));

        $generado = $this->generado($this->programado(), [
            'conclusiones' => 'Narrativa congelada del snapshot.',
            'snapshot'     => $this->snapshot([
                'conclusiones' => ['analisis' => 'Narrativa congelada del snapshot.', 'modelo' => 'gemini-2.5-flash'],
            ]),
        ]);

        EnviarReporteJob::dispatchSync($generado->id);

        $this->assertSame('Narrativa congelada del snapshot.', $capturedReporte['conclusiones']['analisis']);
        Http::assertNothingSent(); // cero llamadas a la API de Gemini
    }

    #[Test]
    public function job_updates_programado_ultimo_envio_at_on_success(): void
    {
        Mail::fake();
        $this->mockPdf();

        $programado = $this->programado();
        $this->assertNull($programado->ultimo_envio_at);

        EnviarReporteJob::dispatchSync($this->generado($programado)->id);

        $this->assertNotNull($programado->refresh()->ultimo_envio_at);
    }

    #[Test]
    public function failed_marks_fallido_and_preserves_snapshot(): void
    {
        $generado = $this->generado($this->programado());

        (new EnviarReporteJob($generado->id))->failed(new \RuntimeException('SMTP timeout'));

        $generado->refresh();
        $this->assertSame(ReporteGenerado::ESTADO_FALLIDO, $generado->estado);
        $this->assertSame('SMTP timeout', $generado->error);
        // El snapshot se conserva: el reintento reenvía sin regenerar.
        $this->assertNotNull($generado->snapshot);
        $this->assertSame(5, $generado->snapshot['kpis']['total']);
        $this->assertDatabaseCount('reportes_generados', 1);
    }

    #[Test]
    public function job_builds_alert_mailable_with_count_from_snapshot(): void
    {
        Mail::fake();
        $this->mockPdf();

        $programado = $this->programado(['tipo' => 'alertas', 'destinatarios' => ['alerta@test.com']]);
        $generado = $this->generado($programado, [
            'tipo'     => 'alertas',
            'snapshot' => $this->snapshot([
                'alertas' => [
                    ['tipo' => 'gap_registro', 'titulo' => 'Sin actividad', 'descripcion' => null, 'fecha_deteccion' => '2026-05-10T08:00:00+00:00', 'leida' => false, 'zona_nombre' => null],
                    ['tipo' => 'peso_fuera_rango', 'titulo' => 'Peso fuera de rango', 'descripcion' => null, 'fecha_deteccion' => '2026-05-12T08:00:00+00:00', 'leida' => true, 'zona_nombre' => 'Centro'],
                ],
            ]),
        ]);

        EnviarReporteJob::dispatchSync($generado->id);

        Mail::assertSent(ReporteAlertaMail::class, 1);
        Mail::assertSent(ReporteAlertaMail::class, fn ($m) => $m->totalAlertas === 2
            && $m->hasTo('alerta@test.com')
            && str_ends_with($m->filename, 'alertas_2026-05.pdf'));
        Mail::assertNotSent(ReporteMensualMail::class);
    }

    #[Test]
    public function job_skips_when_record_is_not_in_enviando_state(): void
    {
        Mail::fake();
        $this->mockPdf();

        foreach ([ReporteGenerado::ESTADO_ENVIADO, ReporteGenerado::ESTADO_EN_REVISION, ReporteGenerado::ESTADO_DESCARTADO] as $estado) {
            $generado = $this->generado($this->programado(), ['estado' => $estado]);

            EnviarReporteJob::dispatchSync($generado->id);

            $this->assertSame($estado, $generado->refresh()->estado);
        }

        // Nunca duplicar un mail al municipio: cero envíos fuera de 'enviando'.
        Mail::assertNothingSent();
    }
}
