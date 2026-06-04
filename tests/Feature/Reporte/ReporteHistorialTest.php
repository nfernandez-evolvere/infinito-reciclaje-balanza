<?php

namespace Tests\Feature\Reporte;

use App\Models\Organizacion;
use App\Models\ReporteGenerado;
use App\Models\Zona;
use App\Services\PdfService;
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
}
