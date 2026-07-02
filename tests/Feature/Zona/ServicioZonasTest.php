<?php

namespace Tests\Feature\Zona;

use App\Models\Organizacion;
use App\Models\TipoServicio;
use App\Models\TipoVehiculo;
use App\Models\Zona;
use App\Models\ZonaTurno;
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
        // El TestCase base ya crea y bindea la organización de prueba. Reutilizarla
        // mantiene el global scope alineado y evita crear orgs duplicadas.
        $this->org = app('organizacion');
    }

    private function servicio(array $attrs = []): TipoServicio
    {
        return TipoServicio::factory()->create(array_merge(['organizacion_id' => $this->org->id], $attrs));
    }

    /** Crea una zona del servicio dado, con sus turnos. */
    private function zona(TipoServicio $servicio, string $nombre, array $turnos = [], bool $activo = true): Zona
    {
        $zona = Zona::create([
            'organizacion_id'  => $this->org->id,
            'tipo_servicio_id' => $servicio->id,
            'nombre'           => $nombre,
            'activo'           => $activo,
        ]);

        foreach ($turnos as $turno) {
            ZonaTurno::create(['zona_id' => $zona->id, 'turno' => $turno]);
        }

        return $zona;
    }

    // — Turnos ——————————————————————————————————————————————————

    #[Test]
    public function retorna_turnos_de_la_zona(): void
    {
        $servicio = $this->servicio();
        $this->zona($servicio, 'Zona Norte', ['Diurna', 'Nocturna']);

        $this->actingAs($this->operador())
            ->getJson(route('servicios.zonas', $servicio))
            ->assertOk()
            ->assertJsonPath('zonas.0.turnos', ['Diurna', 'Nocturna']);
    }

    #[Test]
    public function retorna_arreglo_vacio_cuando_zona_no_tiene_turnos(): void
    {
        $servicio = $this->servicio();
        $this->zona($servicio, 'Zona Industrial', []);

        $this->actingAs($this->operador())
            ->getJson(route('servicios.zonas', $servicio))
            ->assertOk()
            ->assertJsonPath('zonas.0.turnos', []);
    }

    #[Test]
    public function no_mezcla_turnos_de_otro_servicio(): void
    {
        $servicio1 = $this->servicio();
        $servicio2 = $this->servicio();

        // Cada servicio tiene su propia zona (modelo 1:N).
        $this->zona($servicio1, 'Zona Norte', ['Diurna']);
        $this->zona($servicio2, 'Zona Norte', ['Nocturna']);

        $this->actingAs($this->operador())
            ->getJson(route('servicios.zonas', $servicio1))
            ->assertOk()
            ->assertJsonPath('zonas.0.turnos', ['Diurna']);
    }

    #[Test]
    public function multiples_zonas_reciben_sus_turnos_correctamente(): void
    {
        $servicio = $this->servicio();
        $this->zona($servicio, 'Norte', ['Diurna', 'Nocturna']);
        $this->zona($servicio, 'Sur', ['Diurna']);
        $this->zona($servicio, 'Industrial', []);

        $response = $this->actingAs($this->operador())
            ->getJson(route('servicios.zonas', $servicio))
            ->assertOk()
            ->assertJsonCount(3, 'zonas');

        $zonas = collect($response->json('zonas'))->keyBy('nombre');
        $this->assertEquals(['Diurna', 'Nocturna'], $zonas['Norte']['turnos']);
        $this->assertEquals(['Diurna'], $zonas['Sur']['turnos']);
        $this->assertEquals([], $zonas['Industrial']['turnos']);
    }

    // — Filtros de zona ————————————————————————————————————————

    #[Test]
    public function excluye_zonas_inactivas(): void
    {
        $servicio = $this->servicio();
        $this->zona($servicio, 'Activa', ['Diurna'], true);
        $this->zona($servicio, 'Inactiva', ['Nocturna'], false);

        $this->actingAs($this->operador())
            ->getJson(route('servicios.zonas', $servicio))
            ->assertOk()
            ->assertJsonCount(1, 'zonas')
            ->assertJsonPath('zonas.0.nombre', 'Activa');
    }

    #[Test]
    public function retorna_zonas_vacias_cuando_el_servicio_no_tiene_zonas(): void
    {
        // Un servicio sin zonas es elegible en la balanza pero no tiene orígenes:
        // el endpoint debe responder 200 con la lista vacía (no un error).
        $servicio = $this->servicio();

        $this->actingAs($this->operador())
            ->getJson(route('servicios.zonas', $servicio))
            ->assertOk()
            ->assertJsonCount(0, 'zonas');
    }

    #[Test]
    public function no_retorna_zonas_de_otro_servicio(): void
    {
        $servicio1 = $this->servicio();
        $servicio2 = $this->servicio();

        $this->zona($servicio1, 'Del servicio 1');
        $this->zona($servicio2, 'Del servicio 2');

        $this->actingAs($this->operador())
            ->getJson(route('servicios.zonas', $servicio1))
            ->assertOk()
            ->assertJsonCount(1, 'zonas')
            ->assertJsonPath('zonas.0.nombre', 'Del servicio 1');
    }

    // — Estructura de respuesta ————————————————————————————————

    #[Test]
    public function retorna_estructura_correcta(): void
    {
        $servicio = $this->servicio();
        $this->zona($servicio, 'Zona A', ['Diurna']);

        $this->actingAs($this->operador())
            ->getJson(route('servicios.zonas', $servicio))
            ->assertOk()
            ->assertJsonStructure([
                'tipos_vehiculo_sugeridos',
                'zonas' => [['id', 'nombre', 'turnos']],
            ]);
    }

    #[Test]
    public function retorna_tipos_vehiculo_sugeridos(): void
    {
        $tv = TipoVehiculo::factory()->create(['nombre' => 'Compactador', 'organizacion_id' => $this->org->id]);
        $servicio = $this->servicio();
        $servicio->tiposVehiculo()->attach($tv->id);

        $this->actingAs($this->operador())
            ->getJson(route('servicios.zonas', $servicio))
            ->assertOk()
            ->assertJsonPath('tipos_vehiculo_sugeridos', ['Compactador']);
    }

    #[Test]
    public function tipos_vehiculo_sugeridos_vacio_cuando_no_tiene(): void
    {
        $servicio = $this->servicio();

        $this->actingAs($this->operador())
            ->getJson(route('servicios.zonas', $servicio))
            ->assertOk()
            ->assertJsonPath('tipos_vehiculo_sugeridos', []);
    }

    // — Acceso ——————————————————————————————————————————————————

    #[Test]
    public function guest_no_puede_acceder(): void
    {
        $servicio = $this->servicio();

        $this->getJson(route('servicios.zonas', $servicio))
            ->assertUnauthorized();
    }
}
