<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Organizacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrganizacionCrudTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'nombre'      => 'Municipalidad de Corrientes',
            'admin_email' => 'admin@corrientes.gob.ar',
            'admin_name'  => 'Ana Gómez',
        ], $overrides);
    }

    // ── Acceso ────────────────────────────────────────────────────────

    #[Test]
    public function super_admin_accede_al_index(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('super.organizaciones.index'))
            ->assertOk();
    }

    #[Test]
    public function admin_no_accede_al_index(): void
    {
        $this->actingAs($this->admin())
            ->get(route('super.organizaciones.index'))
            ->assertForbidden();
    }

    #[Test]
    public function operador_no_accede_al_index(): void
    {
        $this->actingAs($this->operador())
            ->get(route('super.organizaciones.index'))
            ->assertForbidden();
    }

    #[Test]
    public function guest_es_redirigido_al_login(): void
    {
        $this->get(route('super.organizaciones.index'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function admin_no_puede_crear(): void
    {
        $this->actingAs($this->admin())
            ->post(route('super.organizaciones.store'), $this->payload())
            ->assertForbidden();
    }

    #[Test]
    public function operador_no_puede_crear(): void
    {
        $this->actingAs($this->operador())
            ->post(route('super.organizaciones.store'), $this->payload())
            ->assertForbidden();
    }

    // ── Index ─────────────────────────────────────────────────────────

    #[Test]
    public function index_muestra_organizaciones_existentes(): void
    {
        Organizacion::factory()->create(['nombre' => 'Municipalidad X']);
        Organizacion::factory()->create(['nombre' => 'Municipalidad Y']);

        $this->actingAs($this->superAdmin())
            ->get(route('super.organizaciones.index'))
            ->assertOk()
            ->assertSee('Municipalidad X')
            ->assertSee('Municipalidad Y');
    }

    // ── Store ─────────────────────────────────────────────────────────

    #[Test]
    public function store_crea_organizacion_y_admin_y_redirige(): void
    {
        Notification::fake();

        $this->actingAs($this->superAdmin())
            ->post(route('super.organizaciones.store'), $this->payload())
            ->assertRedirect(route('super.organizaciones.index'));

        $this->assertDatabaseHas('organizaciones', ['nombre' => 'Municipalidad de Corrientes']);

        // El email del admin debe haberse creado como usuario de la org.
        $admin = User::where('email', 'admin@corrientes.gob.ar')->first();
        $this->assertNotNull($admin);
        $this->assertSame('admin@corrientes.gob.ar', $admin->email);

        $org = Organizacion::where('nombre', 'Municipalidad de Corrientes')->first();
        $this->assertTrue($org->users()->whereKey($admin->id)->exists());
    }

    #[Test]
    public function store_validates_nombre_required(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('super.organizaciones.store'), $this->payload(['nombre' => '']))
            ->assertSessionHasErrors('nombre');
    }

    #[Test]
    public function store_validates_nombre_max_150(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('super.organizaciones.store'), $this->payload(['nombre' => str_repeat('a', 151)]))
            ->assertSessionHasErrors('nombre');
    }

    #[Test]
    public function store_validates_admin_email_required_and_valid(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('super.organizaciones.store'), $this->payload(['admin_email' => '']))
            ->assertSessionHasErrors('admin_email');

        $this->actingAs($this->superAdmin())
            ->post(route('super.organizaciones.store'), $this->payload(['admin_email' => 'no-es-email']))
            ->assertSessionHasErrors('admin_email');
    }

    // ── Update ────────────────────────────────────────────────────────

    #[Test]
    public function update_modifica_el_nombre_y_redirige(): void
    {
        $org = Organizacion::factory()->create(['nombre' => 'Nombre Viejo']);

        $this->actingAs($this->superAdmin())
            ->put(route('super.organizaciones.update', $org), ['nombre' => 'Nombre Nuevo'])
            ->assertRedirect(route('super.organizaciones.index'));

        $this->assertDatabaseHas('organizaciones', ['id' => $org->id, 'nombre' => 'Nombre Nuevo']);
        $this->assertDatabaseMissing('organizaciones', ['nombre' => 'Nombre Viejo']);
    }

    #[Test]
    public function update_validates_nombre_required(): void
    {
        $org = Organizacion::factory()->create();

        $this->actingAs($this->superAdmin())
            ->put(route('super.organizaciones.update', $org), ['nombre' => ''])
            ->assertSessionHasErrors('nombre');
    }

    // ── Toggle ────────────────────────────────────────────────────────

    #[Test]
    public function toggle_desactiva_una_organizacion_activa(): void
    {
        $org = Organizacion::factory()->create(['activo' => true]);

        $this->actingAs($this->superAdmin())
            ->patch(route('super.organizaciones.toggle', $org))
            ->assertRedirect(route('super.organizaciones.index'));

        $this->assertDatabaseHas('organizaciones', ['id' => $org->id, 'activo' => false]);
    }

    #[Test]
    public function toggle_activa_una_organizacion_inactiva(): void
    {
        $org = Organizacion::factory()->create(['activo' => false]);

        $this->actingAs($this->superAdmin())
            ->patch(route('super.organizaciones.toggle', $org))
            ->assertRedirect(route('super.organizaciones.index'));

        $this->assertDatabaseHas('organizaciones', ['id' => $org->id, 'activo' => true]);
    }

    // ── Destroy ───────────────────────────────────────────────────────

    #[Test]
    public function destroy_elimina_la_organizacion(): void
    {
        $org = Organizacion::factory()->create();

        $this->actingAs($this->superAdmin())
            ->delete(route('super.organizaciones.destroy', $org))
            ->assertRedirect(route('super.organizaciones.index'));

        $this->assertDatabaseMissing('organizaciones', ['id' => $org->id]);
    }
}
