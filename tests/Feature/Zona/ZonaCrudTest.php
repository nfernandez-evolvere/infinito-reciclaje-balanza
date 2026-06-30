<?php

namespace Tests\Feature\Zona;

use App\Models\TipoServicio;
use App\Models\Zona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ZonaCrudTest extends TestCase
{
    use RefreshDatabase;

    private const INDEX = 'admin.tipos-servicio.index';

    private TipoServicio $servicio;

    protected function setUp(): void
    {
        parent::setUp();
        // Cada zona pertenece a un servicio. Las zonas se gestionan dentro de él.
        $this->servicio = TipoServicio::factory()->create();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'tipo_servicio_id' => $this->servicio->id,
            'nombre'           => 'Zona Norte',
            'hectareas'        => 250.5,
            'barrios'          => 8,
            'habitantes'       => 15000,
        ], $overrides);
    }

    // ── Acceso ────────────────────────────────────────────────────────

    #[Test]
    public function operador_no_puede_crear_zona(): void
    {
        $this->actingAs($this->operador())
            ->post(route('admin.zonas.store'), $this->payload())
            ->assertForbidden();
    }

    #[Test]
    public function guest_no_puede_crear_zona(): void
    {
        $this->post(route('admin.zonas.store'), $this->payload())
            ->assertRedirect(route('login'));
    }

    // ── Store ─────────────────────────────────────────────────────────

    #[Test]
    public function store_crea_zona_con_todos_los_campos_y_redirige(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.zonas.store'), $this->payload([
                'nombre'     => 'Zona Centro',
                'hectareas'  => 100.0,
                'barrios'    => 5,
                'habitantes' => 8000,
            ]))
            ->assertRedirect(route(self::INDEX));

        $this->assertDatabaseHas('zonas', [
            'tipo_servicio_id' => $this->servicio->id,
            'nombre'           => 'Zona Centro',
            'hectareas'        => 100.0,
            'barrios'          => 5,
            'habitantes'       => 8000,
            'activo'           => true,
        ]);
    }

    #[Test]
    public function store_acepta_campos_opcionales_nulos(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.zonas.store'), [
                'tipo_servicio_id' => $this->servicio->id,
                'nombre'           => 'Zona Mínima',
                'hectareas'        => null,
                'barrios'          => null,
                'habitantes'       => null,
            ])
            ->assertRedirect(route(self::INDEX))
            ->assertSessionHasNoErrors();
    }

    #[Test]
    public function store_validates_tipo_servicio_required_and_exists(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.zonas.store'), $this->payload(['tipo_servicio_id' => null]))
            ->assertSessionHasErrors('tipo_servicio_id');

        $this->actingAs($this->admin())
            ->post(route('admin.zonas.store'), $this->payload(['tipo_servicio_id' => 999999]))
            ->assertSessionHasErrors('tipo_servicio_id');
    }

    #[Test]
    public function store_validates_nombre_required(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.zonas.store'), $this->payload(['nombre' => '']))
            ->assertSessionHasErrors('nombre');
    }

    #[Test]
    public function store_validates_nombre_unique_dentro_del_servicio(): void
    {
        Zona::factory()->create(['tipo_servicio_id' => $this->servicio->id, 'nombre' => 'Zona Norte']);

        $this->actingAs($this->admin())
            ->post(route('admin.zonas.store'), $this->payload(['nombre' => 'Zona Norte']))
            ->assertSessionHasErrors('nombre');
    }

    #[Test]
    public function store_permite_mismo_nombre_en_otro_servicio(): void
    {
        $otroServicio = TipoServicio::factory()->create();
        Zona::factory()->create(['tipo_servicio_id' => $otroServicio->id, 'nombre' => 'Zona Norte']);

        $this->actingAs($this->admin())
            ->post(route('admin.zonas.store'), $this->payload(['nombre' => 'Zona Norte']))
            ->assertRedirect(route(self::INDEX))
            ->assertSessionHasNoErrors();

        $this->assertSame(2, Zona::withoutGlobalScopes()->where('nombre', 'Zona Norte')->count());
    }

    #[Test]
    public function store_validates_hectareas_numeric_min_0(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.zonas.store'), $this->payload(['hectareas' => -1]))
            ->assertSessionHasErrors('hectareas');
    }

    #[Test]
    public function store_validates_habitantes_integer_min_0(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.zonas.store'), $this->payload(['habitantes' => -1]))
            ->assertSessionHasErrors('habitantes');
    }

    // ── Turnos y horarios ─────────────────────────────────────────────

    #[Test]
    public function store_persiste_los_turnos(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.zonas.store'), $this->payload([
                'nombre' => 'Zona Con Turnos',
                'turnos' => ['Diurna', 'Nocturna'],
            ]))
            ->assertRedirect(route(self::INDEX))
            ->assertSessionHasNoErrors();

        $zona = Zona::where('nombre', 'Zona Con Turnos')->firstOrFail();
        $this->assertDatabaseHas('zona_turnos', ['zona_id' => $zona->id, 'turno' => 'Diurna']);
        $this->assertDatabaseHas('zona_turnos', ['zona_id' => $zona->id, 'turno' => 'Nocturna']);
    }

    #[Test]
    public function update_reemplaza_los_turnos(): void
    {
        $zona = Zona::factory()->create(['tipo_servicio_id' => $this->servicio->id, 'nombre' => 'Zona Turnos']);
        $zona->turnos()->create(['turno' => 'Diurna']);

        $this->actingAs($this->admin())
            ->put(route('admin.zonas.update', $zona), $this->payload([
                'nombre' => 'Zona Turnos',
                'turnos' => ['Nocturna'],
            ]))
            ->assertRedirect(route(self::INDEX))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('zona_turnos', ['zona_id' => $zona->id, 'turno' => 'Nocturna']);
        $this->assertDatabaseMissing('zona_turnos', ['zona_id' => $zona->id, 'turno' => 'Diurna']);
    }

    #[Test]
    public function store_persiste_los_horarios(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.zonas.store'), $this->payload([
                'nombre'   => 'Zona Horarios',
                'horarios' => [
                    0 => [['inicio' => '08:00', 'fin' => '12:00']], // Lunes
                ],
            ]))
            ->assertRedirect(route(self::INDEX))
            ->assertSessionHasNoErrors();

        $zona = Zona::where('nombre', 'Zona Horarios')->firstOrFail();
        $horario = $zona->horarios()->first();

        $this->assertNotNull($horario);
        $this->assertSame(1, (int) $horario->dia_semana);
        $this->assertSame(1, (int) $horario->franja);
        // El formato de TIME varía por motor (SQLite: "08:00", SQL Server: "08:00:00").
        $this->assertSame('08:00', substr((string) $horario->hora_inicio, 0, 5));
        $this->assertSame('12:00', substr((string) $horario->hora_fin, 0, 5));
    }

    // ── Update ────────────────────────────────────────────────────────

    #[Test]
    public function update_modifica_nombre_y_datos_y_redirige(): void
    {
        $zona = Zona::factory()->create([
            'tipo_servicio_id' => $this->servicio->id,
            'nombre'           => 'Nombre Viejo',
            'hectareas'        => 100.0,
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.zonas.update', $zona), $this->payload([
                'nombre'    => 'Nombre Nuevo',
                'hectareas' => 200.0,
            ]))
            ->assertRedirect(route(self::INDEX));

        $this->assertDatabaseHas('zonas', [
            'id'        => $zona->id,
            'nombre'    => 'Nombre Nuevo',
            'hectareas' => 200.0,
        ]);
        $this->assertDatabaseMissing('zonas', ['nombre' => 'Nombre Viejo']);
    }

    #[Test]
    public function update_permite_el_mismo_nombre_en_el_mismo_registro(): void
    {
        $zona = Zona::factory()->create(['tipo_servicio_id' => $this->servicio->id, 'nombre' => 'Zona Única']);

        $this->actingAs($this->admin())
            ->put(route('admin.zonas.update', $zona), $this->payload([
                'nombre'    => 'Zona Única',
                'hectareas' => 300.0,
            ]))
            ->assertRedirect(route(self::INDEX))
            ->assertSessionHasNoErrors();
    }

    #[Test]
    public function update_rechaza_nombre_de_otra_zona_del_mismo_servicio(): void
    {
        Zona::factory()->create(['tipo_servicio_id' => $this->servicio->id, 'nombre' => 'Zona A']);
        $zona = Zona::factory()->create(['tipo_servicio_id' => $this->servicio->id, 'nombre' => 'Zona B']);

        $this->actingAs($this->admin())
            ->put(route('admin.zonas.update', $zona), $this->payload(['nombre' => 'Zona A']))
            ->assertSessionHasErrors('nombre');
    }

    // ── Geometría ─────────────────────────────────────────────────────

    private function validGeojson(): string
    {
        return json_encode([
            'type'     => 'FeatureCollection',
            'features' => [[
                'type'       => 'Feature',
                'properties' => (object) [],
                'geometry'   => [
                    'type'        => 'Polygon',
                    'coordinates' => [[
                        [-58.84, -27.47],
                        [-58.82, -27.47],
                        [-58.82, -27.45],
                        [-58.84, -27.45],
                        [-58.84, -27.47],
                    ]],
                ],
            ]],
        ]);
    }

    #[Test]
    public function store_persiste_geojson_y_centro(): void
    {
        $geojson = $this->validGeojson();

        $this->actingAs($this->admin())
            ->post(route('admin.zonas.store'), $this->payload([
                'nombre'     => 'Zona Mapeada',
                'geojson'    => $geojson,
                'centro_lat' => '-27.46',
                'centro_lng' => '-58.83',
            ]))
            ->assertRedirect(route(self::INDEX))
            ->assertSessionHasNoErrors();

        $zona = Zona::where('nombre', 'Zona Mapeada')->firstOrFail();

        $this->assertSame($geojson, $zona->geojson);
        $this->assertEquals(-27.46, $zona->centro_lat);
        $this->assertEquals(-58.83, $zona->centro_lng);
    }

    #[Test]
    public function store_acepta_zona_sin_geometria(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.zonas.store'), $this->payload([
                'nombre'     => 'Zona Sin Mapa',
                'geojson'    => '',
                'centro_lat' => '',
                'centro_lng' => '',
            ]))
            ->assertRedirect(route(self::INDEX))
            ->assertSessionHasNoErrors();

        $zona = Zona::where('nombre', 'Zona Sin Mapa')->firstOrFail();

        $this->assertNull($zona->geojson);
        $this->assertNull($zona->centro_lat);
        $this->assertNull($zona->centro_lng);
    }

    #[Test]
    public function store_validates_geojson_es_json(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.zonas.store'), $this->payload(['geojson' => 'no-es-json']))
            ->assertSessionHasErrors('geojson');
    }

    #[Test]
    public function store_validates_centro_lat_dentro_de_rango(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.zonas.store'), $this->payload(['centro_lat' => 91]))
            ->assertSessionHasErrors('centro_lat');
    }

    #[Test]
    public function store_validates_centro_lng_dentro_de_rango(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.zonas.store'), $this->payload(['centro_lng' => -181]))
            ->assertSessionHasErrors('centro_lng');
    }

    #[Test]
    public function update_persiste_la_geometria(): void
    {
        $zona = Zona::factory()->create(['tipo_servicio_id' => $this->servicio->id, 'nombre' => 'Zona Geo', 'geojson' => null]);
        $geojson = $this->validGeojson();

        $this->actingAs($this->admin())
            ->put(route('admin.zonas.update', $zona), $this->payload([
                'nombre'     => 'Zona Geo',
                'geojson'    => $geojson,
                'centro_lat' => '-27.46',
                'centro_lng' => '-58.83',
            ]))
            ->assertRedirect(route(self::INDEX))
            ->assertSessionHasNoErrors();

        $zona->refresh();

        $this->assertSame($geojson, $zona->geojson);
        $this->assertEquals(-27.46, $zona->centro_lat);
        $this->assertEquals(-58.83, $zona->centro_lng);
    }

    #[Test]
    public function update_puede_borrar_la_geometria(): void
    {
        $zona = Zona::factory()->conGeometria()->create(['tipo_servicio_id' => $this->servicio->id, 'nombre' => 'Zona Con Geo']);

        $this->actingAs($this->admin())
            ->put(route('admin.zonas.update', $zona), $this->payload([
                'nombre'     => 'Zona Con Geo',
                'geojson'    => '',
                'centro_lat' => '',
                'centro_lng' => '',
            ]))
            ->assertRedirect(route(self::INDEX))
            ->assertSessionHasNoErrors();

        $zona->refresh();

        $this->assertNull($zona->geojson);
        $this->assertNull($zona->centro_lat);
        $this->assertNull($zona->centro_lng);
    }

    // ── Toggle ────────────────────────────────────────────────────────

    #[Test]
    public function toggle_desactiva_una_zona_activa(): void
    {
        $zona = Zona::factory()->create(['tipo_servicio_id' => $this->servicio->id, 'activo' => true]);

        $this->actingAs($this->admin())
            ->patch(route('admin.zonas.toggle', $zona))
            ->assertRedirect(route(self::INDEX));

        $this->assertDatabaseHas('zonas', ['id' => $zona->id, 'activo' => false]);
    }

    #[Test]
    public function toggle_activa_una_zona_inactiva(): void
    {
        $zona = Zona::factory()->inactiva()->create(['tipo_servicio_id' => $this->servicio->id]);

        $this->actingAs($this->admin())
            ->patch(route('admin.zonas.toggle', $zona))
            ->assertRedirect(route(self::INDEX));

        $this->assertDatabaseHas('zonas', ['id' => $zona->id, 'activo' => true]);
    }

    // ── Destroy ───────────────────────────────────────────────────────

    #[Test]
    public function destroy_elimina_zona_sin_pesajes(): void
    {
        $zona = Zona::factory()->create(['tipo_servicio_id' => $this->servicio->id]);

        $this->actingAs($this->admin())
            ->delete(route('admin.zonas.destroy', $zona))
            ->assertRedirect(route(self::INDEX));

        $this->assertDatabaseMissing('zonas', ['id' => $zona->id]);
    }
}
