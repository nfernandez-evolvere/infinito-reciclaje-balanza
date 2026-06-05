<?php

namespace Tests\Feature\Reporte;

use App\Models\Pesaje;
use App\Models\ReporteProgramado;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use App\Models\Zona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExportReporteExcelTest extends TestCase
{
    use RefreshDatabase;

    /** Siembra un par de pesajes con tipo, zona y turno dentro del rango de marzo. */
    private function sembrarPesajes(): void
    {
        $tipo = TipoVehiculo::factory()->create(['nombre' => 'Compactador']);
        $vehiculo = Vehiculo::factory()->create(['tipo_vehiculo_id' => $tipo->id]);
        $zona = Zona::factory()->create(['nombre' => 'Zona Centro']);

        Pesaje::factory()->create([
            'vehiculo_id'  => $vehiculo->id,
            'zona_id'      => $zona->id,
            'turno'        => 'Diurna',
            'peso_neto_kg' => 5000,
            'estado'       => 'Cerrado',
            'created_at'   => '2026-03-10 08:00:00',
        ]);

        Pesaje::factory()->create([
            'vehiculo_id'  => $vehiculo->id,
            'zona_id'      => $zona->id,
            'turno'        => 'Nocturna',
            'peso_neto_kg' => 3000,
            'estado'       => 'Cerrado',
            'created_at'   => '2026-03-12 20:00:00',
        ]);
    }

    #[Test]
    public function admin_exports_a_valid_xlsx(): void
    {
        $this->sembrarPesajes();

        $response = $this->actingAs($this->admin())
            ->get(route('admin.reportes.excel', ['desde' => '2026-03-01', 'hasta' => '2026-03-31']))
            ->assertOk();

        $response->assertHeader(
            'Content-Type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $contenido = $response->streamedContent();

        // Un .xlsx es un archivo ZIP: debe empezar con la firma "PK" y tener cuerpo real.
        $this->assertStringStartsWith('PK', $contenido);
        $this->assertGreaterThan(2000, strlen($contenido));
    }

    #[Test]
    public function export_works_without_pesajes_in_range(): void
    {
        // Sin datos el reporte sigue generando un xlsx válido (encabezados + filas en cero).
        $contenido = $this->actingAs($this->admin())
            ->get(route('admin.reportes.excel', ['desde' => '2026-03-01', 'hasta' => '2026-03-31']))
            ->assertOk()
            ->streamedContent();

        $this->assertStringStartsWith('PK', $contenido);
    }

    #[Test]
    public function operador_cannot_export(): void
    {
        $this->actingAs($this->operador())
            ->get(route('admin.reportes.excel', ['desde' => '2026-03-01', 'hasta' => '2026-03-31']))
            ->assertForbidden();
    }

    #[Test]
    public function guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.reportes.excel', ['desde' => '2026-03-01', 'hasta' => '2026-03-31']))
            ->assertRedirect(route('login'));
    }

    private function programado(): ReporteProgramado
    {
        return ReporteProgramado::create([
            'tipo'           => 'informe_mensual',
            'nombre'         => 'Mensual municipio',
            'frecuencia'     => 'mensual',
            'cron_expresion' => '0 8 1 * *',
            'destinatarios'  => ['muni@x.gob'],
            'opciones'       => ['formatos' => ['pdf', 'excel']],
            'activo'         => true,
        ]);
    }

    #[Test]
    public function admin_downloads_a_valid_xlsx_for_a_programado(): void
    {
        $contenido = $this->actingAs($this->admin())
            ->get(route('admin.reportes.programados.excel', $this->programado()))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->streamedContent();

        $this->assertStringStartsWith('PK', $contenido);
    }

    #[Test]
    public function operador_cannot_download_programado_excel(): void
    {
        $this->actingAs($this->operador())
            ->get(route('admin.reportes.programados.excel', $this->programado()))
            ->assertForbidden();
    }
}
