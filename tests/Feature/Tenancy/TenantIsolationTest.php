<?php

namespace Tests\Feature\Tenancy;

use App\Models\Organizacion;
use App\Models\Pesaje;
use App\Models\TipoServicio;
use App\Models\TipoVehiculo;
use App\Models\User;
use App\Models\Vehiculo;
use App\Models\Zona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Invariante de seguridad #1: una organización no puede ver ni tocar datos de otra.
 *
 * El tenant activo lo resuelve ResolveOrganizacion desde session('organizacion_id'),
 * así que simulamos un request "dentro de una org" con actingAs + withSession.
 */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    /** Autentica un admin de $org y deja la org activa en la sesión del request. */
    private function actingAsAdminOf(Organizacion $org): User
    {
        $admin = $this->userInOrg($org, ['role' => 'admin']);
        $this->actingAs($admin)->withSession(['organizacion_id' => $org->id]);

        return $admin;
    }

    // ── Aislamiento en los index ──────────────────────────────────────

    #[Test]
    public function admin_no_ve_vehiculos_de_otra_organizacion(): void
    {
        $orgA = $this->createOrganizacion('Org A');
        $orgB = $this->createOrganizacion('Org B');
        $this->actingInOrg($orgA, fn () => Vehiculo::factory()->create(['patente' => 'AAA111']));
        $this->actingInOrg($orgB, fn () => Vehiculo::factory()->create(['patente' => 'BBB222']));

        $this->actingAsAdminOf($orgA);

        $this->get(route('admin.vehiculos.index'))
            ->assertOk()
            ->assertSee('AAA111')
            ->assertDontSee('BBB222');
    }

    #[Test]
    public function admin_no_ve_usuarios_de_otra_organizacion(): void
    {
        $orgA = $this->createOrganizacion('Org A');
        $orgB = $this->createOrganizacion('Org B');
        $this->userInOrg($orgA, ['name' => 'Ana de OrgA']);
        $this->userInOrg($orgB, ['name' => 'Beto de OrgB']);

        $this->actingAsAdminOf($orgA);

        $this->get(route('admin.usuarios.index'))
            ->assertOk()
            ->assertSee('Ana de OrgA')
            ->assertDontSee('Beto de OrgB');
    }

    // ── Acceso por id/uuid a recursos de otra org → 404 ───────────────

    #[Test]
    public function acceder_a_un_vehiculo_de_otra_org_por_id_da_404(): void
    {
        $orgA = $this->createOrganizacion('Org A');
        $orgB = $this->createOrganizacion('Org B');
        $vehiculoB = $this->actingInOrg($orgB, fn () => Vehiculo::factory()->create());

        $this->actingAsAdminOf($orgA);

        // El route-model binding aplica el global scope de la org activa (A) → no lo encuentra.
        $this->patch(route('admin.vehiculos.toggle', $vehiculoB))
            ->assertNotFound();
    }

    #[Test]
    public function acceder_a_un_usuario_de_otra_org_por_id_da_404(): void
    {
        $orgA = $this->createOrganizacion('Org A');
        $orgB = $this->createOrganizacion('Org B');
        $usuarioB = $this->userInOrg($orgB);

        $this->actingAsAdminOf($orgA);

        $this->patch(route('admin.usuarios.toggle', $usuarioB))
            ->assertNotFound();
    }

    #[Test]
    public function acceder_a_un_pesaje_de_otra_org_da_404(): void
    {
        $orgA = $this->createOrganizacion('Org A');
        $orgB = $this->createOrganizacion('Org B');
        $pesajeB = $this->actingInOrg($orgB, fn () => Pesaje::factory()->create());

        $this->actingAsAdminOf($orgA);

        $this->get(route('pesajes.show', $pesajeB))
            ->assertNotFound();
    }

    #[Test]
    public function cancelar_un_pesaje_de_otra_org_da_404(): void
    {
        $orgA = $this->createOrganizacion('Org A');
        $orgB = $this->createOrganizacion('Org B');
        $pesajeB = $this->actingInOrg($orgB, fn () => Pesaje::factory()->create(['estado' => 'En predio']));

        $this->actingAsAdminOf($orgA);

        $this->patch(route('pesajes.cancelar', $pesajeB), ['motivo' => 'Intento cross-org.'])
            ->assertNotFound();

        $this->assertDatabaseHas('pesajes', ['id' => $pesajeB->id, 'estado' => 'En predio']);
    }

    // ── Creación asigna la org del actuante ───────────────────────────

    #[Test]
    public function crear_un_vehiculo_lo_asigna_a_la_org_del_actuante(): void
    {
        $orgA = $this->createOrganizacion('Org A');
        $tipo = $this->actingInOrg($orgA, fn () => TipoVehiculo::factory()->create());

        $this->actingAsAdminOf($orgA);

        $this->post(route('admin.vehiculos.store'), [
            'patente'          => 'NEW999',
            'numero_interno'   => '777',
            'tara_kg'          => 5000,
            'tipo_vehiculo_id' => $tipo->id,
            'titular'          => 'Municipalidad X',
        ])->assertRedirect(route('admin.vehiculos.index'));

        $this->assertDatabaseHas('vehiculos', [
            'patente'         => 'NEW999',
            'organizacion_id' => $orgA->id,
        ]);
    }

    #[Test]
    public function crear_un_pesaje_lo_asigna_a_la_org_del_operador(): void
    {
        $orgA = $this->createOrganizacion('Org A');
        [$vehiculo, $servicio, $zona] = $this->actingInOrg($orgA, fn () => [
            Vehiculo::factory()->create(),
            TipoServicio::factory()->create(),
            Zona::factory()->create(),
        ]);
        $operador = $this->userInOrg($orgA);

        $this->actingAs($operador)->withSession(['organizacion_id' => $orgA->id]);

        $this->post(route('pesajes.store'), [
            'vehiculo_id'      => $vehiculo->id,
            'tipo_servicio_id' => $servicio->id,
            'zona_id'          => $zona->id,
            'turno'            => 'Diurna',
            'peso_bruto_kg'    => 20000,
        ]);

        $this->assertDatabaseHas('pesajes', [
            'vehiculo_id'     => $vehiculo->id,
            'organizacion_id' => $orgA->id,
        ]);
    }
}
