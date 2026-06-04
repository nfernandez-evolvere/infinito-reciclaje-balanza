<?php

namespace Tests\Feature\Alerta;

use App\Models\Alerta;
use App\Models\ConfigAlerta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AlertaModuleTest extends TestCase
{
    use RefreshDatabase;

    // ── Acceso ────────────────────────────────────────────────────────

    #[Test]
    public function index_is_accessible_by_admin(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.alertas.index'))
            ->assertOk();
    }

    #[Test]
    public function index_is_forbidden_for_operador(): void
    {
        $this->actingAs($this->operador())
            ->get(route('admin.alertas.index'))
            ->assertForbidden();
    }

    // ── Marcar leída ──────────────────────────────────────────────────

    #[Test]
    public function marcar_leida_marks_alerta_as_read_and_sets_leida_at(): void
    {
        $admin = $this->admin();
        $alerta = Alerta::create([
            'user_id'         => $admin->id,
            'tipo'            => 'peso_fuera_rango',
            'titulo'          => 'Alerta de prueba',
            'fecha_deteccion' => today()->toDateString(),
            'leida'           => false,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.alertas.leer', $alerta->uuid))
            ->assertRedirect();

        $alerta->refresh();
        $this->assertTrue($alerta->leida);
        $this->assertNotNull($alerta->leida_at);
    }

    #[Test]
    public function marcar_leida_only_updates_the_targeted_alerta(): void
    {
        $admin = $this->admin();

        $alertaA = Alerta::create([
            'user_id' => $admin->id, 'tipo' => 'peso_fuera_rango',
            'titulo' => 'A', 'fecha_deteccion' => today()->toDateString(), 'leida' => false,
        ]);
        $alertaB = Alerta::create([
            'user_id' => $admin->id, 'tipo' => 'gap_registro',
            'titulo' => 'B', 'fecha_deteccion' => today()->toDateString(), 'leida' => false,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.alertas.leer', $alertaA->uuid));

        $this->assertTrue($alertaA->refresh()->leida);
        $this->assertFalse($alertaB->refresh()->leida);
    }

    // ── Marcar todas ──────────────────────────────────────────────────

    #[Test]
    public function marcar_todas_leidas_marks_only_current_user_unread_alertas(): void
    {
        $adminA = $this->admin();
        $adminB = $this->admin();

        foreach (range(1, 3) as $i) {
            Alerta::create([
                'user_id' => $adminA->id, 'tipo' => 'gap_registro',
                'titulo' => "A{$i}", 'fecha_deteccion' => today()->toDateString(), 'leida' => false,
            ]);
        }
        $alertaB = Alerta::create([
            'user_id' => $adminB->id, 'tipo' => 'gap_registro',
            'titulo' => 'B1', 'fecha_deteccion' => today()->toDateString(), 'leida' => false,
        ]);

        $this->actingAs($adminA)
            ->post(route('admin.alertas.leer-todas'))
            ->assertRedirect();

        // Las 3 alertas de adminA quedan leídas.
        $this->assertSame(0, Alerta::withoutGlobalScopes()->where('user_id', $adminA->id)->where('leida', false)->count());
        // La alerta de adminB no se toca.
        $this->assertFalse($alertaB->refresh()->leida);
    }

    #[Test]
    public function marcar_todas_leidas_returns_correct_count_in_toast(): void
    {
        $admin = $this->admin();

        foreach (range(1, 4) as $i) {
            Alerta::create([
                'user_id' => $admin->id, 'tipo' => 'volumen_diario_atipico',
                'titulo' => "V{$i}", 'fecha_deteccion' => today()->toDateString(), 'leida' => false,
            ]);
        }

        $response = $this->actingAs($admin)
            ->post(route('admin.alertas.leer-todas'));

        // El toast de sesión refleja las 4 marcadas.
        $response->assertSessionHas('toast', fn ($t) => str_contains($t['message'], '4'));
    }

    // ── Configuración ─────────────────────────────────────────────────

    #[Test]
    public function update_config_saves_activo_and_umbral_per_tipo(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put(route('admin.alertas.configuracion.update'), [
                'config' => [
                    'peso_fuera_rango'       => ['activo' => '1', 'umbral_valor' => ''],
                    'volumen_diario_atipico' => ['activo' => '0', 'umbral_valor' => '35'],
                    'gap_registro'           => ['activo' => '1', 'umbral_valor' => '90'],
                    'frecuencia_zona_atipica'=> ['activo' => '0', 'umbral_valor' => '25'],
                ],
            ])
            ->assertRedirect();

        $volumen = ConfigAlerta::withoutGlobalScopes()->where('tipo', 'volumen_diario_atipico')->first();
        $this->assertNotNull($volumen);
        $this->assertFalse($volumen->activo);
        $this->assertSame(35.0, $volumen->umbral_valor);

        $gap = ConfigAlerta::withoutGlobalScopes()->where('tipo', 'gap_registro')->first();
        $this->assertTrue($gap->activo);
        $this->assertSame(90.0, $gap->umbral_valor);
    }

    #[Test]
    public function update_config_is_forbidden_for_operador(): void
    {
        $this->actingAs($this->operador())
            ->put(route('admin.alertas.configuracion.update'), ['config' => []])
            ->assertForbidden();
    }

    // ── Novedades JSON ────────────────────────────────────────────────

    #[Test]
    public function novedades_returns_count_and_unread_items_for_current_user(): void
    {
        $admin = $this->admin();

        Alerta::create([
            'user_id' => $admin->id, 'tipo' => 'peso_fuera_rango',
            'titulo' => 'Sin leer 1', 'fecha_deteccion' => today()->toDateString(), 'leida' => false,
        ]);
        Alerta::create([
            'user_id' => $admin->id, 'tipo' => 'gap_registro',
            'titulo' => 'Sin leer 2', 'fecha_deteccion' => today()->toDateString(), 'leida' => false,
        ]);
        Alerta::create([
            'user_id' => $admin->id, 'tipo' => 'gap_registro',
            'titulo' => 'Ya leída', 'fecha_deteccion' => today()->toDateString(), 'leida' => true,
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.alertas.novedades'));

        $response->assertOk()
            ->assertJsonPath('count', 2)
            ->assertJsonCount(2, 'items');
    }

    #[Test]
    public function novedades_does_not_expose_other_user_alertas(): void
    {
        $adminA = $this->admin();
        $adminB = $this->admin();

        Alerta::create([
            'user_id' => $adminB->id, 'tipo' => 'peso_fuera_rango',
            'titulo' => 'Alerta de B', 'fecha_deteccion' => today()->toDateString(), 'leida' => false,
        ]);

        $this->actingAs($adminA)
            ->getJson(route('admin.alertas.novedades'))
            ->assertJsonPath('count', 0)
            ->assertJsonCount(0, 'items');
    }

    // ── Route model binding con UUID ─────────────────────────────────

    #[Test]
    public function marcar_leida_returns_404_for_valid_uuid_that_does_not_exist(): void
    {
        // SQL Server requiere UUID con formato válido para no lanzar error de conversión.
        // Usamos un UUID bien formado que no existe en la tabla.
        $uuidInexistente = '00000000-0000-0000-0000-000000000001';

        $this->actingAs($this->admin())
            ->patch(route('admin.alertas.leer', $uuidInexistente))
            ->assertNotFound();
    }

    #[Test]
    public function admin_from_other_org_cannot_mark_alerta_as_read(): void
    {
        $orgA = $this->createOrganizacion('Org A');
        $orgB = $this->createOrganizacion('Org B');

        $adminA = $this->actingInOrg($orgA, fn () => $this->admin());
        $adminB = $this->actingInOrg($orgB, fn () => $this->admin());

        // Alerta perteneciente a la Org A
        $alerta = $this->actingInOrg($orgA, fn () => Alerta::create([
            'user_id'         => $adminA->id,
            'tipo'            => 'peso_fuera_rango',
            'titulo'          => 'Solo de Org A',
            'fecha_deteccion' => today()->toDateString(),
        ]));

        // Admin de Org B intenta marcarla → el route model binding scoped a Org B devuelve 404
        app()->instance('organizacion', $orgB);

        $this->actingAs($adminB)
            ->patch(route('admin.alertas.leer', $alerta->uuid))
            ->assertNotFound();

        // La alerta sigue sin leer
        $this->assertFalse($alerta->refresh()->leida);
    }

    // ── Aislamiento de organización ───────────────────────────────────

    #[Test]
    public function admin_only_sees_alertas_of_own_organization(): void
    {
        $orgA = $this->createOrganizacion('Org A');
        $orgB = $this->createOrganizacion('Org B');

        $adminA = $this->actingInOrg($orgA, fn () => $this->admin());
        $adminB = $this->actingInOrg($orgB, fn () => $this->admin());

        $this->actingInOrg($orgA, fn () => Alerta::create([
            'user_id' => $adminA->id, 'tipo' => 'peso_fuera_rango',
            'titulo' => 'Alerta de Org A', 'fecha_deteccion' => today()->toDateString(),
        ]));
        $this->actingInOrg($orgB, fn () => Alerta::create([
            'user_id' => $adminB->id, 'tipo' => 'peso_fuera_rango',
            'titulo' => 'Alerta de Org B', 'fecha_deteccion' => today()->toDateString(),
        ]));

        app()->instance('organizacion', $orgA);

        $this->actingAs($adminA)
            ->get(route('admin.alertas.index'))
            ->assertOk()
            ->assertSee('Alerta de Org A')
            ->assertDontSee('Alerta de Org B');
    }
}
