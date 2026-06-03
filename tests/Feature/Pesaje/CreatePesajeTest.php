<?php

namespace Tests\Feature\Pesaje;

use App\Models\Pesaje;
use App\Models\TipoServicio;
use App\Models\Vehiculo;
use App\Models\Zona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreatePesajeTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'vehiculo_id'      => Vehiculo::factory()->create()->id,
            'tipo_servicio_id' => TipoServicio::factory()->create()->id,
            'zona_id'          => Zona::factory()->create()->id,
            'turno'            => 'Diurna',
            'peso_bruto_kg'    => 20000,
            'observaciones'    => null,
        ], $overrides);
    }

    // ── Acceso ────────────────────────────────────────────────────────

    #[Test]
    public function operador_can_create_pesaje_and_is_redirected_to_show(): void
    {
        $response = $this->actingAs($this->operador())
            ->post(route('pesajes.store'), $this->payload());

        $this->assertDatabaseCount('pesajes', 1);

        // SQL Server (uniqueidentifier) devuelve el uuid en mayúsculas al releerlo,
        // pero el redirect usa el uuid en minúsculas del modelo recién creado. El
        // binding por uuid es case-insensitive, así que comparamos sin distinguir caso.
        $pesaje = Pesaje::firstOrFail();
        $this->assertSame(
            strtolower(route('pesajes.show', $pesaje)),
            strtolower($response->headers->get('Location')),
        );
    }

    #[Test]
    public function admin_cannot_create_pesaje(): void
    {
        // La ruta de creación está bajo role:operador; el admin no la alcanza.
        $this->actingAs($this->admin())
            ->post(route('pesajes.store'), $this->payload())
            ->assertForbidden();
    }

    #[Test]
    public function guest_is_redirected_to_login(): void
    {
        $this->post(route('pesajes.store'), $this->payload())
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function store_persists_operador_id_of_authenticated_user(): void
    {
        $operador = $this->operador();

        $this->actingAs($operador)->post(route('pesajes.store'), $this->payload());

        $this->assertDatabaseHas('pesajes', ['operador_id' => $operador->id]);
    }

    // ── Validación ────────────────────────────────────────────────────

    #[Test]
    public function store_validates_vehiculo_id_required_and_existing(): void
    {
        $this->actingAs($this->operador())
            ->post(route('pesajes.store'), $this->payload(['vehiculo_id' => null]))
            ->assertSessionHasErrors('vehiculo_id');

        $this->actingAs($this->operador())
            ->post(route('pesajes.store'), $this->payload(['vehiculo_id' => 999999]))
            ->assertSessionHasErrors('vehiculo_id');
    }

    #[Test]
    public function store_validates_tipo_servicio_id_required(): void
    {
        $this->actingAs($this->operador())
            ->post(route('pesajes.store'), $this->payload(['tipo_servicio_id' => null]))
            ->assertSessionHasErrors('tipo_servicio_id');
    }

    #[Test]
    public function store_validates_zona_id_required(): void
    {
        $this->actingAs($this->operador())
            ->post(route('pesajes.store'), $this->payload(['zona_id' => null]))
            ->assertSessionHasErrors('zona_id');
    }

    #[Test]
    public function store_validates_peso_bruto_required_integer_min_1(): void
    {
        $this->actingAs($this->operador())
            ->post(route('pesajes.store'), $this->payload(['peso_bruto_kg' => 0]))
            ->assertSessionHasErrors('peso_bruto_kg');

        $this->actingAs($this->operador())
            ->post(route('pesajes.store'), $this->payload(['peso_bruto_kg' => null]))
            ->assertSessionHasErrors('peso_bruto_kg');
    }

    #[Test]
    public function store_validates_turno_only_diurna_or_nocturna(): void
    {
        $this->actingAs($this->operador())
            ->post(route('pesajes.store'), $this->payload(['turno' => 'Mañana']))
            ->assertSessionHasErrors('turno');
    }

    #[Test]
    public function store_validates_observaciones_max_500(): void
    {
        $this->actingAs($this->operador())
            ->post(route('pesajes.store'), $this->payload(['observaciones' => str_repeat('a', 501)]))
            ->assertSessionHasErrors('observaciones');
    }
}
