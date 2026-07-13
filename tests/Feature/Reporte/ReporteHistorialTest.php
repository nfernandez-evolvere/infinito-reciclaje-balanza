<?php

namespace Tests\Feature\Reporte;

use App\Models\Organizacion;
use App\Models\ReporteGenerado;
use App\Services\PdfService;
use App\Services\ReporteService;
use App\Services\ReporteSnapshotService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReporteHistorialTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Crea una entrada de historial en la organización de test (la asigna el
     * trait BelongsToOrganizacion). Acepta overrides de cualquier campo.
     */
    private function entrada(array $attrs = []): ReporteGenerado
    {
        return ReporteGenerado::create([
            'origen'        => 'manual',
            'tipo'          => 'informe_mensual',
            'formato'       => 'excel',
            'periodo_desde' => '2026-03-01',
            'periodo_hasta' => '2026-03-31',
            'estado'        => 'generado',
            ...$attrs,
        ]);
    }

    /**
     * Snapshot v2 completo (bloques PDF + Excel) para una entrada 'pdf+excel',
     * igual al que arma GenerarReporteJob para el informe mensual.
     */
    private function snapshotV2ParaAmbosFormatos(): array
    {
        $desde = Carbon::parse('2026-03-01');
        $hasta = Carbon::parse('2026-03-31');
        $reporteService = app(ReporteService::class);

        $reporte = $reporteService->generar($desde, $hasta);
        $reporte['config'] = null;
        $reporte['conclusiones'] = [];
        $reporte['semanas'] = $reporteService->porSemana($reporte['detalle'], $desde, $hasta);
        $reporte['diaSemana'] = $reporteService->porDiaSemana($reporte['detalle']);
        $reporte['flotaActiva'] = $reporteService->vehiculosOperativos($reporte['detalle']);
        $reporte['porServicio'] = $reporteService->porServicio($reporte['detalle']);
        $reporte['zonasServicio'] = $reporteService->zonasPorServicio($reporte['detalle'], $desde, $hasta);
        $reporte['kg_netos_total'] = (int) $reporte['detalle']->sum('peso_neto_kg');
        $reporte['datosV2'] = $reporteService->datosExcelV2($reporte['detalle'], $desde, $hasta);
        $reporte['detalle'] = $reporteService->detalleParaExcel($reporte['detalle']);

        return app(ReporteSnapshotService::class)->capturarV2($reporte);
    }

    #[Test]
    public function historial_tab_lists_recorded_reports(): void
    {
        $admin = $this->admin();
        $this->entrada(['usuario_id' => $admin->id]);

        $this->actingAs($admin)
            ->get(route('admin.reportes.index', ['tab' => 'historial']))
            ->assertOk()
            ->assertSee('Descargado')
            ->assertSee('01/03/2026');
    }

    #[Test]
    public function admin_cannot_reopen_a_history_entry_from_another_organizacion(): void
    {
        $otra = Organizacion::create(['nombre' => 'Otra organización', 'activo' => true]);

        $ajeno = ReporteGenerado::withoutGlobalScopes()->create([
            'organizacion_id' => $otra->id,
            'origen'          => 'manual',
            'tipo'            => 'informe_mensual',
            'formato'         => 'excel',
            'periodo_desde'   => '2026-03-01',
            'periodo_hasta'   => '2026-03-31',
            'estado'          => 'generado',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.reportes.historial.download', $ajeno))
            ->assertNotFound();
    }

    #[Test]
    public function operador_cannot_reopen_history(): void
    {
        $g = $this->entrada();

        $this->actingAs($this->operador())
            ->get(route('admin.reportes.historial.download', $g))
            ->assertForbidden();
    }

    #[Test]
    public function guest_is_redirected_to_login(): void
    {
        $g = $this->entrada();

        $this->get(route('admin.reportes.historial.download', $g))
            ->assertRedirect(route('login'));
    }

    // ── Snapshot: congelar todo y re-descargar idéntico ──────────────────────

    #[Test]
    public function reopening_an_alertas_pdf_entry_uses_the_frozen_snapshot_without_recomputing(): void
    {
        // Alertas es el único tipo que sigue el camino v1 (sin version=2): el
        // snapshot se congela con capturar(), no capturarV2().
        $g = $this->entrada([
            'usuario_id' => $this->admin()->id,
            'tipo'       => 'alertas',
            'formato'    => 'pdf',
            'snapshot'   => [
                'kpis'         => ['total' => 42, 'toneladas' => 777.5, 'dias_op' => 5, 'dias_rango' => 30, 'promedio_ton_dia' => 1, 'promedio_kg_viaje' => 100],
                'config'       => ['municipalidad_nombre' => 'Muni Congelada', 'intro_empresa' => null, 'servicios' => null],
                'conclusiones' => ['analisis' => 'Análisis congelado del envío.'],
            ],
        ]);

        // Con snapshot, el reporte se rehidrata: ReporteService no recalcula nada.
        $this->mock(ReporteService::class)->shouldNotReceive('generar');

        $this->mock(PdfService::class)
            ->shouldReceive('fromView')
            ->once()
            ->withArgs(fn ($view, $data) => ($data['reporte']['kpis']['toneladas'] ?? null) === 777.5
                && ($data['reporte']['config']->municipalidad_nombre ?? null) === 'Muni Congelada'
                && ($data['reporte']['conclusiones']['analisis'] ?? null) === 'Análisis congelado del envío.')
            ->andReturn('%PDF-1.4 fake');

        $this->actingAs($this->admin())
            ->get(route('admin.reportes.historial.download', $g))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->assertDatabaseCount('reportes_generados', 1);
    }

    // ── Descarga por formato en entradas multi-formato (pdf+excel, v2) ───────

    #[Test]
    public function multiformat_entry_downloads_excel_when_formato_param_is_excel(): void
    {
        $admin = $this->admin();
        $g = $this->entrada([
            'usuario_id' => $admin->id,
            'formato'    => 'pdf+excel',
            'origen'     => 'programado',
            'snapshot'   => $this->snapshotV2ParaAmbosFormatos(),
        ]);

        $content = $this->actingAs($admin)
            ->get(route('admin.reportes.historial.download', ['generado' => $g, 'formato' => 'excel']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->streamedContent();

        $this->assertStringStartsWith('PK', $content);
    }

    #[Test]
    public function multiformat_entry_without_formato_param_returns_the_first_format(): void
    {
        $admin = $this->admin();
        $g = $this->entrada([
            'usuario_id' => $admin->id,
            'formato'    => 'pdf+excel',
            'origen'     => 'programado',
            'snapshot'   => $this->snapshotV2ParaAmbosFormatos(),
        ]);

        $this->mock(PdfService::class)
            ->shouldReceive('fromView')->once()->andReturn('%PDF-1.4 fake');

        $this->actingAs($admin)
            ->get(route('admin.reportes.historial.download', $g))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    #[Test]
    public function requesting_a_format_the_entry_did_not_produce_returns_404(): void
    {
        $admin = $this->admin();
        $g = $this->entrada(['usuario_id' => $admin->id, 'formato' => 'pdf']);

        $this->actingAs($admin)
            ->get(route('admin.reportes.historial.download', ['generado' => $g, 'formato' => 'excel']))
            ->assertNotFound();
    }

    #[Test]
    public function historial_renders_a_download_action_per_format(): void
    {
        $admin = $this->admin();
        $g = $this->entrada(['usuario_id' => $admin->id, 'formato' => 'pdf+excel', 'origen' => 'programado']);

        $html = $this->actingAs($admin)
            ->get(route('admin.reportes.index', ['tab' => 'historial']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Descargar PDF', $html);
        $this->assertStringContainsString('Descargar Excel', $html);
        $this->assertStringContainsString(
            route('admin.reportes.historial.download', ['generado' => $g, 'formato' => 'excel'], false),
            $html,
        );
    }
}
