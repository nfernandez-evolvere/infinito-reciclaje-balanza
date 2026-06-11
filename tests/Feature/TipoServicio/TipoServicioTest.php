<?php

namespace Tests\Feature\TipoServicio;

use App\Models\TipoServicio;
use App\Models\TipoVehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TipoServicioTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'nombre'            => 'Domiciliario',
            'tipo_vehiculo_ids' => [],
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
        $tipo = TipoServicio::factory()->create();
        $tipo->tiposVehiculo()->attach($tv->id);

        $this->actingAs($this->admin())
            ->get(route('admin.tipos-servicio.index'))
            ->assertStatus(200)
            ->assertSee('Compactador');
    }

    #[Test]
    public function test_index_shows_guion_when_no_tipo_vehiculo(): void
    {
        TipoServicio::factory()->create();

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
        TipoServicio::factory()->create(['nombre' => 'Domiciliario'])->tiposVehiculo()->attach($tvA->id);
        TipoServicio::factory()->create(['nombre' => 'Barrido'])->tiposVehiculo()->attach($tvB->id);

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
    public function test_admin_can_create_with_tipos_vehiculo(): void
    {
        $tv = TipoVehiculo::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.tipos-servicio.store'), $this->payload([
                'nombre'            => 'Domiciliario',
                'tipo_vehiculo_ids' => [$tv->id],
            ]))
            ->assertRedirect(route('admin.tipos-servicio.index'));

        $this->assertDatabaseHas('tipos_servicio', [
            'nombre' => 'Domiciliario',
            'activo' => true,
        ]);
        $tipo = TipoServicio::where('nombre', 'Domiciliario')->first();
        $this->assertDatabaseHas('tipo_servicio_tipo_vehiculo', [
            'tipo_servicio_id' => $tipo->id,
            'tipo_vehiculo_id' => $tv->id,
        ]);
    }

    #[Test]
    public function test_admin_can_create_without_tipos_vehiculo(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.tipos-servicio.store'), $this->payload([
                'tipo_vehiculo_ids' => [],
            ]))
            ->assertRedirect(route('admin.tipos-servicio.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tipos_servicio', ['nombre' => 'Domiciliario']);
        $this->assertDatabaseCount('tipo_servicio_tipo_vehiculo', 0);
    }

    #[Test]
    public function test_store_acepta_seleccion_de_vehiculos_vacia(): void
    {
        // El multi-select no envía la clave cuando no hay vehículos elegidos.
        $this->actingAs($this->admin())
            ->post(route('admin.tipos-servicio.store'), ['nombre' => 'Domiciliario'])
            ->assertRedirect(route('admin.tipos-servicio.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tipos_servicio', ['nombre' => 'Domiciliario']);
        $this->assertDatabaseCount('tipo_servicio_tipo_vehiculo', 0);
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
                'tipo_vehiculo_ids' => [99999],
            ]))
            ->assertSessionHasErrors('tipo_vehiculo_ids.0');
    }

    // — Update ——————————————————————————————————————————————————

    #[Test]
    public function test_admin_can_update(): void
    {
        $tv = TipoVehiculo::factory()->create();
        $tipo = TipoServicio::factory()->create(['nombre' => 'Antiguo']);

        $this->actingAs($this->admin())
            ->put(route('admin.tipos-servicio.update', $tipo), [
                'nombre'            => 'Nuevo',
                'tipo_vehiculo_ids' => [$tv->id],
            ])
            ->assertRedirect(route('admin.tipos-servicio.index'));

        $this->assertDatabaseHas('tipos_servicio', [
            'id'     => $tipo->id,
            'nombre' => 'Nuevo',
        ]);
        $this->assertDatabaseHas('tipo_servicio_tipo_vehiculo', [
            'tipo_servicio_id' => $tipo->id,
            'tipo_vehiculo_id' => $tv->id,
        ]);
    }

    #[Test]
    public function test_update_permite_mismo_nombre_en_mismo_registro(): void
    {
        // La regla unique con ->ignore() no debe rechazar el mismo nombre en el mismo registro
        $tipo = TipoServicio::factory()->create(['nombre' => 'Barrido']);

        $this->actingAs($this->admin())
            ->put(route('admin.tipos-servicio.update', $tipo), [
                'nombre'            => 'Barrido',
                'tipo_vehiculo_ids' => [],
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

    // — Aislamiento multi-tenant ————————————————————————————————
    // La unicidad de nombre es por organización (unique compuesto en la BD).
    // El nombre de otra organización no debe interferir en la validación.

    #[Test]
    public function test_store_permite_mismo_nombre_en_otra_organizacion(): void
    {
        $otraOrg = $this->createOrganizacion('Otra Organización');
        $this->actingInOrg($otraOrg, fn () => TipoServicio::factory()->create(['nombre' => 'Domiciliario']));

        $this->actingAs($this->admin())
            ->post(route('admin.tipos-servicio.store'), $this->payload(['nombre' => 'Domiciliario']))
            ->assertRedirect(route('admin.tipos-servicio.index'))
            ->assertSessionHasNoErrors();

        // Un servicio "Domiciliario" en cada organización: dos filas, distinta org.
        $this->assertSame(2, TipoServicio::withoutGlobalScopes()->where('nombre', 'Domiciliario')->count());
    }

    #[Test]
    public function test_update_permite_nombre_que_existe_en_otra_organizacion(): void
    {
        $otraOrg = $this->createOrganizacion('Otra Organización');
        $this->actingInOrg($otraOrg, fn () => TipoServicio::factory()->create(['nombre' => 'Domiciliario']));

        // La org actual también tiene su "Domiciliario" (permitido: distinta org).
        $tipo = TipoServicio::factory()->create(['nombre' => 'Domiciliario']);

        // Editar conservando el nombre no debe chocar con la otra org.
        $this->actingAs($this->admin())
            ->put(route('admin.tipos-servicio.update', $tipo), [
                'nombre'            => 'Domiciliario',
                'tipo_vehiculo_ids' => [],
            ])
            ->assertRedirect(route('admin.tipos-servicio.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tipos_servicio', ['id' => $tipo->id, 'nombre' => 'Domiciliario']);
    }

    #[Test]
    public function test_update_puede_limpiar_tipos_vehiculo(): void
    {
        $tv = TipoVehiculo::factory()->create();
        $tipo = TipoServicio::factory()->create();
        $tipo->tiposVehiculo()->attach($tv->id);

        $this->actingAs($this->admin())
            ->put(route('admin.tipos-servicio.update', $tipo), [
                'nombre'            => $tipo->nombre,
                'tipo_vehiculo_ids' => [],
            ])
            ->assertRedirect(route('admin.tipos-servicio.index'));

        $this->assertDatabaseMissing('tipo_servicio_tipo_vehiculo', [
            'tipo_servicio_id' => $tipo->id,
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
    public function test_eliminar_tipo_vehiculo_lo_quita_del_pivot(): void
    {
        // El pivot tiene ON DELETE CASCADE: al eliminar el tipo de vehículo,
        // su vínculo con el servicio se borra (el servicio sigue existiendo).
        $tv = TipoVehiculo::factory()->create();
        $tipo = TipoServicio::factory()->create();
        $tipo->tiposVehiculo()->attach($tv->id);

        $this->actingAs($this->admin())
            ->delete(route('admin.tipos-vehiculo.destroy', $tv))
            ->assertRedirect(route('admin.vehiculos.index', ['tab' => 'tipos']));

        $this->assertDatabaseHas('tipos_servicio', ['id' => $tipo->id]);
        $this->assertDatabaseMissing('tipo_servicio_tipo_vehiculo', [
            'tipo_vehiculo_id' => $tv->id,
        ]);
    }

    #[Test]
    public function test_desactivar_tipo_vehiculo_no_afecta_tipo_servicio(): void
    {
        $tv = TipoVehiculo::factory()->create(['activo' => true]);
        $tipo = TipoServicio::factory()->create(['activo' => true]);
        $tipo->tiposVehiculo()->attach($tv->id);

        $this->actingAs($this->admin())
            ->patch(route('admin.tipos-vehiculo.toggle', $tv));

        // El tipo de servicio conserva su estado y su vínculo en el pivot
        $this->assertDatabaseHas('tipos_servicio', ['id' => $tipo->id, 'activo' => true]);
        $this->assertDatabaseHas('tipo_servicio_tipo_vehiculo', [
            'tipo_servicio_id' => $tipo->id,
            'tipo_vehiculo_id' => $tv->id,
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
