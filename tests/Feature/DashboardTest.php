<?php

namespace Tests\Feature;

use App\Models\Pesaje;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function operador(): User
    {
        return User::factory()->create();
    }

    // ── Protección de rutas ───────────────────────────────────────────

    #[Test]
    public function admin_puede_acceder_al_dashboard(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    #[Test]
    public function operador_no_puede_acceder_al_dashboard(): void
    {
        $this->actingAs($this->operador())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    #[Test]
    public function invitado_es_redirigido_al_login(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    // ── Endpoint /admin/dashboard/data ────────────────────────────────

    #[Test]
    public function data_endpoint_devuelve_json_con_estructura_correcta(): void
    {
        $this->actingAs($this->admin())
            ->getJson(route('admin.dashboard.data'))
            ->assertOk()
            ->assertJsonStructure([
                'kpisDia'  => ['total', 'toneladas', 'promedio', 'ultimo_hace_min',
                               'delta', 'delta_base', 'delta_toneladas'],
                'kpisMes'  => ['total', 'toneladas', 'dias_op', 'delta', 'delta_base'],
                'evolucion7'  => ['datos', 'promedio'],
                'evolucion15' => ['datos', 'promedio'],
                'evolucion90' => ['datos', 'promedio'],
                'desgloseVehiculo',
                'desgloseZona',
                'desgloseVehiculoMes',
                'desgloseZonaMes',
                'alertas',
            ]);
    }

    #[Test]
    public function data_con_rango_valido_incluye_claves_de_rango(): void
    {
        $desde = today()->subDays(7)->format('Y-m-d');
        $hasta = today()->format('Y-m-d');

        $this->actingAs($this->admin())
            ->getJson(route('admin.dashboard.data', compact('desde', 'hasta')))
            ->assertOk()
            ->assertJsonStructure([
                'kpisRango'             => ['total', 'toneladas', 'dias_rango', 'dias_op'],
                'evolucionRango'        => ['datos', 'promedio'],
                'desgloseVehiculoRango',
                'desgloseZonaRango',
            ]);
    }

    #[Test]
    public function data_con_fechas_invalidas_no_incluye_claves_de_rango(): void
    {
        $this->actingAs($this->admin())
            ->getJson(route('admin.dashboard.data', ['desde' => 'not-a-date', 'hasta' => 'also-not']))
            ->assertOk()
            ->assertJsonMissingPath('kpisRango');
    }

    #[Test]
    public function data_con_rango_invertido_no_incluye_claves_de_rango(): void
    {
        // hasta < desde → se ignora
        $this->actingAs($this->admin())
            ->getJson(route('admin.dashboard.data', [
                'desde' => today()->format('Y-m-d'),
                'hasta' => today()->subDays(7)->format('Y-m-d'),
            ]))
            ->assertOk()
            ->assertJsonMissingPath('kpisRango');
    }

    #[Test]
    public function data_kpis_reflejan_pesajes_creados_hoy(): void
    {
        Pesaje::factory()->count(3)->create(['created_at' => today(), 'peso_neto_kg' => 2000]);
        Pesaje::factory()->cancelado()->create(['created_at' => today(), 'peso_neto_kg' => 9000]);

        $json = $this->actingAs($this->admin())
            ->getJson(route('admin.dashboard.data'))
            ->assertOk()
            ->json();

        $this->assertEquals(3, $json['kpisDia']['total']);
        $this->assertEquals(6.0, $json['kpisDia']['toneladas']);
    }

    #[Test]
    public function operador_no_puede_acceder_al_data_endpoint(): void
    {
        $this->actingAs($this->operador())
            ->getJson(route('admin.dashboard.data'))
            ->assertForbidden();
    }
}
