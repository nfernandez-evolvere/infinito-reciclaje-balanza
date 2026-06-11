<?php

namespace Tests\Feature\Reporte;

use App\Models\Organizacion;
use App\Models\Pesaje;
use App\Models\ReporteConfiguracion;
use App\Models\ReporteGenerado;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use App\Models\Zona;
use App\Services\PdfService;
use App\Services\ReporteService;
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

    #[Test]
    public function exporting_excel_records_a_manual_history_entry(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.reportes.excel', ['desde' => '2026-03-01', 'hasta' => '2026-03-31']))
            ->assertOk();

        $this->assertDatabaseCount('reportes_generados', 1);

        $g = ReporteGenerado::sole();
        $this->assertSame('manual', $g->origen);
        $this->assertSame('excel', $g->formato);
        $this->assertSame('informe_mensual', $g->tipo);
        $this->assertSame('generado', $g->estado);
        $this->assertEquals($admin->id, $g->usuario_id);
        $this->assertSame('2026-03-01', $g->periodo_desde->toDateString());
        $this->assertSame('2026-03-31', $g->periodo_hasta->toDateString());
        $this->assertNull($g->filtros);
        $this->assertNull($g->reporte_programado_id);
    }

    #[Test]
    public function exporting_pdf_records_a_manual_history_entry_with_filters(): void
    {
        $admin = $this->admin();
        $zona = Zona::factory()->create();

        $this->mock(PdfService::class)
            ->shouldReceive('fromView')->once()->andReturn('%PDF-1.4 fake');

        $this->actingAs($admin)
            ->get(route('admin.reportes.pdf-presentacion', [
                'desde'   => '2026-03-01',
                'hasta'   => '2026-03-31',
                'zona_id' => $zona->id,
            ]))
            ->assertOk();

        $g = ReporteGenerado::sole();
        $this->assertSame('manual', $g->origen);
        $this->assertSame('pdf', $g->formato);
        $this->assertSame('informe_mensual', $g->tipo);
        $this->assertEquals(['zona_id' => $zona->id], $g->filtros);
    }

    #[Test]
    public function reopening_an_excel_entry_regenerates_the_xlsx_without_a_new_entry(): void
    {
        $admin = $this->admin();
        $g = $this->entrada(['usuario_id' => $admin->id, 'formato' => 'excel']);

        $content = $this->actingAs($admin)
            ->get(route('admin.reportes.historial.download', $g))
            ->assertOk()
            ->streamedContent();

        // Un .xlsx es un ZIP: firma "PK".
        $this->assertStringStartsWith('PK', $content);
        // Re-descargar no registra una entrada nueva.
        $this->assertDatabaseCount('reportes_generados', 1);
    }

    #[Test]
    public function reopening_a_pdf_entry_reuses_the_preserved_ai_narrative(): void
    {
        $admin = $this->admin();
        $g = $this->entrada([
            'usuario_id'   => $admin->id,
            'formato'      => 'pdf',
            'conclusiones' => 'Narrativa preservada del envío original.',
        ]);

        // El PDF se arma con la narrativa guardada, sin volver a llamar al modelo de IA.
        $this->mock(PdfService::class)
            ->shouldReceive('fromView')
            ->once()
            ->withArgs(fn ($view, $data) => ($data['reporte']['conclusiones']['analisis'] ?? null)
                === 'Narrativa preservada del envío original.')
            ->andReturn('%PDF-1.4 fake');

        $this->actingAs($admin)
            ->get(route('admin.reportes.historial.download', $g))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->assertDatabaseCount('reportes_generados', 1);
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
    public function exporting_excel_freezes_the_detalle_in_the_snapshot(): void
    {
        $tipo = TipoVehiculo::factory()->create(['nombre' => 'Compactador']);
        $vehiculo = Vehiculo::factory()->create(['tipo_vehiculo_id' => $tipo->id]);
        $zona = Zona::factory()->create(['nombre' => 'Zona Centro']);

        // Pesaje con tara/neto controlados: lo que debe quedar congelado tal cual,
        // aunque después se recalcule la tara del vehículo.
        Pesaje::factory()->create([
            'vehiculo_id'   => $vehiculo->id,
            'zona_id'       => $zona->id,
            'peso_bruto_kg' => 5800,
            'peso_tara_kg'  => 800,
            'peso_neto_kg'  => 5000,
            'estado'        => 'Cerrado',
            'created_at'    => '2026-03-10 08:00:00',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.reportes.excel', ['desde' => '2026-03-01', 'hasta' => '2026-03-31']))
            ->assertOk();

        $snapshot = ReporteGenerado::sole()->snapshot;

        $this->assertNotNull($snapshot);
        $this->assertCount(1, $snapshot['detalle']);
        $this->assertSame(5000, $snapshot['detalle'][0]['peso_neto_kg']);
        $this->assertSame(800, $snapshot['detalle'][0]['peso_tara_kg']);
        $this->assertSame(5800, $snapshot['detalle'][0]['peso_bruto_kg']);
        $this->assertSame(5000, $snapshot['kg_netos_total']);
        // El snapshot de Excel no arrastra el mapa de calor (no lo usa).
        $this->assertArrayNotHasKey('alertas', $snapshot);
    }

    #[Test]
    public function exporting_pdf_freezes_kpis_and_branding_in_the_snapshot(): void
    {
        ReporteConfiguracion::create([
            'municipalidad_nombre' => 'Municipio de Prueba',
            'ai_enabled'           => false,
            'ai_api_key'           => null,
        ]);

        Pesaje::factory()->create([
            'peso_neto_kg' => 4000,
            'estado'       => 'Cerrado',
            'created_at'   => '2026-03-05 09:00:00',
        ]);

        $this->mock(PdfService::class)
            ->shouldReceive('fromView')->once()->andReturn('%PDF-1.4 fake');

        $this->actingAs($this->admin())
            ->get(route('admin.reportes.pdf-presentacion', ['desde' => '2026-03-01', 'hasta' => '2026-03-31']))
            ->assertOk();

        $snapshot = ReporteGenerado::sole()->snapshot;

        $this->assertNotNull($snapshot);
        $this->assertSame(1, $snapshot['kpis']['total']);
        $this->assertSame('Municipio de Prueba', $snapshot['config']['municipalidad_nombre']);
        // El snapshot de PDF no arrastra pivots/detalle del Excel.
        $this->assertArrayNotHasKey('pivots', $snapshot);
        $this->assertSame([], $snapshot['detalle'] ?? []);
    }

    #[Test]
    public function reopening_a_pdf_entry_with_snapshot_uses_frozen_data_without_recomputing(): void
    {
        $g = $this->entrada([
            'usuario_id' => $this->admin()->id,
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

    #[Test]
    public function reopening_an_excel_entry_reuses_the_stored_snapshot_without_recomputing(): void
    {
        $admin = $this->admin();

        Pesaje::factory()->create([
            'peso_neto_kg' => 2500,
            'estado'       => 'Cerrado',
            'created_at'   => '2026-03-08 10:00:00',
        ]);

        // 1) Exportar deja la entrada con su snapshot.
        $this->actingAs($admin)
            ->get(route('admin.reportes.excel', ['desde' => '2026-03-01', 'hasta' => '2026-03-31']))
            ->assertOk();

        $g = ReporteGenerado::sole();
        $this->assertNotNull($g->snapshot);

        // 2) Re-descargar arma el xlsx desde el snapshot, sin volver a consultar pesajes.
        $this->mock(ReporteService::class)->shouldNotReceive('generar');

        $content = $this->actingAs($admin)
            ->get(route('admin.reportes.historial.download', $g))
            ->assertOk()
            ->streamedContent();

        $this->assertStringStartsWith('PK', $content);
        $this->assertDatabaseCount('reportes_generados', 1);
    }

    // ── Descarga por formato en entradas multi-formato (pdf+excel) ───────────

    #[Test]
    public function multiformat_entry_downloads_excel_when_formato_param_is_excel(): void
    {
        $admin = $this->admin();
        $g = $this->entrada(['usuario_id' => $admin->id, 'formato' => 'pdf+excel', 'origen' => 'programado']);

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
        $g = $this->entrada(['usuario_id' => $admin->id, 'formato' => 'pdf+excel', 'origen' => 'programado']);

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
