<?php

namespace Tests\Feature\Reporte;

use App\Models\Pesaje;
use App\Models\ReporteGenerado;
use App\Models\TipoServicio;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use App\Models\Zona;
use App\Services\PdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReporteHistorialV2Test extends TestCase
{
    use RefreshDatabase;

    /** Mockea solo Browsershot; el Blade v2 se renderiza de verdad. */
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

        $dom = TipoServicio::factory()->create(['nombre' => 'Domiciliario', 'descripcion' => 'Residuos de los hogares.']);
        $zonaA = Zona::factory()->create(['nombre' => 'Zona 1', 'tipo_servicio_id' => $dom->id, 'hectareas' => 120, 'habitantes' => 6000]);
        $zonaB = Zona::factory()->create(['nombre' => 'Zona 2', 'tipo_servicio_id' => $dom->id, 'hectareas' => 90, 'habitantes' => 4000]);

        foreach ([['2026-05-04 08:00:00', $zonaA, 'Diurna', 5000], ['2026-05-12 20:00:00', $zonaB, 'Nocturna', 3000], ['2026-05-20 09:00:00', $zonaA, 'Diurna', 4200]] as [$fecha, $zona, $turno, $kg]) {
            Pesaje::factory()->create([
                'vehiculo_id' => $vehiculo->id, 'tipo_servicio_id' => $dom->id, 'zona_id' => $zona->id,
                'turno'       => $turno, 'peso_neto_kg' => $kg, 'estado' => 'Cerrado', 'created_at' => $fecha,
            ]);
        }
    }

    #[Test]
    public function excel_v2_queda_en_historial_y_se_redescarga(): void
    {
        $this->sembrar();
        $admin = $this->admin();

        // Descarga v2 → registra una entrada en el historial con snapshot version 2.
        $this->actingAs($admin)
            ->get(route('admin.reportes.excel-v2', ['desde' => '2026-05-01', 'hasta' => '2026-05-31']))
            ->assertOk();

        $generado = ReporteGenerado::query()->latest('id')->first();
        $this->assertNotNull($generado);
        $this->assertSame('excel', $generado->formato);
        $this->assertSame(2, $generado->snapshot['version']);

        // Re-descarga desde el historial → reproduce el xlsx desde el snapshot congelado.
        $contenido = $this->actingAs($admin)
            ->get(route('admin.reportes.historial.download', ['generado' => $generado, 'formato' => 'excel']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->streamedContent();

        $this->assertStringStartsWith('PK', $contenido);
    }

    #[Test]
    public function pdf_v2_queda_en_historial_y_se_redescarga(): void
    {
        $this->fakeChrome();
        $this->sembrar();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.reportes.pdf-v2', ['desde' => '2026-05-01', 'hasta' => '2026-05-31']))
            ->assertOk();

        $generado = ReporteGenerado::query()->latest('id')->first();
        $this->assertNotNull($generado);
        $this->assertSame('pdf', $generado->formato);
        $this->assertSame(2, $generado->snapshot['version']);

        // Re-descarga desde el historial → rehidrata el snapshot v2 y renderiza el PDF v2.
        $response = $this->actingAs($admin)
            ->get(route('admin.reportes.historial.download', ['generado' => $generado, 'formato' => 'pdf']));

        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
