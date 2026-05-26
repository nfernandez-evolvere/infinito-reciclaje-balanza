<?php

namespace Tests\Feature;

use App\Models\Organizacion;
use App\Models\TipoServicio;
use App\Models\User;
use App\Models\Zona;
use App\Models\ZonaServicio;
use App\Models\ZonaServicioTurno;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ServicioZonasTest extends TestCase
{
    use RefreshDatabase;

    private Organizacion $org;

    protected function setUp(): void
    {
        parent::setUp();
        $this->org = Organizacion::create(['nombre' => 'Test', 'slug' => 'test', 'activo' => true]);
    }

    private function usuario(): User
    {
        return User::factory()->create();
    }

    private function servicio(array $attrs = []): TipoServicio
    {
        return TipoServicio::factory()->create(array_merge(['organizacion_id' => $this->org->id], $attrs));
    }

    private function zona(string $nombre, bool $activo = true): Zona
    {
        return Zona::create([
            'organizacion_id' => $this->org->id,
            'nombre'          => $nombre,
            'activo'          => $activo,
        ]);
    }

    private function vincular(Zona $zona, TipoServicio $servicio, array $turnos = []): void
    {
        ZonaServicio::create([
            'zona_id'          => $zona->id,
            'tipo_servicio_id' => $servicio->id,
        ]);
        foreach ($turnos as $turno) {
            ZonaServicioTurno::create([
                'zona_id'          => $zona->id,
                'tipo_servicio_id' => $servicio->id,
                'turno'            => $turno,
            ]);
        }
    }

    // — Turnos ——————————————————————————————————————————————————

    #[Test]
    public function retorna_turnos_de_la_zona(): void
    {
        $servicio = $this->servicio();
        $zona     = $this->zona('Zona Norte');
        $this->vincular($zona, $servicio, ['Diurna', 'Nocturna']);

        $this->actingAs($this->usuario())
            ->getJson(route('api.servicios.zonas', $servicio))
            ->assertOk()
            ->assertJsonPath('zonas.0.turnos', ['Diurna', 'Nocturna']);
    }

    #[Test]
    public function retorna_arreglo_vacio_cuando_zona_no_tiene_turnos(): void
    {
        $servicio = $this->servicio();
        $zona     = $this->zona('Zona Industrial');
        $this->vincular($zona, $servicio, []);

        $this->actingAs($this->usuario())
            ->getJson(route('api.servicios.zonas', $servicio))
            ->assertOk()
            ->assertJsonPath('zonas.0.turnos', []);
    }

    #[Test]
    public function no_mezcla_turnos_de_otro_servicio(): void
    {
        $servicio1 = $this->servicio();
        $servicio2 = $this->servicio();
        $zona      = $this->zona('Zona Norte');

        $this->vincular($zona, $servicio1, ['Diurna']);
        $this->vincular($zona, $servicio2, ['Nocturna']);

        $this->actingAs($this->usuario())
            ->getJson(route('api.servicios.zonas', $servicio1))
            ->assertOk()
            ->assertJsonPath('zonas.0.turnos', ['Diurna']);
    }

    #[Test]
    public function multiples_zonas_reciben_sus_turnos_correctamente(): void
    {
        $servicio = $this->servicio();
        $norte    = $this->zona('Norte');
        $sur      = $this->zona('Sur');
        $industrial = $this->zona('Industrial');

        $this->vincular($norte,      $servicio, ['Diurna', 'Nocturna']);
        $this->vincular($sur,        $servicio, ['Diurna']);
        $this->vincular($industrial, $servicio, []);

        $response = $this->actingAs($this->usuario())
            ->getJson(route('api.servicios.zonas', $servicio))
            ->assertOk()
            ->assertJsonCount(3, 'zonas');

        $zonas = collect($response->json('zonas'))->keyBy('nombre');
        $this->assertEquals(['Diurna', 'Nocturna'], $zonas['Norte']['turnos']);
        $this->assertEquals(['Diurna'],             $zonas['Sur']['turnos']);
        $this->assertEquals([],                     $zonas['Industrial']['turnos']);
    }

    // — Filtros de zona ————————————————————————————————————————

    #[Test]
    public function excluye_zonas_inactivas(): void
    {
        $servicio     = $this->servicio();
        $zonaActiva   = $this->zona('Activa',   true);
        $zonaInactiva = $this->zona('Inactiva', false);

        $this->vincular($zonaActiva,   $servicio, ['Diurna']);
        $this->vincular($zonaInactiva, $servicio, ['Nocturna']);

        $this->actingAs($this->usuario())
            ->getJson(route('api.servicios.zonas', $servicio))
            ->assertOk()
            ->assertJsonCount(1, 'zonas')
            ->assertJsonPath('zonas.0.nombre', 'Activa');
    }

    #[Test]
    public function no_retorna_zonas_de_otro_servicio(): void
    {
        $servicio1 = $this->servicio();
        $servicio2 = $this->servicio();

        $this->vincular($this->zona('Del servicio 1'), $servicio1);
        $this->vincular($this->zona('Del servicio 2'), $servicio2);

        $this->actingAs($this->usuario())
            ->getJson(route('api.servicios.zonas', $servicio1))
            ->assertOk()
            ->assertJsonCount(1, 'zonas')
            ->assertJsonPath('zonas.0.nombre', 'Del servicio 1');
    }

    // — Estructura de respuesta ————————————————————————————————

    #[Test]
    public function retorna_estructura_correcta(): void
    {
        $servicio = $this->servicio();
        $this->vincular($this->zona('Zona A'), $servicio, ['Diurna']);

        $this->actingAs($this->usuario())
            ->getJson(route('api.servicios.zonas', $servicio))
            ->assertOk()
            ->assertJsonStructure([
                'tipo_vehiculo_sugerido',
                'zonas' => [['id', 'nombre', 'turnos']],
            ]);
    }

    #[Test]
    public function retorna_tipo_vehiculo_sugerido(): void
    {
        $tv       = \App\Models\TipoVehiculo::factory()->create(['nombre' => 'Compactador', 'organizacion_id' => $this->org->id]);
        $servicio = $this->servicio(['tipo_vehiculo_sugerido_id' => $tv->id]);

        $this->actingAs($this->usuario())
            ->getJson(route('api.servicios.zonas', $servicio))
            ->assertOk()
            ->assertJsonPath('tipo_vehiculo_sugerido', 'Compactador');
    }

    #[Test]
    public function tipo_vehiculo_sugerido_es_null_cuando_no_tiene(): void
    {
        $servicio = $this->servicio(['tipo_vehiculo_sugerido_id' => null]);

        $this->actingAs($this->usuario())
            ->getJson(route('api.servicios.zonas', $servicio))
            ->assertOk()
            ->assertJsonPath('tipo_vehiculo_sugerido', null);
    }

    // — Acceso ——————————————————————————————————————————————————

    #[Test]
    public function guest_no_puede_acceder(): void
    {
        $servicio = $this->servicio();

        $this->getJson(route('api.servicios.zonas', $servicio))
            ->assertUnauthorized();
    }
}
