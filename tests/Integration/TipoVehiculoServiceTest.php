<?php

namespace Tests\Integration;

use App\Models\TipoVehiculo;
use App\Repositories\TipoVehiculoRepository;
use App\Services\TipoVehiculoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TipoVehiculoServiceTest extends TestCase
{
    use RefreshDatabase;

    private TipoVehiculoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TipoVehiculoService(new TipoVehiculoRepository);
    }

    #[Test]
    public function test_create_stores_record(): void
    {
        $tipo = $this->service->crear([
            'nombre'      => 'Compactador',
            'peso_min_kg' => 8000,
            'peso_max_kg' => 30000,
        ]);

        $this->assertDatabaseHas('tipos_vehiculo', [
            'nombre'      => 'Compactador',
            'peso_min_kg' => 8000,
            'peso_max_kg' => 30000,
            'activo'      => true,
        ]);
        $this->assertInstanceOf(TipoVehiculo::class, $tipo);
    }

    #[Test]
    public function test_deactivate_sets_activo_false(): void
    {
        $tipo = TipoVehiculo::factory()->create(['activo' => true]);

        $this->service->desactivar($tipo);

        $this->assertDatabaseHas('tipos_vehiculo', ['id' => $tipo->id, 'activo' => false]);
    }

    #[Test]
    public function test_activate_sets_activo_true(): void
    {
        $tipo = TipoVehiculo::factory()->create(['activo' => false]);

        $this->service->activar($tipo);

        $this->assertDatabaseHas('tipos_vehiculo', ['id' => $tipo->id, 'activo' => true]);
    }

    #[Test]
    public function test_update_modifies_rangos(): void
    {
        $tipo = TipoVehiculo::factory()->create([
            'peso_min_kg' => 1000,
            'peso_max_kg' => 5000,
        ]);

        $this->service->actualizar($tipo, [
            'nombre'      => $tipo->nombre,
            'peso_min_kg' => 2000,
            'peso_max_kg' => 8000,
        ]);

        $this->assertDatabaseHas('tipos_vehiculo', [
            'id'          => $tipo->id,
            'peso_min_kg' => 2000,
            'peso_max_kg' => 8000,
        ]);
    }
}
