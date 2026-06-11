<?php

namespace Tests\Feature\Zona;

use App\Models\Zona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ZonaCrudTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'nombre'     => 'Zona Norte',
            'hectareas'  => 250.5,
            'barrios'    => 8,
            'habitantes' => 15000,
        ], $overrides);
    }

    // ── Acceso ────────────────────────────────────────────────────────

    #[Test]
    public function solo_admin_accede_al_index(): void
    {
        $this->actingAs($this->operador())
            ->get(route('admin.zonas.index'))
            ->assertForbidden();

        $this->actingAs($this->admin())
            ->get(route('admin.zonas.index'))
            ->assertOk();
    }

    #[Test]
    public function guest_es_redirigido_al_login(): void
    {
        $this->get(route('admin.zonas.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function operador_no_puede_crear_zona(): void
    {
        $this->actingAs($this->operador())
            ->post(route('admin.zonas.store'), $this->payload())
            ->assertForbidden();
    }

    // ── Index ─────────────────────────────────────────────────────────

    #[Test]
    public function index_lista_zonas_existentes(): void
    {
        Zona::factory()->create(['nombre' => 'Zona Norte']);
        Zona::factory()->create(['nombre' => 'Zona Sur']);

        $this->actingAs($this->admin())
            ->get(route('admin.zonas.index'))
            ->assertOk()
            ->assertSee('Zona Norte')
            ->assertSee('Zona Sur');
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
            ->assertRedirect(route('admin.zonas.index'));

        $this->assertDatabaseHas('zonas', [
            'nombre'     => 'Zona Centro',
            'hectareas'  => 100.0,
            'barrios'    => 5,
            'habitantes' => 8000,
            'activo'     => true,
        ]);
    }

    #[Test]
    public function store_acepta_campos_opcionales_nulos(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.zonas.store'), [
                'nombre'     => 'Zona Mínima',
                'hectareas'  => null,
                'barrios'    => null,
                'habitantes' => null,
            ])
            ->assertRedirect(route('admin.zonas.index'))
            ->assertSessionHasNoErrors();
    }

    #[Test]
    public function store_validates_nombre_required(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.zonas.store'), $this->payload(['nombre' => '']))
            ->assertSessionHasErrors('nombre');
    }

    #[Test]
    public function store_validates_nombre_unique(): void
    {
        Zona::factory()->create(['nombre' => 'Zona Norte']);

        $this->actingAs($this->admin())
            ->post(route('admin.zonas.store'), $this->payload(['nombre' => 'Zona Norte']))
            ->assertSessionHasErrors('nombre');
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

    // ── Update ────────────────────────────────────────────────────────

    #[Test]
    public function update_modifica_nombre_y_datos_y_redirige(): void
    {
        $zona = Zona::factory()->create(['nombre' => 'Nombre Viejo', 'hectareas' => 100.0]);

        $this->actingAs($this->admin())
            ->put(route('admin.zonas.update', $zona), [
                'nombre'    => 'Nombre Nuevo',
                'hectareas' => 200.0,
            ])
            ->assertRedirect(route('admin.zonas.index'));

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
        $zona = Zona::factory()->create(['nombre' => 'Zona Única']);

        $this->actingAs($this->admin())
            ->put(route('admin.zonas.update', $zona), [
                'nombre'    => 'Zona Única',
                'hectareas' => 300.0,
            ])
            ->assertRedirect(route('admin.zonas.index'))
            ->assertSessionHasNoErrors();
    }

    #[Test]
    public function update_rechaza_nombre_de_otra_zona(): void
    {
        Zona::factory()->create(['nombre' => 'Zona A']);
        $zona = Zona::factory()->create(['nombre' => 'Zona B']);

        $this->actingAs($this->admin())
            ->put(route('admin.zonas.update', $zona), ['nombre' => 'Zona A'])
            ->assertSessionHasErrors('nombre');
    }

    // ── Aislamiento multi-tenant ──────────────────────────────────────
    // La unicidad de nombre es por organización (unique compuesto en la BD).
    // En la tabla compartida, el nombre de otra organización no debe interferir.

    #[Test]
    public function update_permite_editar_con_nombre_que_existe_en_otra_organizacion(): void
    {
        // Otra organización ya tiene una zona llamada "Zona Norte".
        $otraOrg = $this->createOrganizacion('Otra Organización');
        $this->actingInOrg($otraOrg, fn () => Zona::factory()->create(['nombre' => 'Zona Norte']));

        // La organización actual también tiene su "Zona Norte" (permitido: distinta org).
        $zona = Zona::factory()->create(['nombre' => 'Zona Norte', 'geojson' => null]);
        $geojson = $this->validGeojson();

        // Editar sólo el contorno, conservando el nombre, no debe chocar con la otra org.
        $this->actingAs($this->admin())
            ->put(route('admin.zonas.update', $zona), [
                'nombre'     => 'Zona Norte',
                'geojson'    => $geojson,
                'centro_lat' => '-27.46',
                'centro_lng' => '-58.83',
            ])
            ->assertRedirect(route('admin.zonas.index'))
            ->assertSessionHasNoErrors();

        $zona->refresh();
        $this->assertSame($geojson, $zona->geojson);
        $this->assertEquals(-27.46, $zona->centro_lat);
    }

    #[Test]
    public function store_permite_mismo_nombre_en_otra_organizacion(): void
    {
        $otraOrg = $this->createOrganizacion('Otra Organización');
        $this->actingInOrg($otraOrg, fn () => Zona::factory()->create(['nombre' => 'Zona Norte']));

        $this->actingAs($this->admin())
            ->post(route('admin.zonas.store'), $this->payload(['nombre' => 'Zona Norte']))
            ->assertRedirect(route('admin.zonas.index'))
            ->assertSessionHasNoErrors();

        // Una zona "Zona Norte" en cada organización: dos filas, distinta org.
        $this->assertSame(2, Zona::withoutGlobalScopes()->where('nombre', 'Zona Norte')->count());
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
            ->assertRedirect(route('admin.zonas.index'))
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
            ->assertRedirect(route('admin.zonas.index'))
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
        $zona = Zona::factory()->create(['nombre' => 'Zona Geo', 'geojson' => null]);
        $geojson = $this->validGeojson();

        $this->actingAs($this->admin())
            ->put(route('admin.zonas.update', $zona), [
                'nombre'     => 'Zona Geo',
                'geojson'    => $geojson,
                'centro_lat' => '-27.46',
                'centro_lng' => '-58.83',
            ])
            ->assertRedirect(route('admin.zonas.index'))
            ->assertSessionHasNoErrors();

        $zona->refresh();

        $this->assertSame($geojson, $zona->geojson);
        $this->assertEquals(-27.46, $zona->centro_lat);
        $this->assertEquals(-58.83, $zona->centro_lng);
    }

    #[Test]
    public function update_puede_borrar_la_geometria(): void
    {
        $zona = Zona::factory()->conGeometria()->create(['nombre' => 'Zona Con Geo']);

        $this->actingAs($this->admin())
            ->put(route('admin.zonas.update', $zona), [
                'nombre'     => 'Zona Con Geo',
                'geojson'    => '',
                'centro_lat' => '',
                'centro_lng' => '',
            ])
            ->assertRedirect(route('admin.zonas.index'))
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
        $zona = Zona::factory()->create(['activo' => true]);

        $this->actingAs($this->admin())
            ->patch(route('admin.zonas.toggle', $zona))
            ->assertRedirect(route('admin.zonas.index'));

        $this->assertDatabaseHas('zonas', ['id' => $zona->id, 'activo' => false]);
    }

    #[Test]
    public function toggle_activa_una_zona_inactiva(): void
    {
        $zona = Zona::factory()->inactiva()->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.zonas.toggle', $zona))
            ->assertRedirect(route('admin.zonas.index'));

        $this->assertDatabaseHas('zonas', ['id' => $zona->id, 'activo' => true]);
    }

    // ── Destroy ───────────────────────────────────────────────────────

    #[Test]
    public function destroy_elimina_zona_sin_pesajes(): void
    {
        $zona = Zona::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.zonas.destroy', $zona))
            ->assertRedirect(route('admin.zonas.index'));

        $this->assertDatabaseMissing('zonas', ['id' => $zona->id]);
    }
}
