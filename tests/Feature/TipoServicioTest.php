<?php

namespace Tests\Feature;

use App\Models\TipoServicio;
use App\Models\TipoVehiculo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TipoServicioTest extends TestCase
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

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'nombre'                    => 'Domiciliario',
            'tipo_vehiculo_sugerido_id' => null,
        ], $overrides);
    }

    // — Index ——————————————————————————————————————————————————

    #[Test]
    public function test_index_renders_for_admin(): void
    {
        TipoServicio::factory()->create(['nombre' => 'Domiciliario']);
        TipoServicio::factory()->create(['nombre' => 'Barrido']);

        $this->actingAs($this->admin())
            ->get(route('admin.tipos-servicio.index'))
            ->assertStatus(200)
            ->assertSee('Domiciliario')
            ->assertSee('Barrido');
    }

    #[Test]
    public function test_index_shows_tipo_vehiculo_nombre(): void
    {
        $tv = TipoVehiculo::factory()->create(['nombre' => 'Compactador']);
        TipoServicio::factory()->create(['tipo_vehiculo_sugerido_id' => $tv->id]);

        $this->actingAs($this->admin())
            ->get(route('admin.tipos-servicio.index'))
            ->assertStatus(200)
            ->assertSee('Compactador');
    }

    #[Test]
    public function test_index_shows_guion_when_no_tipo_vehiculo(): void
    {
        TipoServicio::factory()->create(['tipo_vehiculo_sugerido_id' => null]);

        $this->actingAs($this->admin())
            ->get(route('admin.tipos-servicio.index'))
            ->assertStatus(200)
            ->assertSee('—');
    }

    #[Test]
    public function test_index_filter_by_nombre(): void
    {
        TipoServicio::factory()->create(['nombre' => 'Domiciliario']);
        TipoServicio::factory()->create(['nombre' => 'Barrido']);

        $this->actingAs($this->admin())
            ->get(route('admin.tipos-servicio.index', ['nombre' => 'Domicil']))
            ->assertStatus(200)
            ->assertSee('Domiciliario')
            ->assertDontSee('Barrido');
    }

    #[Test]
    public function test_index_filter_by_activo(): void
    {
        TipoServicio::factory()->create(['nombre' => 'Domiciliario', 'activo' => true]);
        TipoServicio::factory()->create(['nombre' => 'Barrido',      'activo' => false]);

        $this->actingAs($this->admin())
            ->get(route('admin.tipos-servicio.index', ['activo' => '1']))
            ->assertStatus(200)
            ->assertSee('Domiciliario')
            ->assertDontSee('Barrido');
    }

    #[Test]
    public function test_index_filter_by_tipo_vehiculo_id(): void
    {
        $tvA = TipoVehiculo::factory()->create(['nombre' => 'Compactador']);
        $tvB = TipoVehiculo::factory()->create(['nombre' => 'Volcador']);
        TipoServicio::factory()->create(['nombre' => 'Domiciliario', 'tipo_vehiculo_sugerido_id' => $tvA->id]);
        TipoServicio::factory()->create(['nombre' => 'Barrido',      'tipo_vehiculo_sugerido_id' => $tvB->id]);

        $this->actingAs($this->admin())
            ->get(route('admin.tipos-servicio.index', ['tipo_vehiculo_id' => $tvA->id]))
            ->assertStatus(200)
            ->assertSee('Domiciliario')
            ->assertDontSee('Barrido');
    }

    #[Test]
    public function test_index_shows_empty_state_when_no_records(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.tipos-servicio.index'))
            ->assertStatus(200)
            ->assertSee('Todavía no hay tipos de servicio');
    }

    #[Test]
    public function test_index_shows_empty_state_with_active_filters(): void
    {
        TipoServicio::factory()->create(['nombre' => 'Barrido']);

        $this->actingAs($this->admin())
            ->get(route('admin.tipos-servicio.index', ['nombre' => 'XYZ_INEXISTENTE']))
            ->assertStatus(200)
            ->assertSee('Sin resultados')
            ->assertDontSee('Barrido');
    }

    // — Store ——————————————————————————————————————————————————

    #[Test]
    public function test_admin_can_create_with_tipo_vehiculo(): void
    {
        $tv = TipoVehiculo::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.tipos-servicio.store'), $this->payload([
                'nombre'                    => 'Domiciliario',
                'tipo_vehiculo_sugerido_id' => $tv->id,
            ]))
            ->assertRedirect(route('admin.tipos-servicio.index'));

        $this->assertDatabaseHas('tipos_servicio', [
            'nombre'                    => 'Domiciliario',
            'tipo_vehiculo_sugerido_id' => $tv->id,
            'activo'                    => true,
        ]);
    }

    #[Test]
    public function test_admin_can_create_without_tipo_vehiculo(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.tipos-servicio.store'), $this->payload([
                'tipo_vehiculo_sugerido_id' => null,
            ]))
            ->assertRedirect(route('admin.tipos-servicio.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tipos_servicio', [
            'nombre'                    => 'Domiciliario',
            'tipo_vehiculo_sugerido_id' => null,
        ]);
    }

    #[Test]
    public function test_store_trata_tipo_vehiculo_vacio_como_null(): void
    {
        // El select envía '' cuando el usuario elige "Sin asignar"
        // ConvertEmptyStringsToNull lo convierte a null antes de validar
        $this->actingAs($this->admin())
            ->post(route('admin.tipos-servicio.store'), $this->payload([
                'tipo_vehiculo_sugerido_id' => '',
            ]))
            ->assertRedirect(route('admin.tipos-servicio.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tipos_servicio', [
            'nombre'                    => 'Domiciliario',
            'tipo_vehiculo_sugerido_id' => null,
        ]);
    }

    #[Test]
    public function test_store_validates_nombre_required(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.tipos-servicio.store'), $this->payload(['nombre' => '']))
            ->assertSessionHasErrors('nombre');
    }

    #[Test]
    public function test_store_validates_nombre_max_length(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.tipos-servicio.store'), $this->payload(['nombre' => str_repeat('a', 101)]))
            ->assertSessionHasErrors('nombre');
    }

    #[Test]
    public function test_store_validates_nombre_unique(): void
    {
        TipoServicio::factory()->create(['nombre' => 'Domiciliario']);

        $this->actingAs($this->admin())
            ->post(route('admin.tipos-servicio.store'), $this->payload(['nombre' => 'Domiciliario']))
            ->assertSessionHasErrors('nombre');
    }

    #[Test]
    public function test_store_validates_tipo_vehiculo_debe_existir(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.tipos-servicio.store'), $this->payload([
                'tipo_vehiculo_sugerido_id' => 99999,
            ]))
            ->assertSessionHasErrors('tipo_vehiculo_sugerido_id');
    }

    // — Update ——————————————————————————————————————————————————

    #[Test]
    public function test_admin_can_update(): void
    {
        $tv   = TipoVehiculo::factory()->create();
        $tipo = TipoServicio::factory()->create(['nombre' => 'Antiguo']);

        $this->actingAs($this->admin())
            ->put(route('admin.tipos-servicio.update', $tipo), [
                'nombre'                    => 'Nuevo',
                'tipo_vehiculo_sugerido_id' => $tv->id,
            ])
            ->assertRedirect(route('admin.tipos-servicio.index'));

        $this->assertDatabaseHas('tipos_servicio', [
            'id'                        => $tipo->id,
            'nombre'                    => 'Nuevo',
            'tipo_vehiculo_sugerido_id' => $tv->id,
        ]);
    }

    #[Test]
    public function test_update_permite_mismo_nombre_en_mismo_registro(): void
    {
        // La regla unique con ->ignore() no debe rechazar el mismo nombre en el mismo registro
        $tipo = TipoServicio::factory()->create(['nombre' => 'Barrido']);

        $this->actingAs($this->admin())
            ->put(route('admin.tipos-servicio.update', $tipo), [
                'nombre'                    => 'Barrido',
                'tipo_vehiculo_sugerido_id' => null,
            ])
            ->assertRedirect(route('admin.tipos-servicio.index'));

        // Si llegó al index (no a la vista con errores), la validación pasó
        $this->assertDatabaseHas('tipos_servicio', ['id' => $tipo->id, 'nombre' => 'Barrido']);
    }

    #[Test]
    public function test_update_rechaza_nombre_de_otro_registro(): void
    {
        TipoServicio::factory()->create(['nombre' => 'Barrido']);
        $tipo = TipoServicio::factory()->create(['nombre' => 'Voluminoso']);

        $this->actingAs($this->admin())
            ->put(route('admin.tipos-servicio.update', $tipo), $this->payload(['nombre' => 'Barrido']))
            ->assertSessionHasErrors('nombre');
    }

    #[Test]
    public function test_update_puede_limpiar_tipo_vehiculo(): void
    {
        $tv   = TipoVehiculo::factory()->create();
        $tipo = TipoServicio::factory()->create(['tipo_vehiculo_sugerido_id' => $tv->id]);

        $this->actingAs($this->admin())
            ->put(route('admin.tipos-servicio.update', $tipo), [
                'nombre'                    => $tipo->nombre,
                'tipo_vehiculo_sugerido_id' => null,
            ])
            ->assertRedirect(route('admin.tipos-servicio.index'));

        $this->assertDatabaseHas('tipos_servicio', [
            'id'                        => $tipo->id,
            'tipo_vehiculo_sugerido_id' => null,
        ]);
    }

    #[Test]
    public function test_update_validates_nombre_required(): void
    {
        $tipo = TipoServicio::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('admin.tipos-servicio.update', $tipo), ['nombre' => ''])
            ->assertSessionHasErrors('nombre');
    }

    // — Toggle ——————————————————————————————————————————————————

    #[Test]
    public function test_admin_can_deactivate_via_toggle(): void
    {
        $tipo = TipoServicio::factory()->create(['activo' => true]);

        $this->actingAs($this->admin())
            ->patch(route('admin.tipos-servicio.toggle', $tipo))
            ->assertRedirect(route('admin.tipos-servicio.index'));

        $this->assertDatabaseHas('tipos_servicio', ['id' => $tipo->id, 'activo' => false]);
    }

    #[Test]
    public function test_admin_can_activate_via_toggle(): void
    {
        $tipo = TipoServicio::factory()->create(['activo' => false]);

        $this->actingAs($this->admin())
            ->patch(route('admin.tipos-servicio.toggle', $tipo))
            ->assertRedirect(route('admin.tipos-servicio.index'));

        $this->assertDatabaseHas('tipos_servicio', ['id' => $tipo->id, 'activo' => true]);
    }

    // — Destroy ——————————————————————————————————————————————————

    #[Test]
    public function test_admin_can_destroy_sin_pesajes(): void
    {
        $tipo = TipoServicio::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.tipos-servicio.destroy', $tipo))
            ->assertRedirect(route('admin.tipos-servicio.index'));

        $this->assertDatabaseMissing('tipos_servicio', ['id' => $tipo->id]);
    }

    // — Acceso ——————————————————————————————————————————————————

    #[Test]
    public function test_operador_cannot_access_index(): void
    {
        $this->actingAs($this->operador())
            ->get(route('admin.tipos-servicio.index'))
            ->assertStatus(403);
    }

    #[Test]
    public function test_operador_cannot_store(): void
    {
        $this->actingAs($this->operador())
            ->post(route('admin.tipos-servicio.store'), $this->payload())
            ->assertStatus(403);
    }

    #[Test]
    public function test_operador_cannot_update(): void
    {
        $tipo = TipoServicio::factory()->create();

        $this->actingAs($this->operador())
            ->put(route('admin.tipos-servicio.update', $tipo), $this->payload())
            ->assertStatus(403);
    }

    #[Test]
    public function test_operador_cannot_toggle(): void
    {
        $tipo = TipoServicio::factory()->create();

        $this->actingAs($this->operador())
            ->patch(route('admin.tipos-servicio.toggle', $tipo))
            ->assertStatus(403);
    }

    #[Test]
    public function test_operador_cannot_destroy(): void
    {
        $tipo = TipoServicio::factory()->create();

        $this->actingAs($this->operador())
            ->delete(route('admin.tipos-servicio.destroy', $tipo))
            ->assertStatus(403);
    }

    #[Test]
    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.tipos-servicio.index'))
            ->assertRedirect(route('login'));
    }

    // — Integración con TipoVehiculo ——————————————————————————

    #[Test]
    public function test_tipos_vehiculo_aparecen_en_el_select_del_index(): void
    {
        TipoVehiculo::factory()->create(['nombre' => 'Volcador']);

        $this->actingAs($this->admin())
            ->get(route('admin.tipos-servicio.index'))
            ->assertStatus(200)
            ->assertSee('Volcador');
    }

    #[Test]
    public function test_eliminar_tipo_vehiculo_pone_sugerido_en_null(): void
    {
        // ON DELETE SET NULL: al eliminar el tipo de vehículo, el FK del servicio debe quedar null
        $tv   = TipoVehiculo::factory()->create();
        $tipo = TipoServicio::factory()->create(['tipo_vehiculo_sugerido_id' => $tv->id]);

        $this->actingAs($this->admin())
            ->delete(route('admin.tipos-vehiculo.destroy', $tv))
            ->assertRedirect(route('admin.tipos-vehiculo.index'));

        $this->assertDatabaseHas('tipos_servicio', [
            'id'                        => $tipo->id,
            'tipo_vehiculo_sugerido_id' => null,
        ]);
    }

    #[Test]
    public function test_desactivar_tipo_vehiculo_no_afecta_tipo_servicio(): void
    {
        $tv   = TipoVehiculo::factory()->create(['activo' => true]);
        $tipo = TipoServicio::factory()->create([
            'tipo_vehiculo_sugerido_id' => $tv->id,
            'activo'                    => true,
        ]);

        $this->actingAs($this->admin())
            ->patch(route('admin.tipos-vehiculo.toggle', $tv));

        // El tipo de servicio conserva su estado y su FK
        $this->assertDatabaseHas('tipos_servicio', [
            'id'                        => $tipo->id,
            'activo'                    => true,
            'tipo_vehiculo_sugerido_id' => $tv->id,
        ]);
    }

    #[Test]
    public function test_tipos_servicio_inactivos_siguen_visibles_en_tabla(): void
    {
        TipoServicio::factory()->create(['nombre' => 'Activo',   'activo' => true]);
        TipoServicio::factory()->create(['nombre' => 'Inactivo', 'activo' => false]);

        // Sin filtro de activo, la tabla muestra ambos
        $this->actingAs($this->admin())
            ->get(route('admin.tipos-servicio.index'))
            ->assertSee('Activo')
            ->assertSee('Inactivo');
    }
}
