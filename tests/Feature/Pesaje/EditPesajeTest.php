<?php

namespace Tests\Feature\Pesaje;

use App\Models\Pesaje;
use App\Models\TipoServicio;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use App\Models\Zona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EditPesajeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function update_requires_motivo(): void
    {
        $pesaje = Pesaje::factory()->create();

        $this->actingAs($this->operador())
            ->put(route('pesajes.update', $pesaje), ['zona_id' => Zona::factory()->create()->id])
            ->assertSessionHasErrors('motivo');
    }

    #[Test]
    public function editing_bruto_recalculates_neto_and_appears_in_log(): void
    {
        $pesaje = Pesaje::factory()->create([
            'peso_bruto_kg' => 20000,
            'peso_tara_kg'  => 8000,
            'peso_neto_kg'  => 12000,
            'editado'       => false,
        ]);

        $this->actingAs($this->operador())
            ->put(route('pesajes.update', $pesaje), [
                'peso_bruto_kg' => 25000,
                'motivo'        => 'Corrección del peso bruto.',
            ])
            ->assertRedirect(route('historial'));

        $this->assertDatabaseHas('pesajes', [
            'id'            => $pesaje->id,
            'peso_bruto_kg' => 25000,
            'peso_neto_kg'  => 17000,
            'editado'       => true,
        ]);
        $this->assertDatabaseHas('pesajes_log', ['pesaje_id' => $pesaje->id, 'campo' => 'peso_bruto_kg']);
    }

    #[Test]
    public function editing_only_zona_does_not_touch_the_rest(): void
    {
        $pesaje = Pesaje::factory()->create([
            'peso_bruto_kg' => 20000,
            'turno'         => 'Mañana',
        ]);
        $nuevaZona = Zona::factory()->create();

        $this->actingAs($this->operador())
            ->put(route('pesajes.update', $pesaje), [
                'zona_id' => $nuevaZona->id,
                'motivo'  => 'Origen mal cargado.',
            ])
            ->assertRedirect(route('historial'));

        $this->assertDatabaseHas('pesajes', [
            'id'            => $pesaje->id,
            'zona_id'       => $nuevaZona->id,
            'peso_bruto_kg' => 20000,
            'turno'         => 'Mañana',
        ]);
        // Solo se registra el campo que cambió.
        $this->assertDatabaseCount('pesajes_log', 1);
        $this->assertDatabaseHas('pesajes_log', ['pesaje_id' => $pesaje->id, 'campo' => 'zona_id']);
    }

    #[Test]
    public function admin_update_redirects_to_admin_index(): void
    {
        $pesaje = Pesaje::factory()->create([
            'peso_bruto_kg' => 20000,
            'peso_tara_kg'  => 8000,
            'peso_neto_kg'  => 12000,
        ]);

        $this->actingAs($this->admin())
            ->put(route('pesajes.update', $pesaje), [
                'peso_bruto_kg' => 22000,
                'motivo'        => 'Corrección verificada por admin.',
            ])
            ->assertRedirect(route('admin.pesajes.index'));
    }

    #[Test]
    public function guest_is_redirected_to_login(): void
    {
        $pesaje = Pesaje::factory()->create();

        $this->put(route('pesajes.update', $pesaje), ['motivo' => 'algo'])
            ->assertRedirect(route('login'));
    }

    // ── edit GET view ─────────────────────────────────────────────────

    #[Test]
    public function operador_can_view_edit_form(): void
    {
        $tipo = TipoVehiculo::factory()->create();
        $vehiculo = Vehiculo::factory()->create(['tipo_vehiculo_id' => $tipo->id]);
        $servicio = TipoServicio::factory()->create();
        $servicio->tiposVehiculo()->attach($tipo->id);
        $pesaje = Pesaje::factory()->create([
            'vehiculo_id'      => $vehiculo->id,
            'tipo_servicio_id' => $servicio->id,
        ]);

        $this->actingAs($this->operador())
            ->get(route('pesajes.edit', $pesaje))
            ->assertOk();
    }

    #[Test]
    public function admin_can_view_edit_form(): void
    {
        $tipo = TipoVehiculo::factory()->create();
        $vehiculo = Vehiculo::factory()->create(['tipo_vehiculo_id' => $tipo->id]);
        $servicio = TipoServicio::factory()->create();
        $servicio->tiposVehiculo()->attach($tipo->id);
        $pesaje = Pesaje::factory()->create([
            'vehiculo_id'      => $vehiculo->id,
            'tipo_servicio_id' => $servicio->id,
        ]);

        $this->actingAs($this->admin())
            ->get(route('pesajes.edit', $pesaje))
            ->assertOk();
    }

    #[Test]
    public function edit_form_passes_initial_data_with_vehiculo_and_servicio(): void
    {
        $tipo = TipoVehiculo::factory()->create(['peso_min_kg' => 1000, 'peso_max_kg' => 30000]);
        $vehiculo = Vehiculo::factory()->create([
            'tipo_vehiculo_id' => $tipo->id,
            'tara_kg'          => 5000,
        ]);
        $servicio = TipoServicio::factory()->create();
        $servicio->tiposVehiculo()->attach($tipo->id);
        $pesaje = Pesaje::factory()->create([
            'vehiculo_id'      => $vehiculo->id,
            'tipo_servicio_id' => $servicio->id,
            'peso_bruto_kg'    => 18000,
        ]);

        $response = $this->actingAs($this->operador())
            ->get(route('pesajes.edit', $pesaje));

        $response->assertOk();
        $response->assertViewHas('pesaje', fn ($p) => $p->id === $pesaje->id);
        $response->assertViewHas('initial', fn ($i) => $i['vehiculo']['id'] === $vehiculo->id &&
            $i['servicioId'] === $servicio->id &&
            $i['pesoBruto'] === 18000
        );
    }

    #[Test]
    public function edit_form_guest_is_redirected_to_login(): void
    {
        $pesaje = Pesaje::factory()->create();

        $this->get(route('pesajes.edit', $pesaje))
            ->assertRedirect(route('login'));
    }
}
