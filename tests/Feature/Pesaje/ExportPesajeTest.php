<?php

namespace Tests\Feature\Pesaje;

use App\Models\Pesaje;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExportPesajeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function export_returns_csv_with_bom_and_headers(): void
    {
        Pesaje::factory()->create();

        $response = $this->actingAs($this->admin())
            ->get(route('admin.pesajes.export'))
            ->assertOk();

        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $contenido = $response->streamedContent();

        // BOM UTF-8 al inicio.
        $this->assertStringStartsWith(chr(0xEF).chr(0xBB).chr(0xBF), $contenido);
        // Encabezados de columnas.
        $this->assertStringContainsString('Patente', $contenido);
        $this->assertStringContainsString('Neto (kg)', $contenido);
    }

    #[Test]
    public function operador_cannot_export(): void
    {
        $this->actingAs($this->operador())
            ->get(route('admin.pesajes.export'))
            ->assertForbidden();
    }

    #[Test]
    public function guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.pesajes.export'))
            ->assertRedirect(route('login'));
    }
}
