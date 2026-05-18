<?php

namespace Tests\Unit;

use App\Models\TipoServicio;
use App\Models\TipoVehiculo;
use App\Repositories\TipoServicioRepository;
use App\Services\TipoServicioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TipoServicioServiceTest extends TestCase
{
    use RefreshDatabase;

    private TipoServicioService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TipoServicioService(new TipoServicioRepository());
    }

    // — Crear ——————————————————————————————————————————————————

    #[Test]
    public function test_crear_almacena_con_tipo_vehiculo(): void
    {
        $tv   = TipoVehiculo::factory()->create();
        $tipo = $this->service->crear([
            'nombre'                    => 'Domiciliario',
            'tipo_vehiculo_sugerido_id' => $tv->id,
        ]);

        $this->assertInstanceOf(TipoServicio::class, $tipo);
        $this->assertDatabaseHas('tipos_servicio', [
            'nombre'                    => 'Domiciliario',
            'tipo_vehiculo_sugerido_id' => $tv->id,
            'activo'                    => true,
        ]);
    }

    #[Test]
    public function test_crear_sin_tipo_vehiculo_sugerido(): void
    {
        $tipo = $this->service->crear(['nombre' => 'Barrido']);

        $this->assertDatabaseHas('tipos_servicio', [
            'nombre'                    => 'Barrido',
            'tipo_vehiculo_sugerido_id' => null,
            'activo'                    => true,
        ]);
        $this->assertInstanceOf(TipoServicio::class, $tipo);
    }

    // — Activar / Desactivar ———————————————————————————————————

    #[Test]
    public function test_desactivar_sets_activo_false(): void
    {
        $tipo = TipoServicio::factory()->create(['activo' => true]);

        $this->service->desactivar($tipo);

        $this->assertDatabaseHas('tipos_servicio', ['id' => $tipo->id, 'activo' => false]);
    }

    #[Test]
    public function test_activar_sets_activo_true(): void
    {
        $tipo = TipoServicio::factory()->create(['activo' => false]);

        $this->service->activar($tipo);

        $this->assertDatabaseHas('tipos_servicio', ['id' => $tipo->id, 'activo' => true]);
    }

    // — Actualizar ——————————————————————————————————————————————

    #[Test]
    public function test_actualizar_modifica_nombre(): void
    {
        $tipo = TipoServicio::factory()->create(['nombre' => 'Antiguo']);

        $this->service->actualizar($tipo, ['nombre' => 'Nuevo']);

        $this->assertDatabaseHas('tipos_servicio', ['id' => $tipo->id, 'nombre' => 'Nuevo']);
        $this->assertDatabaseMissing('tipos_servicio', ['nombre' => 'Antiguo']);
    }

    #[Test]
    public function test_actualizar_cambia_tipo_vehiculo_sugerido(): void
    {
        $tvA  = TipoVehiculo::factory()->create();
        $tvB  = TipoVehiculo::factory()->create();
        $tipo = TipoServicio::factory()->create(['tipo_vehiculo_sugerido_id' => $tvA->id]);

        $this->service->actualizar($tipo, [
            'nombre'                    => $tipo->nombre,
            'tipo_vehiculo_sugerido_id' => $tvB->id,
        ]);

        $this->assertDatabaseHas('tipos_servicio', [
            'id'                        => $tipo->id,
            'tipo_vehiculo_sugerido_id' => $tvB->id,
        ]);
    }

    #[Test]
    public function test_actualizar_limpia_tipo_vehiculo_sugerido(): void
    {
        $tv   = TipoVehiculo::factory()->create();
        $tipo = TipoServicio::factory()->create(['tipo_vehiculo_sugerido_id' => $tv->id]);

        $this->service->actualizar($tipo, [
            'nombre'                    => $tipo->nombre,
            'tipo_vehiculo_sugerido_id' => null,
        ]);

        $this->assertDatabaseHas('tipos_servicio', [
            'id'                        => $tipo->id,
            'tipo_vehiculo_sugerido_id' => null,
        ]);
    }

    // — Eliminar ——————————————————————————————————————————————————

    #[Test]
    public function test_eliminar_removes_record(): void
    {
        $tipo = TipoServicio::factory()->create();

        $this->service->eliminar($tipo);

        $this->assertDatabaseMissing('tipos_servicio', ['id' => $tipo->id]);
    }

    // — Listar ——————————————————————————————————————————————————

    #[Test]
    public function test_listar_returns_all_records(): void
    {
        TipoServicio::factory()->count(4)->create();

        $resultado = $this->service->listar([]);

        $this->assertCount(4, $resultado);
    }

    #[Test]
    public function test_listar_filters_by_nombre(): void
    {
        TipoServicio::factory()->create(['nombre' => 'Domiciliario']);
        TipoServicio::factory()->create(['nombre' => 'Barrido']);
        TipoServicio::factory()->create(['nombre' => 'Barrido Especial']);

        $resultado = $this->service->listar(['nombre' => 'Barrid']);

        $this->assertCount(2, $resultado);
        $resultado->each(fn ($t) => $this->assertStringContainsStringIgnoringCase('Barrid', $t->nombre));
    }

    #[Test]
    public function test_listar_filters_by_activo(): void
    {
        TipoServicio::factory()->count(3)->create(['activo' => true]);
        TipoServicio::factory()->count(2)->create(['activo' => false]);

        $activos   = $this->service->listar(['activo' => '1']);
        $inactivos = $this->service->listar(['activo' => '0']);

        $this->assertCount(3, $activos);
        $this->assertCount(2, $inactivos);
    }

    #[Test]
    public function test_listar_filters_by_tipo_vehiculo_id(): void
    {
        $tvA = TipoVehiculo::factory()->create();
        $tvB = TipoVehiculo::factory()->create();
        TipoServicio::factory()->count(2)->create(['tipo_vehiculo_sugerido_id' => $tvA->id]);
        TipoServicio::factory()->create(['tipo_vehiculo_sugerido_id' => $tvB->id]);
        TipoServicio::factory()->create(['tipo_vehiculo_sugerido_id' => null]);

        $resultado = $this->service->listar(['tipo_vehiculo_id' => $tvA->id]);

        $this->assertCount(2, $resultado);
        $resultado->each(fn ($t) => $this->assertEquals($tvA->id, $t->tipo_vehiculo_sugerido_id));
    }

    #[Test]
    public function test_listar_eager_loads_tipo_vehiculo_cuando_asignado(): void
    {
        $tv   = TipoVehiculo::factory()->create(['nombre' => 'Compactador']);
        TipoServicio::factory()->create(['tipo_vehiculo_sugerido_id' => $tv->id]);

        $resultado = $this->service->listar([]);

        $this->assertTrue($resultado->first()->relationLoaded('tipoVehiculo'));
        $this->assertEquals('Compactador', $resultado->first()->tipoVehiculo->nombre);
    }

    #[Test]
    public function test_listar_tipo_vehiculo_es_null_cuando_no_asignado(): void
    {
        TipoServicio::factory()->create(['tipo_vehiculo_sugerido_id' => null]);

        $resultado = $this->service->listar([]);

        $this->assertTrue($resultado->first()->relationLoaded('tipoVehiculo'));
        $this->assertNull($resultado->first()->tipoVehiculo);
    }

    #[Test]
    public function test_listar_ignora_filtros_vacios(): void
    {
        TipoServicio::factory()->count(3)->create();

        $resultado = $this->service->listar(['nombre' => '', 'activo' => '', 'tipo_vehiculo_id' => '']);

        $this->assertCount(3, $resultado);
    }

    // — Scope ——————————————————————————————————————————————————

    #[Test]
    public function test_scope_activos_returns_only_active(): void
    {
        TipoServicio::factory()->count(3)->create(['activo' => true]);
        TipoServicio::factory()->count(2)->create(['activo' => false]);

        $activos = TipoServicio::activos()->get();

        $this->assertCount(3, $activos);
        $activos->each(fn ($t) => $this->assertTrue($t->activo));
    }
}
