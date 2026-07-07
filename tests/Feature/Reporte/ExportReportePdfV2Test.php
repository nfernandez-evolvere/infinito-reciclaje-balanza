<?php

namespace Tests\Feature\Reporte;

use App\Models\Pesaje;
use App\Models\ReporteConfiguracion;
use App\Models\TipoServicio;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use App\Models\Zona;
use App\Services\PdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExportReportePdfV2Test extends TestCase
{
    use RefreshDatabase;

    /** HTML del informe capturado por el fake de Chrome en la última descarga. */
    private string $htmlRenderizado = '';

    /**
     * Mockea solo PdfService::fromHtml (Browsershot/Chrome): fromView sigue siendo real,
     * así el Blade v2 se renderiza de verdad y un error de plantilla haría fallar el test,
     * pero sin abrir un navegador. El HTML recibido queda capturado para los asserts
     * de secciones.
     */
    private function fakeChrome(): void
    {
        $this->partialMock(PdfService::class, function ($m) {
            $m->shouldReceive('fromHtml')->andReturnUsing(function (string $html) {
                $this->htmlRenderizado = $html;

                return '%PDF-1.4 fake';
            });
        });
    }

    private function sembrar(): void
    {
        $tipo = TipoVehiculo::factory()->create(['nombre' => 'Compactador']);
        $vehiculo = Vehiculo::factory()->create(['tipo_vehiculo_id' => $tipo->id, 'numero_interno' => '7042']);

        $dom = TipoServicio::factory()->create(['nombre' => 'Domiciliario', 'descripcion' => 'Residuos comunes de los hogares.']);
        $zona = Zona::factory()->create(['nombre' => 'Zona 1', 'tipo_servicio_id' => $dom->id, 'hectareas' => 120, 'habitantes' => 6000]);

        Pesaje::factory()->create([
            'vehiculo_id' => $vehiculo->id, 'tipo_servicio_id' => $dom->id, 'zona_id' => $zona->id,
            'turno'       => 'Diurna', 'peso_neto_kg' => 5000, 'estado' => 'Cerrado', 'created_at' => '2026-05-04 08:00:00',
        ]);
        Pesaje::factory()->create([
            'vehiculo_id' => $vehiculo->id, 'tipo_servicio_id' => $dom->id, 'zona_id' => $zona->id,
            'turno'       => 'Nocturna', 'peso_neto_kg' => 3000, 'estado' => 'Cerrado', 'created_at' => '2026-05-20 20:00:00',
        ]);
    }

    #[Test]
    public function admin_exports_pdf_v2(): void
    {
        $this->fakeChrome();
        $this->sembrar();

        $response = $this->actingAs($this->admin())
            ->get(route('admin.reportes.pdf-v2', ['desde' => '2026-05-01', 'hasta' => '2026-05-31']));

        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    #[Test]
    public function pdf_v2_renders_without_pesajes(): void
    {
        // Rango sin datos: el Blade v2 debe renderizar igual (secciones en cero, sin errores).
        $this->fakeChrome();

        $this->actingAs($this->admin())
            ->get(route('admin.reportes.pdf-v2', ['desde' => '2026-05-01', 'hasta' => '2026-05-31']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    #[Test]
    public function operador_cannot_export_pdf_v2(): void
    {
        $this->actingAs($this->operador())
            ->get(route('admin.reportes.pdf-v2', ['desde' => '2026-05-01', 'hasta' => '2026-05-31']))
            ->assertForbidden();
    }

    // ── Secciones configurables (páginas del informe) ────────────────────

    #[Test]
    public function pdf_completo_incluye_todas_las_paginas(): void
    {
        $this->fakeChrome();
        $this->sembrar();

        $this->actingAs($this->admin())
            ->get(route('admin.reportes.pdf-v2', ['desde' => '2026-05-01', 'hasta' => '2026-05-31']))
            ->assertOk();

        foreach (['Quiénes somos', 'Resumen ejecutivo', '¿Cuánto ingresa por semana?', 'Recolección según el día', 'Composición por tipo de vehículo', '¿Qué es cada servicio?', '¿Cuánto recolecta cada servicio?', 'Resumen del período', 'Análisis por flota', 'Zonas y servicios'] as $titulo) {
            $this->assertStringContainsString($titulo, $this->htmlRenderizado);
        }

        // Total declarado en el pie: portada + quiénes somos + 3 separadores + 6 páginas
        // de contenido + zonas del único servicio + cierre = 13.
        $this->assertStringContainsString('Pág. 01 / 13', $this->htmlRenderizado);
    }

    #[Test]
    public function pdf_omite_las_paginas_deshabilitadas_y_renumera(): void
    {
        $this->fakeChrome();
        $this->sembrar();

        $this->actingAs($this->admin())
            ->get(route('admin.reportes.pdf-v2', [
                'desde'     => '2026-05-01',
                'hasta'     => '2026-05-31',
                'secciones' => ['resumen_ejecutivo'],
            ]))
            ->assertOk();

        $html = $this->htmlRenderizado;

        $this->assertStringContainsString('Resumen ejecutivo', $html);
        $this->assertStringContainsString('Resumen del período', $html); // separador de su grupo

        $this->assertStringNotContainsString('Quiénes somos', $html);
        $this->assertStringNotContainsString('¿Cuánto ingresa por semana?', $html);
        $this->assertStringNotContainsString('Composición por tipo de vehículo', $html);
        $this->assertStringNotContainsString('Análisis por flota', $html);   // separador sin grupo activo
        $this->assertStringNotContainsString('Zonas y servicios', $html);    // ídem

        // Total: portada + separador + resumen ejecutivo + cierre. El eyebrow se
        // renumera correlativo (la portada es 01 → resumen ejecutivo pasa a 02).
        $this->assertStringContainsString('Pág. 01 / 04', $html);
        $this->assertStringContainsString('02 · Indicadores clave', $html);
    }

    #[Test]
    public function pdf_usa_las_secciones_de_la_configuracion_general(): void
    {
        $this->fakeChrome();
        $this->sembrar();
        ReporteConfiguracion::create(['secciones' => ['pdf' => ['quienes_somos']]]);

        $this->actingAs($this->admin())
            ->get(route('admin.reportes.pdf-v2', ['desde' => '2026-05-01', 'hasta' => '2026-05-31']))
            ->assertOk();

        $html = $this->htmlRenderizado;

        $this->assertStringContainsString('Quiénes somos', $html);
        $this->assertStringNotContainsString('Resumen ejecutivo', $html);
        $this->assertStringNotContainsString('Resumen del período', $html);

        // Portada + quiénes somos + cierre.
        $this->assertStringContainsString('Pág. 01 / 03', $html);
    }
}
