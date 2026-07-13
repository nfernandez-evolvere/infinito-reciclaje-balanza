<?php

namespace Tests\Feature\Tenancy;

use App\Jobs\GenerarReporteJob;
use App\Models\Organizacion;
use App\Models\Pesaje;
use App\Models\ReporteGenerado;
use App\Models\ReporteProgramado;
use App\Models\TipoServicio;
use App\Models\TipoVehiculo;
use App\Models\User;
use App\Models\Vehiculo;
use App\Models\Zona;
use App\Services\PdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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

    #[Test]
    public function egreso_sobre_pesaje_de_otra_org_da_404(): void
    {
        $orgA = $this->createOrganizacion('Org A');
        $orgB = $this->createOrganizacion('Org B');
        $pesajeB = $this->actingInOrg($orgB, fn () => Pesaje::factory()->enPredio()->create());

        $this->actingAsAdminOf($orgA);

        $this->post(route('pesajes.egreso', $pesajeB))
            ->assertNotFound();

        $this->assertDatabaseHas('pesajes', ['id' => $pesajeB->id, 'estado' => 'En predio']);
    }

    #[Test]
    public function update_sobre_pesaje_de_otra_org_da_404(): void
    {
        $orgA = $this->createOrganizacion('Org A');
        $orgB = $this->createOrganizacion('Org B');
        $pesajeB = $this->actingInOrg($orgB, fn () => Pesaje::factory()->create([
            'peso_bruto_kg' => 20000,
            'peso_neto_kg'  => 12000,
        ]));

        $this->actingAsAdminOf($orgA);

        $this->put(route('pesajes.update', $pesajeB), ['motivo' => 'Intento cross-org.'])
            ->assertNotFound();

        // Los datos no deben haber cambiado.
        $this->assertDatabaseHas('pesajes', ['id' => $pesajeB->id, 'peso_neto_kg' => 12000]);
        $this->assertDatabaseCount('pesajes_log', 0);
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

    // ── Background jobs: aislamiento sin contexto HTTP ───────────────────

    #[Test]
    public function job_de_reporte_no_mezcla_pesajes_entre_organizaciones(): void
    {
        // Invariante: un job de reporte programado solo puede ver los pesajes
        // de su propia organización, aunque corra sin contexto HTTP (sin sesión,
        // sin middleware ResolveOrganizacion).
        $orgA = $this->createOrganizacion('Org A');
        $orgB = $this->createOrganizacion('Org B');

        // 4 pesajes de org A (1500 kg c/u → 6 t en total)
        $this->actingInOrg($orgA, function () {
            Pesaje::factory()->count(4)->create([
                'estado'       => 'Cerrado',
                'peso_neto_kg' => 1500,
                'created_at'   => now()->subDays(3)->format('Y-m-d\TH:i:s'),
                'updated_at'   => now()->subDays(3)->format('Y-m-d\TH:i:s'),
            ]);
        });

        // 6 pesajes de org B (3000 kg c/u → 18 t). Sin aislamiento se sumarían:
        // total=10, toneladas=24. Con aislamiento correcto: total=4, toneladas=6.
        $this->actingInOrg($orgB, function () {
            Pesaje::factory()->count(6)->create([
                'estado'       => 'Cerrado',
                'peso_neto_kg' => 3000,
                'created_at'   => now()->subDays(3)->format('Y-m-d\TH:i:s'),
                'updated_at'   => now()->subDays(3)->format('Y-m-d\TH:i:s'),
            ]);
        });

        [$programado, $generado] = $this->actingInOrg($orgA, function () {
            $programado = ReporteProgramado::create([
                'tipo'           => 'informe_mensual',
                'nombre'         => 'Mensual Org A',
                'frecuencia'     => 'mensual',
                'cron_expresion' => '0 8 1 * *',
                'destinatarios'  => ['a@test.com'],
                'activo'         => true,
                'opciones'       => ['formatos' => ['pdf']],
            ]);

            // El registro lo crea iniciarGeneracion al despachar; acá lo armamos
            // igual para invocar el job directamente con su id.
            $generado = ReporteGenerado::create([
                'reporte_programado_id' => $programado->id,
                'origen'                => 'programado',
                'tipo'                  => 'informe_mensual',
                'formato'               => 'pdf',
                'periodo_desde'         => now()->subDays(30)->toDateString(),
                'periodo_hasta'         => now()->toDateString(),
                'destinatarios'         => ['a@test.com'],
                'estado'                => ReporteGenerado::ESTADO_GENERANDO,
            ]);

            return [$programado, $generado];
        });

        $this->instance(PdfService::class, \Mockery::mock(PdfService::class, function ($m) {
            $m->shouldReceive('fromView')->andReturn('pdf');
        }));

        Mail::fake();
        // Sin binding de organización: replica el contexto del worker de cola
        app()->forgetInstance('organizacion');

        GenerarReporteJob::dispatchSync($generado->id);

        // Solo los 4 pesajes de Org A deben aparecer en el snapshot congelado;
        // Org B no puede cruzar.
        $kpis = ReporteGenerado::withoutGlobalScopes()->find($generado->id)->snapshot['kpis'];
        $this->assertSame(4, $kpis['total']);
        // 4 × 1500 kg = 6000 kg = 6 t
        $this->assertEqualsWithDelta(6.0, $kpis['toneladas'], 0.01);
    }

    #[Test]
    public function crear_un_pesaje_lo_asigna_a_la_org_del_operador(): void
    {
        $orgA = $this->createOrganizacion('Org A');
        [$vehiculo, $servicio, $zona] = $this->actingInOrg($orgA, function () {
            // Tipo con tope amplio: el payload usa peso_bruto_kg = 20.000 y no debe
            // chocar con el tope duro aleatorio (peso_max × 2) de TipoVehiculo::factory().
            $tipo = TipoVehiculo::factory()->create(['peso_min_kg' => 5000, 'peso_max_kg' => 30000]);

            return [
                Vehiculo::factory()->create(['tara_kg' => 8000, 'tipo_vehiculo_id' => $tipo->id]),
                TipoServicio::factory()->create(),
                Zona::factory()->create(),
            ];
        });
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
