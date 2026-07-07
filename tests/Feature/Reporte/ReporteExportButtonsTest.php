<?php

namespace Tests\Feature\Reporte;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReporteExportButtonsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function el_header_descarga_el_formato_v2(): void
    {
        $response = $this->actingAs($this->admin())
            ->get(route('admin.reportes.index', ['desde' => '2026-05-01', 'hasta' => '2026-05-31']))
            ->assertOk();

        // Los botones de descarga apuntan al generador v2. Las URLs viven en el
        // payload del componente seccionesExport (Js::from escapa las barras),
        // que arma el href según las secciones elegidas en el popover.
        $response->assertSee('seccionesExport', escape: false);
        $response->assertSee('excel-v2', escape: false);
        $response->assertSee('pdf-v2', escape: false);
    }
}
