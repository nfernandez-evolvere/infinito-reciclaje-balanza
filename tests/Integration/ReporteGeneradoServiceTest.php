<?php

namespace Tests\Integration;

use App\Models\ReporteGenerado;
use App\Models\ReporteProgramado;
use App\Services\ReporteGeneradoService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
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

    private function generado(array $attrs = []): ReporteGenerado
    {
        return ReporteGenerado::create([
            'origen'        => 'programado',
            'tipo'          => 'informe_mensual',
            'formato'       => 'pdf',
            'periodo_desde' => '2026-05-01',
            'periodo_hasta' => '2026-05-31',
            'destinatarios' => ['muni@x.gob'],
            'estado'        => ReporteGenerado::ESTADO_GENERANDO,
            ...$attrs,
        ]);
    }

    // ── registrarDescarga (sin cambios de comportamiento) ─────────────

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

    // ── transiciones desde los jobs ───────────────────────────────────

    #[Test]
    public function marcar_en_revision_freezes_snapshot_and_narrative(): void
    {
        $g = $this->generado();

        $ok = $this->service()->marcarEnRevision($g, 'Narrativa IA.', ['kpis' => ['total' => 7]]);

        $this->assertTrue($ok);
        $g->refresh();
        $this->assertSame(ReporteGenerado::ESTADO_EN_REVISION, $g->estado);
        $this->assertSame('Narrativa IA.', $g->conclusiones);
        $this->assertSame(7, $g->snapshot['kpis']['total']);
    }

    #[Test]
    public function marcar_listo_para_envio_transitions_to_enviando(): void
    {
        $g = $this->generado();

        $ok = $this->service()->marcarListoParaEnvio($g, null, ['kpis' => ['total' => 2]]);

        $this->assertTrue($ok);
        $g->refresh();
        $this->assertSame(ReporteGenerado::ESTADO_ENVIANDO, $g->estado);
        $this->assertNull($g->conclusiones);
        $this->assertSame(2, $g->snapshot['kpis']['total']);
    }

    #[Test]
    public function marcar_enviado_sets_enviado_at(): void
    {
        $g = $this->generado(['estado' => ReporteGenerado::ESTADO_ENVIANDO]);

        $ok = $this->service()->marcarEnviado($g);

        $this->assertTrue($ok);
        $g->refresh();
        $this->assertSame(ReporteGenerado::ESTADO_ENVIADO, $g->estado);
        $this->assertNotNull($g->enviado_at);
    }

    #[Test]
    public function marcar_fallo_truncates_the_error_to_500_chars_in_place(): void
    {
        $g = $this->generado(['estado' => ReporteGenerado::ESTADO_ENVIANDO]);

        $ok = $this->service()->marcarFallo($g, str_repeat('e', 600));

        $this->assertTrue($ok);
        $this->assertDatabaseCount('reportes_generados', 1);
        $g->refresh();
        $this->assertSame(ReporteGenerado::ESTADO_FALLIDO, $g->estado);
        $this->assertSame(500, mb_strlen((string) $g->error));
    }

    // ── lock optimista ────────────────────────────────────────────────

    #[Test]
    public function transitions_fail_from_an_unexpected_state(): void
    {
        Queue::fake();

        // El registro ya está enviado: ninguna transición debe aplicar.
        $g = $this->generado(['estado' => ReporteGenerado::ESTADO_ENVIADO]);

        $this->assertFalse($this->service()->marcarEnRevision($g, null, []));
        $this->assertFalse($this->service()->marcarEnviado($g));
        $this->assertFalse($this->service()->marcarFallo($g, 'x'));
        $this->assertFalse($this->service()->aprobar($g, $this->admin()));
        $this->assertFalse($this->service()->descartar($g, $this->admin()));
        $this->assertFalse($this->service()->reintentar($g));

        Queue::assertNothingPushed();
        $this->assertSame(ReporteGenerado::ESTADO_ENVIADO, $g->fresh()->estado);
    }

    // ── edición de narrativa ──────────────────────────────────────────

    #[Test]
    public function actualizar_conclusiones_updates_column_and_snapshot_preserving_original(): void
    {
        $g = $this->generado([
            'estado'       => ReporteGenerado::ESTADO_EN_REVISION,
            'conclusiones' => 'Original IA.',
            'snapshot'     => ['kpis' => ['total' => 1], 'conclusiones' => ['analisis' => 'Original IA.', 'modelo' => 'gemini-2.5-flash']],
        ]);

        $this->assertTrue($this->service()->actualizarConclusiones($g, 'Corregido.'));

        $g->refresh();
        $this->assertSame('Corregido.', $g->conclusiones);
        $this->assertSame('Corregido.', $g->snapshot['conclusiones']['analisis']);
        $this->assertSame('Original IA.', $g->snapshot['conclusiones']['original']);
        $this->assertSame('gemini-2.5-flash', $g->snapshot['conclusiones']['modelo']);
        $this->assertSame(1, $g->snapshot['kpis']['total']); // el resto del snapshot no se toca

        // La segunda edición no pisa el original.
        $this->assertTrue($this->service()->actualizarConclusiones($g, 'Final.'));
        $this->assertSame('Original IA.', $g->refresh()->snapshot['conclusiones']['original']);
    }
}
