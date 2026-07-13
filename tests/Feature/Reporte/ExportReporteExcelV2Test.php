<?php

namespace Tests\Feature\Reporte;

use App\Models\Pesaje;
use App\Models\ReporteConfiguracion;
use App\Models\TipoServicio;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use App\Models\Zona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExportReporteExcelV2Test extends TestCase
{
    use RefreshDatabase;

    /**
     * Títulos de las hojas del xlsx descargado (el streamedContent se escribe a
     * un archivo temporal porque PhpSpreadsheet solo lee desde disco).
     *
     * @return list<string>
     */
    private function hojasDelXlsx(string $contenido): array
    {
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($tmp, $contenido);

        try {
            return IOFactory::load($tmp)->getSheetNames();
        } finally {
            unlink($tmp);
        }
    }

    /**
     * Siembra dos servicios con sus zonas y un vehículo con N° interno, con pesajes
     * en varios días de marzo: ejercita las tres hojas nuevas (Resumen, Por N°
     * interno, y el cruce servicio × tipo de vehículo).
     */
    private function sembrarPesajes(): void
    {
        $tipo = TipoVehiculo::factory()->create(['nombre' => 'Compactador']);
        $vehiculo = Vehiculo::factory()->create(['tipo_vehiculo_id' => $tipo->id, 'numero_interno' => '7042']);

        $domiciliario = TipoServicio::factory()->create(['nombre' => 'Domiciliario']);
        $voluminosos = TipoServicio::factory()->create(['nombre' => 'Voluminosos']);

        $zonaDom = Zona::factory()->create(['nombre' => 'Zona 1', 'tipo_servicio_id' => $domiciliario->id]);
        $zonaVol = Zona::factory()->create(['nombre' => 'Zona Norte', 'tipo_servicio_id' => $voluminosos->id]);

        Pesaje::factory()->create([
            'vehiculo_id'      => $vehiculo->id,
            'tipo_servicio_id' => $domiciliario->id,
            'zona_id'          => $zonaDom->id,
            'turno'            => 'Diurna',
            'peso_neto_kg'     => 5000,
            'estado'           => 'Cerrado',
            'created_at'       => '2026-03-10 08:00:00',
        ]);

        Pesaje::factory()->create([
            'vehiculo_id'      => $vehiculo->id,
            'tipo_servicio_id' => $domiciliario->id,
            'zona_id'          => $zonaDom->id,
            'turno'            => 'Nocturna',
            'peso_neto_kg'     => 3000,
            'estado'           => 'Cerrado',
            'created_at'       => '2026-03-12 20:00:00',
        ]);

        Pesaje::factory()->create([
            'vehiculo_id'      => $vehiculo->id,
            'tipo_servicio_id' => $voluminosos->id,
            'zona_id'          => $zonaVol->id,
            'turno'            => 'Diurna',
            'peso_neto_kg'     => 9000,
            'estado'           => 'Cerrado',
            'created_at'       => '2026-03-10 10:00:00',
        ]);
    }

    #[Test]
    public function admin_exports_a_valid_xlsx_v2(): void
    {
        $this->sembrarPesajes();

        $response = $this->actingAs($this->admin())
            ->get(route('admin.reportes.excel-v2', ['desde' => '2026-03-01', 'hasta' => '2026-03-31']))
            ->assertOk();

        $response->assertHeader(
            'Content-Type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $contenido = $response->streamedContent();

        // Un .xlsx es un ZIP: firma "PK" y cuerpo real.
        $this->assertStringStartsWith('PK', $contenido);
        $this->assertGreaterThan(2000, strlen($contenido));
    }

    #[Test]
    public function export_v2_works_without_pesajes_in_range(): void
    {
        // Sin datos el reporte v2 sigue generando un xlsx válido (encabezados en cero).
        $contenido = $this->actingAs($this->admin())
            ->get(route('admin.reportes.excel-v2', ['desde' => '2026-03-01', 'hasta' => '2026-03-31']))
            ->assertOk()
            ->streamedContent();

        $this->assertStringStartsWith('PK', $contenido);
    }

    #[Test]
    public function operador_cannot_export_v2(): void
    {
        $this->actingAs($this->operador())
            ->get(route('admin.reportes.excel-v2', ['desde' => '2026-03-01', 'hasta' => '2026-03-31']))
            ->assertForbidden();
    }

    // ── Secciones configurables (hojas del workbook) ─────────────────────

    #[Test]
    public function export_sin_configuracion_incluye_todas_las_hojas(): void
    {
        $this->sembrarPesajes();

        $contenido = $this->actingAs($this->admin())
            ->get(route('admin.reportes.excel-v2', ['desde' => '2026-03-01', 'hasta' => '2026-03-31']))
            ->assertOk()
            ->streamedContent();

        // Dos servicios sembrados (Domiciliario y Voluminosos, kg desc) → una hoja cada uno.
        $this->assertSame(
            ['Resumen', 'Por vehículo- N° Interno', 'Voluminosos', 'Domiciliario', 'Base de datos'],
            $this->hojasDelXlsx($contenido),
        );
    }

    #[Test]
    public function export_respeta_las_secciones_ad_hoc_del_request(): void
    {
        $this->sembrarPesajes();

        $contenido = $this->actingAs($this->admin())
            ->get(route('admin.reportes.excel-v2', [
                'desde'     => '2026-03-01',
                'hasta'     => '2026-03-31',
                'secciones' => ['resumen', 'base_datos'],
            ]))
            ->assertOk()
            ->streamedContent();

        $this->assertSame(['Resumen', 'Base de datos'], $this->hojasDelXlsx($contenido));
    }

    #[Test]
    public function export_usa_las_secciones_de_la_configuracion_general(): void
    {
        $this->sembrarPesajes();
        ReporteConfiguracion::create(['secciones' => ['excel' => ['por_interno']]]);

        $contenido = $this->actingAs($this->admin())
            ->get(route('admin.reportes.excel-v2', ['desde' => '2026-03-01', 'hasta' => '2026-03-31']))
            ->assertOk()
            ->streamedContent();

        $this->assertSame(['Por vehículo- N° Interno'], $this->hojasDelXlsx($contenido));
    }

    #[Test]
    public function export_con_secciones_invalidas_cae_a_la_hoja_resumen(): void
    {
        // El guard del workbook: nunca un xlsx sin hojas.
        $this->sembrarPesajes();

        $contenido = $this->actingAs($this->admin())
            ->get(route('admin.reportes.excel-v2', [
                'desde'     => '2026-03-01',
                'hasta'     => '2026-03-31',
                'secciones' => ['quienes_somos'], // clave de PDF: no es una hoja de Excel
            ]))
            ->assertOk()
            ->streamedContent();

        $this->assertSame(['Resumen'], $this->hojasDelXlsx($contenido));
    }
}
