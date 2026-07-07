<?php

namespace Tests\Feature\Reporte;

use App\Models\Pesaje;
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

    /**
     * Mockea solo PdfService::fromHtml (Browsershot/Chrome): fromView sigue siendo real,
     * así el Blade v2 se renderiza de verdad y un error de plantilla haría fallar el test,
     * pero sin abrir un navegador.
     */
    private function fakeChrome(): void
    {
        $this->partialMock(PdfService::class, function ($m) {
            $m->shouldReceive('fromHtml')->andReturn('%PDF-1.4 fake');
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
}
