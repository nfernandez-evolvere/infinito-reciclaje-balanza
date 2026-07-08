<?php

namespace Tests\Feature\Reporte;

use App\Models\ReporteProgramado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DescargarExcelProgramadoTest extends TestCase
{
    use RefreshDatabase;

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
