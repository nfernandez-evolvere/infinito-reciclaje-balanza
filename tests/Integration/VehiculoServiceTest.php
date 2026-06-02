<?php

namespace Tests\Integration;

use App\Models\Pesaje;
use App\Models\TipoVehiculo;
use App\Models\User;
use App\Models\Vehiculo;
use App\Repositories\PesajeRepository;
use App\Repositories\VehiculoLogRepository;
use App\Repositories\VehiculoRepository;
use App\Services\VehiculoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VehiculoServiceTest extends TestCase
{
    use RefreshDatabase;

    private VehiculoService $service;
    private TipoVehiculo $tipo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new VehiculoService(
            new VehiculoRepository(),
            new VehiculoLogRepository(),
            new PesajeRepository(),
        );
        $this->tipo = TipoVehiculo::factory()->create();
    }

    #[Test]
    public function test_create_stores_record(): void
    {
        $vehiculo = $this->service->crear([
            'patente'          => 'ABC123',
            'numero_interno'   => '042',
            'tara_kg'          => 5000,
            'tipo_vehiculo_id' => $this->tipo->id,
            'titular'          => 'Municipalidad de Corrientes',
        ]);

        $this->assertDatabaseHas('vehiculos', [
            'patente'          => 'ABC123',
            'numero_interno'   => '042',
            'tara_kg'          => 5000,
            'tipo_vehiculo_id' => $this->tipo->id,
            'titular'          => 'Municipalidad de Corrientes',
            'activo'           => true,
        ]);
        $this->assertInstanceOf(Vehiculo::class, $vehiculo);
    }

    #[Test]
    public function test_create_stores_optional_fields(): void
    {
        $vehiculo = $this->service->crear([
            'patente'          => 'XYZ999',
            'numero_interno'   => '001',
            'tara_kg'          => 4500,
            'tipo_vehiculo_id' => $this->tipo->id,
            'titular'          => 'Empresa Privada',
            'capacidad_kg'     => 12000,
            'observaciones'    => 'Requiere asistencia para maniobrar.',
        ]);

        $this->assertDatabaseHas('vehiculos', [
            'patente'       => 'XYZ999',
            'capacidad_kg'  => 12000,
            'observaciones' => 'Requiere asistencia para maniobrar.',
        ]);
    }

    #[Test]
    public function test_deactivate_sets_activo_false(): void
    {
        $vehiculo = Vehiculo::factory()->create(['activo' => true]);

        $this->service->desactivar($vehiculo);

        $this->assertDatabaseHas('vehiculos', ['id' => $vehiculo->id, 'activo' => false]);
    }

    #[Test]
    public function test_activate_sets_activo_true(): void
    {
        $vehiculo = Vehiculo::factory()->create(['activo' => false]);

        $this->service->activar($vehiculo);

        $this->assertDatabaseHas('vehiculos', ['id' => $vehiculo->id, 'activo' => true]);
    }

    #[Test]
    public function test_update_modifies_patente_and_tara(): void
    {
        $vehiculo = Vehiculo::factory()->create([
            'patente' => 'AAA000',
            'tara_kg' => 3000,
        ]);

        $this->service->update($vehiculo, [
            'patente'          => 'BBB111',
            'numero_interno'   => $vehiculo->numero_interno,
            'tara_kg'          => 6000,
            'tipo_vehiculo_id' => $vehiculo->tipo_vehiculo_id,
            'titular'          => $vehiculo->titular,
        ], User::factory()->create());

        $this->assertDatabaseHas('vehiculos', [
            'id'      => $vehiculo->id,
            'patente' => 'BBB111',
            'tara_kg' => 6000,
        ]);
        $this->assertDatabaseMissing('vehiculos', ['patente' => 'AAA000']);
    }

    // — Corrección de tara: recálculo y auditoría ————————————————

    #[Test]
    public function test_update_corregir_dato_recalculates_pesajes(): void
    {
        $vehiculo = Vehiculo::factory()->create(['tara_kg' => 8000, 'tipo_vehiculo_id' => $this->tipo->id]);
        $usuario  = User::factory()->create();

        $pesaje = Pesaje::factory()->create([
            'vehiculo_id'   => $vehiculo->id,
            'peso_bruto_kg' => 20000,
            'peso_tara_kg'  => 8000,
            'peso_neto_kg'  => 12000,
            'estado'        => 'Cerrado',
            'editado'       => false,
        ]);

        $this->service->update($vehiculo, [
            'tara_kg'         => 18000,
            '_intencion_tara' => 'corregir_dato',
            '_motivo_tara'    => 'Se había cargado 8.000 en vez de 18.000.',
        ], $usuario);

        $pesaje->refresh();
        $this->assertSame(18000, $pesaje->peso_tara_kg);
        $this->assertSame(2000, $pesaje->peso_neto_kg);
        $this->assertTrue($pesaje->editado);

        $this->assertDatabaseHas('pesajes_log', [
            'pesaje_id'      => $pesaje->id,
            'campo'          => 'peso_tara_kg',
            'valor_anterior' => '8000',
            'valor_nuevo'    => '18000',
        ]);
        $this->assertDatabaseHas('pesajes_log', [
            'pesaje_id'   => $pesaje->id,
            'campo'       => 'peso_neto_kg',
            'valor_nuevo' => '2000',
        ]);
        $this->assertDatabaseHas('vehiculos_log', [
            'vehiculo_id'    => $vehiculo->id,
            'campo'          => 'tara_kg',
            'valor_anterior' => '8000',
            'valor_nuevo'    => '18000',
        ]);
    }

    #[Test]
    public function test_update_corregir_dato_audita_tara_sin_neto_cuando_el_neto_no_cambia(): void
    {
        // Bruto menor que ambas taras: el neto queda clampeado a 0 antes y después,
        // así que cambia la tara pero NO el neto → solo debe auditarse la tara.
        $vehiculo = Vehiculo::factory()->create(['tara_kg' => 8000, 'tipo_vehiculo_id' => $this->tipo->id]);
        $usuario  = User::factory()->create();

        $pesaje = Pesaje::factory()->create([
            'vehiculo_id'   => $vehiculo->id,
            'peso_bruto_kg' => 5000,
            'peso_tara_kg'  => 8000,
            'peso_neto_kg'  => 0,
            'estado'        => 'Cerrado',
            'editado'       => false,
        ]);

        $this->service->update($vehiculo, [
            'tara_kg'         => 9000,
            '_intencion_tara' => 'corregir_dato',
            '_motivo_tara'    => 'Ajuste de tara.',
        ], $usuario);

        $pesaje->refresh();
        $this->assertSame(9000, $pesaje->peso_tara_kg);
        $this->assertSame(0, $pesaje->peso_neto_kg);
        $this->assertTrue($pesaje->editado);

        $this->assertDatabaseHas('pesajes_log', [
            'pesaje_id' => $pesaje->id,
            'campo'     => 'peso_tara_kg',
        ]);
        $this->assertDatabaseMissing('pesajes_log', [
            'pesaje_id' => $pesaje->id,
            'campo'     => 'peso_neto_kg',
        ]);
    }

    #[Test]
    public function test_update_cambio_real_keeps_pesajes_but_audits(): void
    {
        $vehiculo = Vehiculo::factory()->create(['tara_kg' => 8000, 'tipo_vehiculo_id' => $this->tipo->id]);
        $usuario  = User::factory()->create();

        $pesaje = Pesaje::factory()->create([
            'vehiculo_id'   => $vehiculo->id,
            'peso_bruto_kg' => 20000,
            'peso_tara_kg'  => 8000,
            'peso_neto_kg'  => 12000,
            'estado'        => 'Cerrado',
            'editado'       => false,
        ]);

        $this->service->update($vehiculo, [
            'tara_kg'         => 18000,
            '_intencion_tara' => 'cambio_real',
            '_motivo_tara'    => 'Se le agregó una caja compactadora.',
        ], $usuario);

        $pesaje->refresh();
        $this->assertSame(8000, $pesaje->peso_tara_kg);
        $this->assertSame(12000, $pesaje->peso_neto_kg);
        $this->assertFalse($pesaje->editado);

        $this->assertDatabaseMissing('pesajes_log', ['pesaje_id' => $pesaje->id]);
        $this->assertDatabaseHas('vehiculos_log', [
            'vehiculo_id' => $vehiculo->id,
            'campo'       => 'tara_kg',
            'valor_nuevo' => '18000',
        ]);
    }

    #[Test]
    public function test_update_corregir_dato_excludes_cancelados(): void
    {
        $vehiculo = Vehiculo::factory()->create(['tara_kg' => 8000, 'tipo_vehiculo_id' => $this->tipo->id]);
        $usuario  = User::factory()->create();

        $cancelado = Pesaje::factory()->cancelado()->create([
            'vehiculo_id'   => $vehiculo->id,
            'peso_bruto_kg' => 20000,
            'peso_tara_kg'  => 8000,
            'peso_neto_kg'  => 12000,
        ]);

        $this->service->update($vehiculo, [
            'tara_kg'         => 18000,
            '_intencion_tara' => 'corregir_dato',
            '_motivo_tara'    => 'Corrección de carga.',
        ], $usuario);

        $cancelado->refresh();
        $this->assertSame(8000, $cancelado->peso_tara_kg);
        $this->assertSame(12000, $cancelado->peso_neto_kg);
        $this->assertDatabaseMissing('pesajes_log', ['pesaje_id' => $cancelado->id]);
    }

    #[Test]
    public function test_update_without_tara_change_does_not_audit(): void
    {
        $vehiculo = Vehiculo::factory()->create(['tara_kg' => 8000, 'tipo_vehiculo_id' => $this->tipo->id]);

        $this->service->update($vehiculo, [
            'patente'          => $vehiculo->patente,
            'numero_interno'   => $vehiculo->numero_interno,
            'tara_kg'          => 8000,
            'tipo_vehiculo_id' => $vehiculo->tipo_vehiculo_id,
            'titular'          => 'Nuevo titular',
        ], User::factory()->create());

        $this->assertDatabaseCount('vehiculos_log', 0);
    }

    #[Test]
    public function test_eliminar_removes_record(): void
    {
        $vehiculo = Vehiculo::factory()->create();

        $this->service->eliminar($vehiculo);

        $this->assertDatabaseMissing('vehiculos', ['id' => $vehiculo->id]);
    }

    #[Test]
    public function test_listar_returns_paginated_results(): void
    {
        Vehiculo::factory()->count(3)->create(['tipo_vehiculo_id' => $this->tipo->id]);

        $resultado = $this->service->listar([]);

        $this->assertCount(3, $resultado);
    }

    #[Test]
    public function test_listar_filters_by_patente(): void
    {
        Vehiculo::factory()->create(['patente' => 'ZZZ999']);
        Vehiculo::factory()->create(['patente' => 'AAA111']);

        $resultado = $this->service->listar(['patente' => 'ZZZ']);

        $this->assertCount(1, $resultado);
        $this->assertEquals('ZZZ999', $resultado->first()->patente);
    }

    #[Test]
    public function test_listar_filters_by_activo(): void
    {
        Vehiculo::factory()->create(['activo' => true]);
        Vehiculo::factory()->create(['activo' => true]);
        Vehiculo::factory()->create(['activo' => false]);

        $activos   = $this->service->listar(['activo' => '1']);
        $inactivos = $this->service->listar(['activo' => '0']);

        $this->assertCount(2, $activos);
        $this->assertCount(1, $inactivos);
    }

    #[Test]
    public function test_listar_filters_by_tipo_vehiculo(): void
    {
        $otroTipo = TipoVehiculo::factory()->create();
        Vehiculo::factory()->create(['tipo_vehiculo_id' => $this->tipo->id]);
        Vehiculo::factory()->create(['tipo_vehiculo_id' => $this->tipo->id]);
        Vehiculo::factory()->create(['tipo_vehiculo_id' => $otroTipo->id]);

        $resultado = $this->service->listar(['tipo_vehiculo_id' => $this->tipo->id]);

        $this->assertCount(2, $resultado);
        $resultado->each(fn ($v) => $this->assertEquals($this->tipo->id, $v->tipo_vehiculo_id));
    }

    #[Test]
    public function test_listar_eager_loads_tipo_vehiculo(): void
    {
        Vehiculo::factory()->create(['tipo_vehiculo_id' => $this->tipo->id]);

        $resultado = $this->service->listar([]);

        $this->assertTrue($resultado->first()->relationLoaded('tipoVehiculo'));
        $this->assertEquals($this->tipo->nombre, $resultado->first()->tipoVehiculo->nombre);
    }
}
