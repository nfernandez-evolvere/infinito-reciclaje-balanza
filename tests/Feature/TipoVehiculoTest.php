<?php

namespace Tests\Feature;

use App\Models\TipoVehiculo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TipoVehiculoTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function operador(): User
    {
        return User::factory()->create(['role' => 'operador']);
    }

    #[Test]
    public function test_index_renders_list(): void
    {
        TipoVehiculo::factory()->create(['nombre' => 'Compactador']);
        TipoVehiculo::factory()->create(['nombre' => 'Volcador']);

        $this->actingAs($this->admin())
            ->get(route('admin.tipos-vehiculo.index'))
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
            ->assertRedirect(route('admin.tipos-vehiculo.index'));

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
    public function test_admin_can_deactivate(): void
    {
        $tipo = TipoVehiculo::factory()->create(['activo' => true]);

        $this->actingAs($this->admin())
            ->delete(route('admin.tipos-vehiculo.destroy', $tipo))
            ->assertRedirect(route('admin.tipos-vehiculo.index'));

        $this->assertDatabaseHas('tipos_vehiculo', ['id' => $tipo->id, 'activo' => false]);
    }

    #[Test]
    public function test_physical_delete_not_allowed(): void
    {
        $tipo = TipoVehiculo::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.tipos-vehiculo.destroy', $tipo));

        $this->assertDatabaseHas('tipos_vehiculo', ['id' => $tipo->id]);
    }

    #[Test]
    public function test_operador_cannot_access(): void
    {
        $this->actingAs($this->operador())
            ->get(route('admin.tipos-vehiculo.index'))
            ->assertStatus(403);
    }
}
