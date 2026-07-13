<?php

namespace Tests\Feature\Pesaje;

use App\Models\Pesaje;
use App\Models\Vehiculo;
use App\Models\Zona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PesajeHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function pesajeConPatente(string $patente, array $overrides = []): Pesaje
    {
        $vehiculo = Vehiculo::factory()->create(['patente' => $patente]);

        return Pesaje::factory()->create(array_merge([
            'vehiculo_id' => $vehiculo->id,
            // Fuera del turno de hoy: así los widgets "del día" (último pesaje, KPIs del
            // turno) no muestran la patente y los assert del filtro miran solo la tabla.
            'created_at' => now()->subWeek(),
        ], $overrides));
    }

    // ── Acceso ────────────────────────────────────────────────────────

    #[Test]
    public function admin_can_view_pesajes_index(): void
    {
        $this->pesajeConPatente('ADM111');

        $this->actingAs($this->admin())
            ->get(route('admin.pesajes.index'))
            ->assertOk()
            ->assertSee('ADM111');
    }

    #[Test]
    public function operador_can_view_historial(): void
    {
        $this->pesajeConPatente('OPE222');

        $this->actingAs($this->operador())
            ->get(route('historial'))
            ->assertOk()
            ->assertSee('OPE222');
    }

    #[Test]
    public function guest_is_redirected_to_login(): void
    {
        $this->get(route('historial'))->assertRedirect(route('login'));
    }

    // ── Detalle (show) ────────────────────────────────────────────────

    #[Test]
    public function admin_detail_view_links_historial_to_admin_index(): void
    {
        // El botón "Ver historial" debe llevar al índice del admin, no a la ruta
        // 'historial' (role:operador) — esa daba 403 al admin de organización.
        $pesaje = $this->pesajeConPatente('DET001');

        $this->actingAs($this->admin())
            ->get(route('pesajes.show', $pesaje))
            ->assertOk()
            ->assertSee('Ver historial')
            // El layout del admin nunca renderiza la ruta operador: si aparece,
            // es por el botón apuntando a la ruta equivocada.
            ->assertDontSee(route('historial'))
            // El admin no puede crear pesajes (/balanza es role:operador): se oculta.
            ->assertDontSee('Registrar otro pesaje');
    }

    #[Test]
    public function operador_detail_view_shows_both_actions(): void
    {
        $pesaje = $this->pesajeConPatente('DET002');

        $this->actingAs($this->operador())
            ->get(route('pesajes.show', $pesaje))
            ->assertOk()
            ->assertSee('Ver historial')
            ->assertSee('Registrar otro pesaje');
    }

    // ── Filtros ───────────────────────────────────────────────────────

    #[Test]
    public function filter_by_patente_narrows_results(): void
    {
        $this->pesajeConPatente('FIL001');
        $this->pesajeConPatente('OTR002');

        $this->actingAs($this->admin())
            ->get(route('admin.pesajes.index', ['patente' => 'FIL']))
            ->assertOk()
            ->assertSee('FIL001')
            ->assertDontSee('OTR002');
    }

    #[Test]
    public function filter_by_estado_narrows_results(): void
    {
        $this->pesajeConPatente('PRE001', ['estado' => 'En predio']);
        $this->pesajeConPatente('CER002', ['estado' => 'Cerrado']);

        $this->actingAs($this->admin())
            ->get(route('admin.pesajes.index', ['estado' => 'En predio']))
            ->assertOk()
            ->assertSee('PRE001')
            ->assertDontSee('CER002');
    }

    #[Test]
    public function admin_can_filter_by_zona(): void
    {
        $zonaA = Zona::factory()->create();
        $zonaB = Zona::factory()->create();
        $this->pesajeConPatente('ZNA001', ['zona_id' => $zonaA->id]);
        $this->pesajeConPatente('ZNB002', ['zona_id' => $zonaB->id]);

        $this->actingAs($this->admin())
            ->get(route('admin.pesajes.index', ['zona_id' => $zonaA->id]))
            ->assertOk()
            ->assertSee('ZNA001')
            ->assertDontSee('ZNB002');
    }

    #[Test]
    public function operador_can_filter_by_zona(): void
    {
        // El operador ve los mismos filtros que el admin (zona, servicio, tipo de
        // vehículo, mostrar): el panel de historial ya no se los oculta.
        $zonaA = Zona::factory()->create();
        $zonaB = Zona::factory()->create();
        $this->pesajeConPatente('OZA001', ['zona_id' => $zonaA->id]);
        $this->pesajeConPatente('OZB002', ['zona_id' => $zonaB->id]);

        $this->actingAs($this->operador())
            ->get(route('historial', ['zona_id' => $zonaA->id]))
            ->assertOk()
            ->assertSee('OZA001')
            ->assertDontSee('OZB002');
    }
}
