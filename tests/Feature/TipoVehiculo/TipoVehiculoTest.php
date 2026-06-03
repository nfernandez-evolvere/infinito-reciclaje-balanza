<?php

namespace Tests\Feature\TipoVehiculo;

use App\Models\TipoVehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TipoVehiculoTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_index_renders_list(): void
    {
        TipoVehiculo::factory()->create(['nombre' => 'Compactador']);
        TipoVehiculo::factory()->create(['nombre' => 'Volcador']);

        // El índice de tipos-vehículo se fusionó como tab dentro de la pantalla de vehículos.
        $this->actingAs($this->admin())
            ->get(route('admin.vehiculos.index', ['tab' => 'tipos']))
            ->assertStatus(200)
            ->assertSee('Compactador')
            ->assertSee('Volcador');
    }

    #[Test]
    public function test_admin_can_create(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.tipos-vehiculo.store'), [
                'nombre'      => 'Particular',
                'peso_min_kg' => 500,
                'peso_max_kg' => 3500,
            ])
            ->assertRedirect(route('admin.vehiculos.index', ['tab' => 'tipos']));

        $this->assertDatabaseHas('tipos_vehiculo', ['nombre' => 'Particular']);
    }

    #[Test]
    public function test_validation_fails_when_min_greater_than_max(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.tipos-vehiculo.store'), [
                'nombre'      => 'Inválido',
                'peso_min_kg' => 5000,
                'peso_max_kg' => 1000,
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors('peso_max_kg');
    }

    #[Test]
    public function test_admin_can_deactivate_via_toggle(): void
    {
        $tipo = TipoVehiculo::factory()->create(['activo' => true]);

        $this->actingAs($this->admin())
            ->patch(route('admin.tipos-vehiculo.toggle', $tipo))
            ->assertRedirect(route('admin.vehiculos.index', ['tab' => 'tipos']));

        $this->assertDatabaseHas('tipos_vehiculo', ['id' => $tipo->id, 'activo' => false]);
    }

    #[Test]
    public function test_admin_can_activate_via_toggle(): void
    {
        $tipo = TipoVehiculo::factory()->create(['activo' => false]);

        $this->actingAs($this->admin())
            ->patch(route('admin.tipos-vehiculo.toggle', $tipo))
            ->assertRedirect(route('admin.vehiculos.index', ['tab' => 'tipos']));

        $this->assertDatabaseHas('tipos_vehiculo', ['id' => $tipo->id, 'activo' => true]);
    }

    #[Test]
    public function test_admin_can_destroy_tipo_without_vehiculos(): void
    {
        $tipo = TipoVehiculo::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.tipos-vehiculo.destroy', $tipo))
            ->assertRedirect(route('admin.vehiculos.index', ['tab' => 'tipos']));

        $this->assertDatabaseMissing('tipos_vehiculo', ['id' => $tipo->id]);
    }

    #[Test]
    public function test_operador_cannot_access(): void
    {
        $this->actingAs($this->operador())
            ->get(route('admin.tipos-vehiculo.index'))
            ->assertStatus(403);
    }
}
