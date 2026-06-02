<?php

namespace Tests\Feature\Vehiculo;

use App\Models\Pesaje;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VehiculoTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        $tipo = TipoVehiculo::factory()->create();

        return array_merge([
            'patente'          => 'TST001',
            'numero_interno'   => '099',
            'tara_kg'          => 5000,
            'tipo_vehiculo_id' => $tipo->id,
            'titular'          => 'Municipalidad de Corrientes',
            'capacidad_kg'     => null,
            'observaciones'    => null,
        ], $overrides);
    }

    // — Index ——————————————————————————————————————————————————

    #[Test]
    public function test_index_renders_list(): void
    {
        $tipo = TipoVehiculo::factory()->create(['nombre' => 'Compactador']);
        Vehiculo::factory()->create(['patente' => 'ABC123', 'tipo_vehiculo_id' => $tipo->id]);
        Vehiculo::factory()->create(['patente' => 'XYZ999', 'tipo_vehiculo_id' => $tipo->id]);

        $this->actingAs($this->admin())
            ->get(route('admin.vehiculos.index'))
            ->assertStatus(200)
            ->assertSee('ABC123')
            ->assertSee('XYZ999');
    }

    #[Test]
    public function test_index_shows_tipo_vehiculo_name(): void
    {
        $tipo = TipoVehiculo::factory()->create(['nombre' => 'Volcador']);
        Vehiculo::factory()->create(['tipo_vehiculo_id' => $tipo->id]);

        $this->actingAs($this->admin())
            ->get(route('admin.vehiculos.index'))
            ->assertStatus(200)
            ->assertSee('Volcador');
    }

    #[Test]
    public function test_index_filter_by_patente(): void
    {
        Vehiculo::factory()->create(['patente' => 'FIL001']);
        Vehiculo::factory()->create(['patente' => 'OTR002']);

        $this->actingAs($this->admin())
            ->get(route('admin.vehiculos.index', ['patente' => 'FIL']))
            ->assertStatus(200)
            ->assertSee('FIL001')
            ->assertDontSee('OTR002');
    }

    #[Test]
    public function test_index_filter_by_tipo_vehiculo(): void
    {
        $tipoA = TipoVehiculo::factory()->create(['nombre' => 'Compactador']);
        $tipoB = TipoVehiculo::factory()->create(['nombre' => 'Volcador']);
        Vehiculo::factory()->create(['patente' => 'CMP001', 'tipo_vehiculo_id' => $tipoA->id]);
        Vehiculo::factory()->create(['patente' => 'VLC002', 'tipo_vehiculo_id' => $tipoB->id]);

        $this->actingAs($this->admin())
            ->get(route('admin.vehiculos.index', ['tipo_vehiculo_id' => $tipoA->id]))
            ->assertStatus(200)
            ->assertSee('CMP001')
            ->assertDontSee('VLC002');
    }

    // — Store ——————————————————————————————————————————————————

    #[Test]
    public function test_admin_can_create(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.vehiculos.store'), $this->payload())
            ->assertRedirect(route('admin.vehiculos.index'));

        $this->assertDatabaseHas('vehiculos', ['patente' => 'TST001']);
    }

    #[Test]
    public function test_store_validates_patente_required(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.vehiculos.store'), $this->payload(['patente' => '']))
            ->assertSessionHasErrors('patente');
    }

    #[Test]
    public function test_store_validates_patente_unique(): void
    {
        $tipo = TipoVehiculo::factory()->create();
        Vehiculo::factory()->create(['patente' => 'DUP001', 'tipo_vehiculo_id' => $tipo->id]);

        $this->actingAs($this->admin())
            ->post(route('admin.vehiculos.store'), $this->payload(['patente' => 'DUP001']))
            ->assertSessionHasErrors('patente');
    }

    #[Test]
    public function test_store_validates_numero_interno_unique(): void
    {
        $tipo = TipoVehiculo::factory()->create();
        Vehiculo::factory()->create(['numero_interno' => '001', 'tipo_vehiculo_id' => $tipo->id]);

        $this->actingAs($this->admin())
            ->post(route('admin.vehiculos.store'), $this->payload(['numero_interno' => '001']))
            ->assertSessionHasErrors('numero_interno');
    }

    #[Test]
    public function test_store_validates_tara_must_be_positive(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.vehiculos.store'), $this->payload(['tara_kg' => 0]))
            ->assertSessionHasErrors('tara_kg');
    }

    #[Test]
    public function test_store_validates_tipo_vehiculo_must_exist(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.vehiculos.store'), $this->payload(['tipo_vehiculo_id' => 99999]))
            ->assertSessionHasErrors('tipo_vehiculo_id');
    }

    #[Test]
    public function test_store_validates_titular_required(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.vehiculos.store'), $this->payload(['titular' => '']))
            ->assertSessionHasErrors('titular');
    }

    #[Test]
    public function test_store_accepts_optional_fields_as_null(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.vehiculos.store'), $this->payload([
                'capacidad_kg'  => null,
                'observaciones' => null,
            ]))
            ->assertRedirect(route('admin.vehiculos.index'));

        $this->assertDatabaseHas('vehiculos', [
            'patente'       => 'TST001',
            'capacidad_kg'  => null,
            'observaciones' => null,
        ]);
    }

    // — Update ——————————————————————————————————————————————————

    #[Test]
    public function test_admin_can_update(): void
    {
        $tipo     = TipoVehiculo::factory()->create();
        $vehiculo = Vehiculo::factory()->create(['patente' => 'OLD001', 'tipo_vehiculo_id' => $tipo->id]);

        $this->actingAs($this->admin())
            ->put(route('admin.vehiculos.update', $vehiculo), array_merge(
                $this->payload(['tipo_vehiculo_id' => $tipo->id]),
                ['patente' => 'NEW002', 'tara_kg' => 7000]
            ))
            ->assertRedirect(route('admin.vehiculos.index'));

        $this->assertDatabaseHas('vehiculos', ['id' => $vehiculo->id, 'patente' => 'NEW002', 'tara_kg' => 7000]);
    }

    #[Test]
    public function test_update_allows_same_patente_on_same_record(): void
    {
        $vehiculo = Vehiculo::factory()->create(['patente' => 'SAME01']);

        // Actualizar manteniendo la misma patente no debe fallar por unique
        $this->actingAs($this->admin())
            ->put(route('admin.vehiculos.update', $vehiculo), [
                'patente'          => 'SAME01',
                'numero_interno'   => $vehiculo->numero_interno,
                'tara_kg'          => 5500,
                'tipo_vehiculo_id' => $vehiculo->tipo_vehiculo_id,
                'titular'          => $vehiculo->titular,
            ])
            ->assertRedirect(route('admin.vehiculos.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('vehiculos', ['id' => $vehiculo->id, 'tara_kg' => 5500]);
    }

    #[Test]
    public function test_update_allows_same_numero_interno_on_same_record(): void
    {
        $vehiculo = Vehiculo::factory()->create(['numero_interno' => '042']);

        $this->actingAs($this->admin())
            ->put(route('admin.vehiculos.update', $vehiculo), [
                'patente'          => $vehiculo->patente,
                'numero_interno'   => '042',
                'tara_kg'          => $vehiculo->tara_kg,
                'tipo_vehiculo_id' => $vehiculo->tipo_vehiculo_id,
                'titular'          => $vehiculo->titular,
            ])
            ->assertRedirect(route('admin.vehiculos.index'))
            ->assertSessionHasNoErrors();
    }

    #[Test]
    public function test_update_rejects_patente_of_another_vehiculo(): void
    {
        $tipo     = TipoVehiculo::factory()->create();
        Vehiculo::factory()->create(['patente' => 'OTRO01', 'tipo_vehiculo_id' => $tipo->id]);
        $vehiculo = Vehiculo::factory()->create(['tipo_vehiculo_id' => $tipo->id]);

        $this->actingAs($this->admin())
            ->put(route('admin.vehiculos.update', $vehiculo), [
                'patente'          => 'OTRO01',
                'numero_interno'   => $vehiculo->numero_interno,
                'tara_kg'          => $vehiculo->tara_kg,
                'tipo_vehiculo_id' => $vehiculo->tipo_vehiculo_id,
                'titular'          => $vehiculo->titular,
            ])
            ->assertSessionHasErrors('patente');
    }

    // — Update: corrección de tara ————————————————————————————————

    #[Test]
    public function test_update_requires_decision_when_tara_changes_with_pesajes(): void
    {
        $tipo     = TipoVehiculo::factory()->create();
        $vehiculo = Vehiculo::factory()->create(['tara_kg' => 8000, 'tipo_vehiculo_id' => $tipo->id]);
        Pesaje::factory()->create(['vehiculo_id' => $vehiculo->id]);

        $this->actingAs($this->admin())
            ->put(route('admin.vehiculos.update', $vehiculo), [
                'patente'          => $vehiculo->patente,
                'numero_interno'   => $vehiculo->numero_interno,
                'tara_kg'          => 18000,
                'tipo_vehiculo_id' => $tipo->id,
                'titular'          => $vehiculo->titular,
            ])
            ->assertSessionHasErrors('_intencion_tara');
    }

    #[Test]
    public function test_update_corregir_dato_recalculates_pesajes(): void
    {
        $tipo     = TipoVehiculo::factory()->create();
        $vehiculo = Vehiculo::factory()->create(['tara_kg' => 8000, 'tipo_vehiculo_id' => $tipo->id]);
        $pesaje   = Pesaje::factory()->create([
            'vehiculo_id'   => $vehiculo->id,
            'peso_bruto_kg' => 20000,
            'peso_tara_kg'  => 8000,
            'peso_neto_kg'  => 12000,
            'estado'        => 'Cerrado',
            'editado'       => false,
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.vehiculos.update', $vehiculo), [
                'patente'          => $vehiculo->patente,
                'numero_interno'   => $vehiculo->numero_interno,
                'tara_kg'          => 18000,
                'tipo_vehiculo_id' => $tipo->id,
                'titular'          => $vehiculo->titular,
                '_intencion_tara'  => 'corregir_dato',
                '_motivo_tara'     => 'Se había cargado 8.000 en vez de 18.000.',
            ])
            ->assertRedirect(route('admin.vehiculos.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('pesajes', [
            'id'           => $pesaje->id,
            'peso_tara_kg' => 18000,
            'peso_neto_kg' => 2000,
            'editado'      => true,
        ]);
        $this->assertDatabaseHas('vehiculos_log', ['vehiculo_id' => $vehiculo->id, 'campo' => 'tara_kg']);
    }

    #[Test]
    public function test_update_cambio_real_keeps_history(): void
    {
        $tipo     = TipoVehiculo::factory()->create();
        $vehiculo = Vehiculo::factory()->create(['tara_kg' => 8000, 'tipo_vehiculo_id' => $tipo->id]);
        $pesaje   = Pesaje::factory()->create([
            'vehiculo_id'   => $vehiculo->id,
            'peso_bruto_kg' => 20000,
            'peso_tara_kg'  => 8000,
            'peso_neto_kg'  => 12000,
            'estado'        => 'Cerrado',
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.vehiculos.update', $vehiculo), [
                'patente'          => $vehiculo->patente,
                'numero_interno'   => $vehiculo->numero_interno,
                'tara_kg'          => 18000,
                'tipo_vehiculo_id' => $tipo->id,
                'titular'          => $vehiculo->titular,
                '_intencion_tara'  => 'cambio_real',
                '_motivo_tara'     => 'Se le agregó una caja compactadora.',
            ])
            ->assertRedirect(route('admin.vehiculos.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('pesajes', [
            'id'           => $pesaje->id,
            'peso_tara_kg' => 8000,
            'peso_neto_kg' => 12000,
        ]);
        $this->assertDatabaseMissing('pesajes_log', ['pesaje_id' => $pesaje->id]);
        $this->assertDatabaseHas('vehiculos_log', ['vehiculo_id' => $vehiculo->id, 'campo' => 'tara_kg']);
    }

    #[Test]
    public function test_update_tara_without_pesajes_needs_no_decision(): void
    {
        $tipo     = TipoVehiculo::factory()->create();
        $vehiculo = Vehiculo::factory()->create(['tara_kg' => 8000, 'tipo_vehiculo_id' => $tipo->id]);

        $this->actingAs($this->admin())
            ->put(route('admin.vehiculos.update', $vehiculo), [
                'patente'          => $vehiculo->patente,
                'numero_interno'   => $vehiculo->numero_interno,
                'tara_kg'          => 18000,
                'tipo_vehiculo_id' => $tipo->id,
                'titular'          => $vehiculo->titular,
            ])
            ->assertRedirect(route('admin.vehiculos.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('vehiculos', ['id' => $vehiculo->id, 'tara_kg' => 18000]);
        $this->assertDatabaseHas('vehiculos_log', ['vehiculo_id' => $vehiculo->id, 'campo' => 'tara_kg']);
    }

    // — Toggle ——————————————————————————————————————————————————

    #[Test]
    public function test_admin_can_deactivate_via_toggle(): void
    {
        $vehiculo = Vehiculo::factory()->create(['activo' => true]);

        $this->actingAs($this->admin())
            ->patch(route('admin.vehiculos.toggle', $vehiculo))
            ->assertRedirect(route('admin.vehiculos.index'));

        $this->assertDatabaseHas('vehiculos', ['id' => $vehiculo->id, 'activo' => false]);
    }

    #[Test]
    public function test_admin_can_activate_via_toggle(): void
    {
        $vehiculo = Vehiculo::factory()->create(['activo' => false]);

        $this->actingAs($this->admin())
            ->patch(route('admin.vehiculos.toggle', $vehiculo))
            ->assertRedirect(route('admin.vehiculos.index'));

        $this->assertDatabaseHas('vehiculos', ['id' => $vehiculo->id, 'activo' => true]);
    }

    // — Destroy ——————————————————————————————————————————————————

    #[Test]
    public function test_admin_can_destroy_vehiculo(): void
    {
        $vehiculo = Vehiculo::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.vehiculos.destroy', $vehiculo))
            ->assertRedirect(route('admin.vehiculos.index'));

        $this->assertDatabaseMissing('vehiculos', ['id' => $vehiculo->id]);
    }

    // — Acceso ——————————————————————————————————————————————————

    #[Test]
    public function test_operador_cannot_access_index(): void
    {
        $this->actingAs($this->operador())
            ->get(route('admin.vehiculos.index'))
            ->assertStatus(403);
    }

    #[Test]
    public function test_operador_cannot_store(): void
    {
        $this->actingAs($this->operador())
            ->post(route('admin.vehiculos.store'), $this->payload())
            ->assertStatus(403);
    }

    #[Test]
    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.vehiculos.index'))
            ->assertRedirect(route('login'));
    }

    // — Relación con tipos_vehiculo ———————————————————————————

    #[Test]
    public function test_tipos_vehiculo_appear_in_index_select(): void
    {
        $tipo = TipoVehiculo::factory()->create(['nombre' => 'Volquete']);

        $this->actingAs($this->admin())
            ->get(route('admin.vehiculos.index'))
            ->assertStatus(200)
            ->assertSee('Volquete');
    }

    #[Test]
    public function test_inactivating_tipo_vehiculo_does_not_cascade_to_vehiculos(): void
    {
        $tipo     = TipoVehiculo::factory()->create(['activo' => true]);
        $vehiculo = Vehiculo::factory()->create(['tipo_vehiculo_id' => $tipo->id, 'activo' => true]);

        // Desactivar el tipo no debe desactivar ni eliminar los vehículos asignados
        $tipo->update(['activo' => false]);

        $this->assertDatabaseHas('vehiculos', ['id' => $vehiculo->id, 'activo' => true]);
    }
}
