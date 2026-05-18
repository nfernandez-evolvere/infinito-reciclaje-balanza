<?php

namespace Tests\Unit;

use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
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
        $this->service = new VehiculoService(new VehiculoRepository());
        $this->tipo    = TipoVehiculo::factory()->create();
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

        $this->service->actualizar($vehiculo, [
            'patente'          => 'BBB111',
            'numero_interno'   => $vehiculo->numero_interno,
            'tara_kg'          => 6000,
            'tipo_vehiculo_id' => $vehiculo->tipo_vehiculo_id,
            'titular'          => $vehiculo->titular,
        ]);

        $this->assertDatabaseHas('vehiculos', [
            'id'      => $vehiculo->id,
            'patente' => 'BBB111',
            'tara_kg' => 6000,
        ]);
        $this->assertDatabaseMissing('vehiculos', ['patente' => 'AAA000']);
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
