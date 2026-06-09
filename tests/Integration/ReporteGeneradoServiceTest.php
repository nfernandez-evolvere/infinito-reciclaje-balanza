<?php

namespace Tests\Integration;

use App\Jobs\GenerarEnviarReporteJob;
use App\Models\ReporteProgramado;
use App\Services\DashboardService;
use App\Services\PdfService;
use App\Services\ReporteGeneradoService;
use App\Services\ReporteService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReporteGeneradoServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): ReporteGeneradoService
    {
        return app(ReporteGeneradoService::class);
    }

    private function programado(array $attrs = []): ReporteProgramado
    {
        return ReporteProgramado::create([
            'tipo'           => 'informe_mensual',
            'nombre'         => 'Mensual municipio',
            'frecuencia'     => 'mensual',
            'cron_expresion' => '0 8 1 * *',
            'destinatarios'  => ['muni@x.gob'],
            'opciones'       => ['formatos' => ['pdf']],
            'activo'         => true,
            ...$attrs,
        ]);
    }

    #[Test]
    public function registrar_descarga_persists_a_manual_entry(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $g = $this->service()->registrarDescarga(
            'excel',
            'informe_mensual',
            Carbon::parse('2026-03-01'),
            Carbon::parse('2026-03-31'),
            ['zona_id' => 7],
        );

        $this->assertSame('manual', $g->origen);
        $this->assertSame('excel', $g->formato);
        $this->assertSame('informe_mensual', $g->tipo);
        $this->assertSame('generado', $g->estado);
        $this->assertEquals($admin->id, $g->usuario_id);
        $this->assertNull($g->reporte_programado_id);
        $this->assertNull($g->conclusiones);
        $this->assertEquals(['zona_id' => 7], $g->fresh()->filtros);
        $this->assertSame('2026-03-01', $g->periodo_desde->toDateString());
        $this->assertSame('2026-03-31', $g->periodo_hasta->toDateString());
    }

    #[Test]
    public function registrar_descarga_stores_null_filters_when_empty(): void
    {
        $this->actingAs($this->admin());

        $g = $this->service()->registrarDescarga(
            'pdf',
            'alertas',
            Carbon::parse('2026-03-01'),
            Carbon::parse('2026-03-31'),
            [],
        );

        $this->assertSame('alertas', $g->tipo);
        $this->assertNull($g->fresh()->filtros);
    }

    #[Test]
    public function registrar_envio_persists_a_programado_entry_with_recipients_and_narrative(): void
    {
        $programado = $this->programado(['opciones' => ['formatos' => ['pdf', 'excel']]]);

        $g = $this->service()->registrarEnvio(
            $programado,
            Carbon::parse('2026-02-01'),
            Carbon::parse('2026-02-28'),
            $programado->destinatarios,
            'Conclusión generada por IA.',
        );

        $this->assertSame('programado', $g->origen);
        $this->assertSame('enviado', $g->estado);
        $this->assertSame('pdf+excel', $g->formato);
        $this->assertEquals($programado->id, $g->reporte_programado_id);
        $this->assertEquals($programado->organizacion_id, $g->organizacion_id);
        $this->assertNull($g->usuario_id);
        $this->assertSame('Conclusión generada por IA.', $g->conclusiones);
        $this->assertSame(['muni@x.gob'], $g->fresh()->destinatarios);
    }

    #[Test]
    public function registrar_fallo_truncates_the_error_to_500_chars(): void
    {
        $programado = $this->programado();

        $g = $this->service()->registrarFallo(
            $programado,
            Carbon::parse('2026-02-01'),
            Carbon::parse('2026-02-28'),
            str_repeat('e', 600),
        );

        $this->assertSame('programado', $g->origen);
        $this->assertSame('fallido', $g->estado);
        $this->assertSame(500, mb_strlen((string) $g->error));
        $this->assertSame(['muni@x.gob'], $g->fresh()->destinatarios);
    }

    #[Test]
    public function the_job_records_an_enviado_entry_after_sending(): void
    {
        Mail::fake();
        $this->mock(PdfService::class)->shouldReceive('fromView')->andReturn('%PDF-1.4 fake');

        $programado = $this->programado();

        (new GenerarEnviarReporteJob($programado->id))->handle(
            app(ReporteService::class),
            app(PdfService::class),
            app(ReporteGeneradoService::class),
            app(DashboardService::class),
        );

        $this->assertDatabaseHas('reportes_generados', [
            'reporte_programado_id' => $programado->id,
            'origen'                => 'programado',
            'estado'                => 'enviado',
            'formato'               => 'pdf',
        ]);
    }
}
