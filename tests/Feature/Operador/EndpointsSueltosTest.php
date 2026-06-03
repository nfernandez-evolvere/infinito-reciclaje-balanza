<?php

namespace Tests\Feature\Operador;

use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EndpointsSueltosTest extends TestCase
{
    use RefreshDatabase;

    // ── VehiculoController@buscar (autocomplete de balanza) ───────────

    #[Test]
    public function buscar_retorna_vehiculos_que_coinciden_con_la_patente(): void
    {
        $tipo = TipoVehiculo::factory()->create(['peso_min_kg' => 5000, 'peso_max_kg' => 30000]);
        Vehiculo::factory()->create(['patente' => 'ABC123', 'tipo_vehiculo_id' => $tipo->id]);
        Vehiculo::factory()->create(['patente' => 'XYZ999', 'tipo_vehiculo_id' => $tipo->id]);

        $this->actingAs($this->operador())
            ->getJson(route('vehiculos.buscar', ['q' => 'ABC']))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.patente', 'ABC123')
            ->assertJsonStructure(['*' => ['id', 'patente', 'interno', 'tara', 'tipo', 'titular', 'peso_min', 'peso_max']]);
    }

    #[Test]
    public function buscar_retorna_vehiculos_que_coinciden_con_el_numero_interno(): void
    {
        Vehiculo::factory()->create(['numero_interno' => '042']);
        Vehiculo::factory()->create(['numero_interno' => '099']);

        $this->actingAs($this->operador())
            ->getJson(route('vehiculos.buscar', ['q' => '042']))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.interno', '042');
    }

    #[Test]
    public function buscar_retorna_array_vacio_con_query_en_blanco(): void
    {
        Vehiculo::factory()->create();

        $this->actingAs($this->operador())
            ->getJson(route('vehiculos.buscar', ['q' => '']))
            ->assertOk()
            ->assertExactJson([]);
    }

    #[Test]
    public function buscar_respeta_el_scope_de_la_organizacion(): void
    {
        // El global scope BelongsToOrganizacion filtra por la org activa.
        // Los vehículos de otra org no deben aparecer en el autocomplete.
        $orgA = $this->createOrganizacion('Org A');
        $orgB = $this->createOrganizacion('Org B');

        $this->actingInOrg($orgB, fn () => Vehiculo::factory()->create(['patente' => 'ORG_B_01']));

        app()->instance('organizacion', $orgA);

        $this->actingAs($this->userInOrg($orgA))
            ->getJson(route('vehiculos.buscar', ['q' => 'ORG_B']))
            ->assertOk()
            ->assertExactJson([]);
    }

    #[Test]
    public function buscar_requiere_autenticacion(): void
    {
        $this->getJson(route('vehiculos.buscar', ['q' => 'ABC']))
            ->assertUnauthorized();
    }

    // ── OnboardingController@store ────────────────────────────────────

    #[Test]
    public function onboarding_visto_marca_el_flag_del_usuario_autenticado(): void
    {
        $user = $this->operador(['onboarding_visto' => false]);

        $this->actingAs($user)
            ->postJson(route('onboarding.visto'))
            ->assertOk()
            ->assertExactJson(['ok' => true]);

        $this->assertDatabaseHas('users', [
            'id'               => $user->id,
            'onboarding_visto' => true,
        ]);
    }

    #[Test]
    public function onboarding_visto_requiere_autenticacion(): void
    {
        $this->postJson(route('onboarding.visto'))
            ->assertUnauthorized();
    }

    #[Test]
    public function onboarding_visto_no_afecta_a_otros_usuarios(): void
    {
        $user = $this->operador(['onboarding_visto' => false]);
        $otro = $this->operador(['onboarding_visto' => false]);

        $this->actingAs($user)->postJson(route('onboarding.visto'));

        $this->assertDatabaseHas('users', ['id' => $otro->id, 'onboarding_visto' => false]);
    }
}
