<?php

namespace Tests\Integration;

use App\Models\TipoServicio;
use App\Models\TipoVehiculo;
use App\Models\User;
use App\Models\Vehiculo;
use App\Models\Zona;
use App\Models\ZonaServicio;
use App\Services\ConfiguracionInicialService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ConfiguracionInicialServiceTest extends TestCase
{
    use RefreshDatabase;

    private ConfiguracionInicialService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ConfiguracionInicialService;
        ConfiguracionInicialService::forgetCache();
    }

    /** Devuelve el paso con la etiqueta dada del resultado de getProgress(). */
    private function step(array $progress, string $label): array
    {
        return collect($progress['steps'])->firstWhere('label', $label);
    }

    // — Estructura y estado inicial ————————————————————————————

    #[Test]
    public function progress_reports_five_pending_steps_for_a_fresh_organization(): void
    {
        $progress = $this->service->getProgress();

        $this->assertSame(5, $progress['total']);
        $this->assertSame(0, $progress['completado']);
        $this->assertSame(0, $progress['porcentaje']);

        $this->assertSame(
            ['Tipos de vehículo', 'Tipos de servicio', 'Zonas con servicios', 'Vehículos cargados', 'Operadores creados'],
            collect($progress['steps'])->pluck('label')->all(),
        );

        foreach ($progress['steps'] as $step) {
            $this->assertFalse($step['done'], "El paso '{$step['label']}' no debería estar completo en una org vacía.");
        }
    }

    // — Paso "Operadores creados": aislamiento multi-tenant ————————

    #[Test]
    public function operadores_step_is_done_with_an_active_operador_in_the_current_org(): void
    {
        User::factory()->create(['role' => 'operador', 'activo' => true]);

        $progress = $this->service->getProgress();

        $this->assertTrue($this->step($progress, 'Operadores creados')['done']);
        $this->assertSame(1, $progress['completado']);
        $this->assertSame(20, $progress['porcentaje']);
    }

    #[Test]
    public function operadores_step_ignores_operadores_from_other_organizations(): void
    {
        // La org actual sólo tiene un administrador cargado (no un operario).
        User::factory()->admin()->create(['activo' => true]);

        // Otra organización SÍ tiene un operador activo: no debe filtrarse.
        $otraOrg = $this->createOrganizacion('Otra Organización');
        $this->userInOrg($otraOrg, ['role' => 'operador', 'activo' => true]);

        $progress = $this->service->getProgress();

        $this->assertFalse(
            $this->step($progress, 'Operadores creados')['done'],
            'Un operador de otra organización no debe marcar el paso como completo.',
        );
        $this->assertSame(0, $progress['completado']);
        $this->assertSame(0, $progress['porcentaje']);
    }

    #[Test]
    public function operadores_step_ignores_admins_of_the_current_org(): void
    {
        User::factory()->admin()->create(['activo' => true]);

        $progress = $this->service->getProgress();

        $this->assertFalse($this->step($progress, 'Operadores creados')['done']);
        $this->assertSame(0, $progress['completado']);
    }

    #[Test]
    public function operadores_step_ignores_inactive_operadores(): void
    {
        User::factory()->inactive()->create(['role' => 'operador']);

        $progress = $this->service->getProgress();

        $this->assertFalse($this->step($progress, 'Operadores creados')['done']);
        $this->assertSame(0, $progress['completado']);
    }

    // — Cálculo de progreso —————————————————————————————————————

    #[Test]
    public function progress_counts_only_the_completed_steps(): void
    {
        // 2 de 5 pasos: tipos de vehículo y tipos de servicio.
        TipoVehiculo::factory()->create(['activo' => true]);
        TipoServicio::factory()->create(['activo' => true]);

        $progress = $this->service->getProgress();

        $this->assertTrue($this->step($progress, 'Tipos de vehículo')['done']);
        $this->assertTrue($this->step($progress, 'Tipos de servicio')['done']);
        $this->assertFalse($this->step($progress, 'Zonas con servicios')['done']);
        $this->assertFalse($this->step($progress, 'Vehículos cargados')['done']);
        $this->assertFalse($this->step($progress, 'Operadores creados')['done']);

        $this->assertSame(2, $progress['completado']);
        $this->assertSame(40, $progress['porcentaje']);
    }

    #[Test]
    public function progress_is_complete_when_every_step_is_satisfied(): void
    {
        $tipoVehiculo = TipoVehiculo::factory()->create(['activo' => true]);
        $tipoServicio = TipoServicio::factory()->create(['activo' => true]);

        $zona = Zona::factory()->create(['activo' => true]);
        ZonaServicio::create(['zona_id' => $zona->id, 'tipo_servicio_id' => $tipoServicio->id]);

        Vehiculo::factory()->create(['tipo_vehiculo_id' => $tipoVehiculo->id, 'activo' => true]);
        User::factory()->create(['role' => 'operador', 'activo' => true]);

        $progress = $this->service->getProgress();

        $this->assertSame(5, $progress['completado']);
        $this->assertSame(100, $progress['porcentaje']);

        foreach ($progress['steps'] as $step) {
            $this->assertTrue($step['done'], "El paso '{$step['label']}' debería estar completo.");
        }
    }

    #[Test]
    public function zonas_step_requires_a_zona_with_at_least_one_servicio(): void
    {
        // Una zona activa pero SIN servicios asociados no completa el paso.
        Zona::factory()->create(['activo' => true]);

        $progress = $this->service->getProgress();

        $this->assertFalse($this->step($progress, 'Zonas con servicios')['done']);
        $this->assertSame(0, $progress['completado']);
    }
}
