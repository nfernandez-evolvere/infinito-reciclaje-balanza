<?php

namespace Tests\Feature\Zona;

use App\Models\TipoServicio;
use App\Models\Zona;
use App\Models\ZonaServicio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ZonaServicioTest extends TestCase
{
    use RefreshDatabase;

    // ── store (asignar servicio a zona) ───────────────────────────────

    #[Test]
    public function store_asigna_servicio_a_zona_y_redirige(): void
    {
        $zona = Zona::factory()->create();
        $servicio = TipoServicio::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.zonas.servicios.store', $zona), [
                'tipo_servicio_id' => $servicio->id,
                'turnos'           => ['Diurna'],
            ])
            ->assertRedirect(route('admin.zonas.index'));

        $this->assertDatabaseHas('zona_servicios', [
            'zona_id'          => $zona->id,
            'tipo_servicio_id' => $servicio->id,
        ]);
    }

    #[Test]
    public function store_persiste_los_turnos_asignados(): void
    {
        $zona = Zona::factory()->create();
        $servicio = TipoServicio::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.zonas.servicios.store', $zona), [
                'tipo_servicio_id' => $servicio->id,
                'turnos'           => ['Diurna', 'Nocturna'],
            ]);

        $this->assertDatabaseHas('zona_servicio_turnos', ['zona_id' => $zona->id, 'turno' => 'Diurna']);
        $this->assertDatabaseHas('zona_servicio_turnos', ['zona_id' => $zona->id, 'turno' => 'Nocturna']);
    }

    #[Test]
    public function store_validates_tipo_servicio_required_and_exists(): void
    {
        $zona = Zona::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.zonas.servicios.store', $zona), [
                'tipo_servicio_id' => null,
            ])
            ->assertSessionHasErrors('tipo_servicio_id');

        $this->actingAs($this->admin())
            ->post(route('admin.zonas.servicios.store', $zona), [
                'tipo_servicio_id' => 999999,
            ])
            ->assertSessionHasErrors('tipo_servicio_id');
    }

    #[Test]
    public function solo_admin_puede_asignar_servicio(): void
    {
        $zona = Zona::factory()->create();
        $servicio = TipoServicio::factory()->create();

        $this->actingAs($this->operador())
            ->post(route('admin.zonas.servicios.store', $zona), [
                'tipo_servicio_id' => $servicio->id,
            ])
            ->assertForbidden();
    }

    // ── update (actualizar asignacion) ────────────────────────────────

    #[Test]
    public function update_reemplaza_los_turnos_del_servicio(): void
    {
        $zona = Zona::factory()->create();
        $servicio = TipoServicio::factory()->create();

        // Asignación inicial con Diurna.
        ZonaServicio::create(['zona_id' => $zona->id, 'tipo_servicio_id' => $servicio->id]);

        $this->actingAs($this->admin())
            ->put(route('admin.zonas.servicios.update', [$zona, $servicio]), [
                'tipo_servicio_id' => $servicio->id,
                'turnos'           => ['Nocturna'],
            ])
            ->assertRedirect(route('admin.zonas.index'));

        $this->assertDatabaseHas('zona_servicio_turnos', ['zona_id' => $zona->id, 'turno' => 'Nocturna']);
    }

    // ── destroy (quitar servicio de zona) ─────────────────────────────

    #[Test]
    public function destroy_quita_el_servicio_de_la_zona(): void
    {
        $zona = Zona::factory()->create();
        $servicio = TipoServicio::factory()->create();
        ZonaServicio::create(['zona_id' => $zona->id, 'tipo_servicio_id' => $servicio->id]);

        $this->actingAs($this->admin())
            ->delete(route('admin.zonas.servicios.destroy', [$zona, $servicio]))
            ->assertRedirect(route('admin.zonas.index'));

        $this->assertDatabaseMissing('zona_servicios', [
            'zona_id'          => $zona->id,
            'tipo_servicio_id' => $servicio->id,
        ]);
    }
}
