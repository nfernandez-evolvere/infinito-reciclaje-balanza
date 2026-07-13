<?php

namespace Tests\Feature\Pesaje;

use App\Models\Alerta;
use App\Models\Pesaje;
use App\Models\TipoServicio;
use App\Models\TipoVehiculo;
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
        // Vehiculo con tara controlada para afirmar el neto calculado.
        $tipo = TipoVehiculo::factory()->create(['peso_min_kg' => 5000, 'peso_max_kg' => 30000]);
        $vehiculo = Vehiculo::factory()->create(['tara_kg' => 8000, 'tipo_vehiculo_id' => $tipo->id]);

        $response = $this->actingAs($this->operador())
            ->post(route('pesajes.store'), $this->payload([
                'vehiculo_id'   => $vehiculo->id,
                'peso_bruto_kg' => 20000,
            ]));

        $pesaje = Pesaje::firstOrFail();

        // Datos calculados por el servicio persistidos correctamente vía HTTP.
        $this->assertSame('En predio', $pesaje->estado);
        $this->assertFalse($pesaje->editado);
        $this->assertSame(8000, $pesaje->peso_tara_kg);
        $this->assertSame(12000, $pesaje->peso_neto_kg);
        $this->assertFalse($pesaje->alerta_peso);

        // SQL Server (uniqueidentifier) devuelve el uuid en mayúsculas al releerlo,
        // pero el redirect usa el uuid en minúsculas del modelo recién creado.
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
        // Tipo de vehículo con tope amplio: el payload por defecto usa
        // peso_bruto_kg = 20.000 y no debe chocar con el tope duro aleatorio
        // que generaría TipoVehiculo::factory() sin overrides.
        $tipo = TipoVehiculo::factory()->create(['peso_min_kg' => 5000, 'peso_max_kg' => 30000]);
        $vehiculo = Vehiculo::factory()->create(['tara_kg' => 8000, 'tipo_vehiculo_id' => $tipo->id]);
        $operador = $this->operador();

        $this->actingAs($operador)->post(route('pesajes.store'), $this->payload([
            'vehiculo_id' => $vehiculo->id,
        ]));

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
    public function store_accepts_any_turno_text_written_for_the_zona(): void
    {
        // Turno es texto libre (sin catálogo): cualquier string corto es válido.
        $this->actingAs($this->operador())
            ->post(route('pesajes.store'), $this->payload(['turno' => 'Refuerzo']))
            ->assertSessionDoesntHaveErrors('turno');
    }

    #[Test]
    public function store_validates_turno_max_20_chars(): void
    {
        $this->actingAs($this->operador())
            ->post(route('pesajes.store'), $this->payload(['turno' => str_repeat('a', 21)]))
            ->assertSessionHasErrors('turno');
    }

    #[Test]
    public function store_validates_observaciones_max_500(): void
    {
        $this->actingAs($this->operador())
            ->post(route('pesajes.store'), $this->payload(['observaciones' => str_repeat('a', 501)]))
            ->assertSessionHasErrors('observaciones');
    }

    #[Test]
    public function store_rechaza_bruto_menor_a_la_tara_del_vehiculo(): void
    {
        // Regla custom de StorePesajeRequest::withValidator — borde exacto: la
        // condición es `bruto < tara`, así que tara - 1 debe fallar.
        $tipo = TipoVehiculo::factory()->create(['peso_min_kg' => 1000, 'peso_max_kg' => 30000]);
        $vehiculo = Vehiculo::factory()->create(['tara_kg' => 8000, 'tipo_vehiculo_id' => $tipo->id]);

        $this->actingAs($this->operador())
            ->post(route('pesajes.store'), $this->payload([
                'vehiculo_id'   => $vehiculo->id,
                'peso_bruto_kg' => 7999,
            ]))
            ->assertSessionHasErrors('peso_bruto_kg');

        $this->assertDatabaseCount('pesajes', 0);
    }

    #[Test]
    public function store_acepta_bruto_igual_a_la_tara(): void
    {
        // Borde exacto del lado válido: bruto == tara pasa la regla y produce neto 0.
        $tipo = TipoVehiculo::factory()->create(['peso_min_kg' => 1000, 'peso_max_kg' => 30000]);
        $vehiculo = Vehiculo::factory()->create(['tara_kg' => 8000, 'tipo_vehiculo_id' => $tipo->id]);

        $this->actingAs($this->operador())
            ->post(route('pesajes.store'), $this->payload([
                'vehiculo_id'   => $vehiculo->id,
                'peso_bruto_kg' => 8000,
            ]))
            ->assertSessionHasNoErrors();

        $pesaje = Pesaje::firstOrFail();
        $this->assertSame(8000, $pesaje->peso_bruto_kg);
        $this->assertSame(0, $pesaje->peso_neto_kg);
    }

    #[Test]
    public function store_rechaza_bruto_por_encima_del_tope_duro(): void
    {
        // Tope duro = peso_max × FACTOR_TOPE_PESO (2) = 20.000 kg. Borde exacto:
        // la condición es `bruto > tope`, así que tope + 1 debe fallar y no persistir.
        $tipo = TipoVehiculo::factory()->create(['peso_min_kg' => 5000, 'peso_max_kg' => 10000]);
        $vehiculo = Vehiculo::factory()->create(['tara_kg' => 4000, 'tipo_vehiculo_id' => $tipo->id]);

        $this->actingAs($this->operador())
            ->post(route('pesajes.store'), $this->payload([
                'vehiculo_id'   => $vehiculo->id,
                'peso_bruto_kg' => 20001,
            ]))
            ->assertSessionHasErrors('peso_bruto_kg');

        $this->assertDatabaseCount('pesajes', 0);
    }

    #[Test]
    public function store_acepta_bruto_igual_al_tope_duro_con_alerta(): void
    {
        // Borde exacto del lado válido: bruto == tope (20.000) pasa la regla, pero
        // como sigue por encima del máximo habitual (10.000) se guarda con alerta.
        $admin = $this->admin();
        app('organizacion')->users()->syncWithoutDetaching([$admin->id]);

        $tipo = TipoVehiculo::factory()->create(['peso_min_kg' => 5000, 'peso_max_kg' => 10000]);
        $vehiculo = Vehiculo::factory()->create(['tara_kg' => 4000, 'tipo_vehiculo_id' => $tipo->id]);

        $this->actingAs($this->operador())
            ->post(route('pesajes.store'), $this->payload([
                'vehiculo_id'   => $vehiculo->id,
                'peso_bruto_kg' => 20000,
            ]))
            ->assertSessionHasNoErrors();

        $pesaje = Pesaje::firstOrFail();
        $this->assertSame(20000, $pesaje->peso_bruto_kg);
        $this->assertTrue($pesaje->alerta_peso);
    }

    // ── Integración con Alertas ───────────────────────────────────────

    #[Test]
    public function crear_pesaje_fuera_de_rango_persists_alerta_record(): void
    {
        // Admin adjunto a la org para que AlertaService::getAdminIds() lo encuentre
        $admin = $this->admin();
        app('organizacion')->users()->syncWithoutDetaching([$admin->id]);

        $tipo = TipoVehiculo::factory()->create(['peso_min_kg' => 5000, 'peso_max_kg' => 10000]);
        $vehiculo = Vehiculo::factory()->create(['tara_kg' => 4000, 'tipo_vehiculo_id' => $tipo->id]);

        // Peso bruto fuera del rango (> 10.000 kg) pero dentro del tope duro
        // (≤ 20.000 kg = peso_max × FACTOR_TOPE_PESO): genera alerta, no bloquea.
        $this->actingAs($this->operador())
            ->post(route('pesajes.store'), $this->payload([
                'vehiculo_id'   => $vehiculo->id,
                'peso_bruto_kg' => 15000,
            ]));

        $pesaje = Pesaje::firstOrFail();
        $this->assertTrue($pesaje->alerta_peso);

        // Debe existir exactamente un registro en alertas para ese pesaje
        $alerta = Alerta::withoutGlobalScopes()
            ->where('pesaje_id', $pesaje->id)
            ->firstOrFail();

        $this->assertSame('peso_fuera_rango', $alerta->tipo);
        $this->assertSame($admin->id, $alerta->user_id);
        $this->assertNotNull($alerta->uuid);
        $this->assertFalse($alerta->leida);
    }

    #[Test]
    public function crear_pesaje_con_vehiculo_no_habitual_persists_alerta_record(): void
    {
        $admin = $this->admin();
        app('organizacion')->users()->syncWithoutDetaching([$admin->id]);

        $tipoHabitual = TipoVehiculo::factory()->create();
        $tipoNoHabitual = TipoVehiculo::factory()->create();
        $vehiculo = Vehiculo::factory()->create(['tara_kg' => 2000, 'tipo_vehiculo_id' => $tipoNoHabitual->id]);

        $servicio = TipoServicio::factory()->create();
        $servicio->tiposVehiculo()->attach($tipoHabitual->id); // vehículo NO es habitual

        $this->actingAs($this->operador())
            ->post(route('pesajes.store'), [
                'vehiculo_id'      => $vehiculo->id,
                'tipo_servicio_id' => $servicio->id,
                'zona_id'          => Zona::factory()->create()->id,
                'turno'            => null,
                'peso_bruto_kg'    => 5000,
                'observaciones'    => null,
            ]);

        $pesaje = Pesaje::firstOrFail();

        $alerta = Alerta::withoutGlobalScopes()
            ->where('tipo', 'vehiculo_no_habitual')
            ->where('pesaje_id', $pesaje->id)
            ->firstOrFail();

        $this->assertSame($admin->id, $alerta->user_id);
        $this->assertStringContainsString($vehiculo->patente, $alerta->titulo);
        $this->assertFalse($alerta->leida);
    }

    #[Test]
    public function crear_pesaje_con_vehiculo_habitual_does_not_create_alerta_vehiculo(): void
    {
        $admin = $this->admin();
        app('organizacion')->users()->syncWithoutDetaching([$admin->id]);

        $tipoHabitual = TipoVehiculo::factory()->create(['peso_min_kg' => 1000, 'peso_max_kg' => 30000]);
        $vehiculo = Vehiculo::factory()->create(['tara_kg' => 2000, 'tipo_vehiculo_id' => $tipoHabitual->id]);

        $servicio = TipoServicio::factory()->create();
        $servicio->tiposVehiculo()->attach($tipoHabitual->id); // vehículo SÍ es habitual

        $this->actingAs($this->operador())
            ->post(route('pesajes.store'), [
                'vehiculo_id'      => $vehiculo->id,
                'tipo_servicio_id' => $servicio->id,
                'zona_id'          => Zona::factory()->create()->id,
                'turno'            => null,
                'peso_bruto_kg'    => 5000,
                'observaciones'    => null,
            ]);

        $this->assertSame(0, Alerta::withoutGlobalScopes()
            ->where('tipo', 'vehiculo_no_habitual')
            ->count());
    }

    #[Test]
    public function crear_pesaje_dentro_de_rango_does_not_create_alerta(): void
    {
        $admin = $this->admin();
        app('organizacion')->users()->syncWithoutDetaching([$admin->id]);

        $tipo = TipoVehiculo::factory()->create(['peso_min_kg' => 5000, 'peso_max_kg' => 30000]);
        $vehiculo = Vehiculo::factory()->create(['tara_kg' => 4000, 'tipo_vehiculo_id' => $tipo->id]);

        $this->actingAs($this->operador())
            ->post(route('pesajes.store'), $this->payload([
                'vehiculo_id'   => $vehiculo->id,
                'peso_bruto_kg' => 20000, // dentro del rango 5.000–30.000
            ]));

        $pesaje = Pesaje::firstOrFail();
        $this->assertFalse($pesaje->alerta_peso);
        $this->assertSame(0, Alerta::withoutGlobalScopes()->where('pesaje_id', $pesaje->id)->count());
    }
}
