<?php

namespace Tests\Feature\Pesaje;

use App\Models\Pesaje;
use App\Models\TipoServicio;
use App\Models\Zona;
use App\Services\PesajeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PesajeLogTest extends TestCase
{
    use RefreshDatabase;

    // ── Acceso ────────────────────────────────────────────────────────

    #[Test]
    public function guest_is_redirected_to_login(): void
    {
        $pesaje = Pesaje::factory()->create();

        $this->get(route('pesajes.log', $pesaje))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function operador_can_fetch_log(): void
    {
        $pesaje = Pesaje::factory()->create();

        $this->actingAs($this->operador())
            ->get(route('pesajes.log', $pesaje))
            ->assertOk()
            ->assertJsonStructure([]);
    }

    #[Test]
    public function admin_can_fetch_log(): void
    {
        $pesaje = Pesaje::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('pesajes.log', $pesaje))
            ->assertOk();
    }

    // ── Contenido vacío ───────────────────────────────────────────────

    #[Test]
    public function log_returns_empty_array_when_no_entries(): void
    {
        $pesaje = Pesaje::factory()->create();

        $response = $this->actingAs($this->operador())
            ->get(route('pesajes.log', $pesaje));

        $response->assertOk()->assertExactJson([]);
    }

    // ── Estructura del log ────────────────────────────────────────────

    #[Test]
    public function log_returns_grouped_entries_with_correct_structure(): void
    {
        $pesaje = Pesaje::factory()->create([
            'peso_bruto_kg' => 20000,
            'peso_tara_kg'  => 8000,
            'peso_neto_kg'  => 12000,
        ]);
        $editor = $this->operador();

        // Genera una entrada en el log editando el peso bruto vía servicio.
        app(PesajeService::class)->editar($pesaje, [
            'peso_bruto_kg' => 22000,
            'motivo'        => 'Error de báscula.',
        ], $editor);

        $response = $this->actingAs($this->operador())
            ->getJson(route('pesajes.log', $pesaje));

        $response->assertOk();
        $grupos = $response->json();

        $this->assertCount(1, $grupos);
        $grupo = $grupos[0];

        $this->assertArrayHasKey('fecha', $grupo);
        $this->assertArrayHasKey('motivo', $grupo);
        $this->assertArrayHasKey('usuario', $grupo);
        $this->assertArrayHasKey('cambios', $grupo);
        $this->assertSame('Error de báscula.', $grupo['motivo']);
        $this->assertSame($editor->name, $grupo['usuario']);
    }

    #[Test]
    public function log_multiple_fields_edited_at_once_appear_in_same_group(): void
    {
        $pesaje = Pesaje::factory()->create([
            'peso_bruto_kg' => 20000,
            'peso_tara_kg'  => 8000,
            'peso_neto_kg'  => 12000,
            'turno'         => 'Mañana',
        ]);
        $editor = $this->operador();

        app(PesajeService::class)->editar($pesaje, [
            'peso_bruto_kg' => 21000,
            'turno'         => 'Tarde',
            'motivo'        => 'Corrección doble.',
        ], $editor);

        $grupos = $this->actingAs($this->operador())
            ->getJson(route('pesajes.log', $pesaje))
            ->assertOk()
            ->json();

        // Ambos cambios están en el mismo grupo (mismo timestamp + motivo + usuario).
        $this->assertCount(1, $grupos);
        $campos = array_column($grupos[0]['cambios'], 'campo');
        $this->assertContains('Peso bruto', $campos);
        $this->assertContains('Turno', $campos);
    }

    // ── Formato de valores ────────────────────────────────────────────

    #[Test]
    public function log_peso_values_are_formatted_with_kg_suffix(): void
    {
        $pesaje = Pesaje::factory()->create([
            'peso_bruto_kg' => 20000,
            'peso_tara_kg'  => 8000,
            'peso_neto_kg'  => 12000,
        ]);

        app(PesajeService::class)->editar($pesaje, [
            'peso_bruto_kg' => 25000,
            'motivo'        => 'Ajuste de peso.',
        ], $this->operador());

        $grupos = $this->actingAs($this->operador())
            ->getJson(route('pesajes.log', $pesaje))
            ->json();

        $cambio = collect($grupos[0]['cambios'])
            ->firstWhere('campo', 'Peso bruto');

        $this->assertNotNull($cambio);
        $this->assertStringContainsString('kg', $cambio['anterior']);
        $this->assertStringContainsString('kg', $cambio['nuevo']);
    }

    #[Test]
    public function log_zona_id_resolves_to_zona_name(): void
    {
        $zonaOriginal = Zona::factory()->create(['nombre' => 'Zona Norte']);
        $zonaNueva = Zona::factory()->create(['nombre' => 'Zona Sur']);

        $pesaje = Pesaje::factory()->create(['zona_id' => $zonaOriginal->id]);

        app(PesajeService::class)->editar($pesaje, [
            'zona_id' => $zonaNueva->id,
            'motivo'  => 'Reasignación de origen.',
        ], $this->operador());

        $grupos = $this->actingAs($this->operador())
            ->getJson(route('pesajes.log', $pesaje))
            ->json();

        $cambio = collect($grupos[0]['cambios'])->firstWhere('campo', 'Origen');
        $this->assertNotNull($cambio);
        $this->assertSame('Zona Norte', $cambio['anterior']);
        $this->assertSame('Zona Sur', $cambio['nuevo']);
    }

    #[Test]
    public function log_tipo_servicio_id_resolves_to_servicio_name(): void
    {
        $servicioOriginal = TipoServicio::factory()->create(['nombre' => 'Servicio A']);
        $servicioNuevo = TipoServicio::factory()->create(['nombre' => 'Servicio B']);

        $pesaje = Pesaje::factory()->create(['tipo_servicio_id' => $servicioOriginal->id]);

        app(PesajeService::class)->editar($pesaje, [
            'tipo_servicio_id' => $servicioNuevo->id,
            'motivo'           => 'Cambio de servicio.',
        ], $this->operador());

        $grupos = $this->actingAs($this->operador())
            ->getJson(route('pesajes.log', $pesaje))
            ->json();

        $cambio = collect($grupos[0]['cambios'])->firstWhere('campo', 'Tipo de servicio');
        $this->assertNotNull($cambio);
        $this->assertSame('Servicio A', $cambio['anterior']);
        $this->assertSame('Servicio B', $cambio['nuevo']);
    }
}
