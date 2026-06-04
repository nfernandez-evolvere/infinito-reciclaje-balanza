<?php

namespace Tests\Integration;

use App\Models\Pesaje;
use App\Models\TipoServicio;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use App\Models\Zona;
use App\Repositories\AlertaRepository;
use App\Repositories\PesajeLogRepository;
use App\Repositories\PesajeRepository;
use App\Services\AlertaService;
use App\Services\PesajeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PesajeServiceTest extends TestCase
{
    use RefreshDatabase;

    private PesajeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PesajeService(
            new PesajeRepository,
            new PesajeLogRepository,
            new AlertaService(new AlertaRepository),
        );
    }

    /** Vehículo con tara y rango de peso controlados para los cálculos. */
    private function vehiculo(int $tara = 8000, int $min = 5000, int $max = 30000): Vehiculo
    {
        $tipo = TipoVehiculo::factory()->create(['peso_min_kg' => $min, 'peso_max_kg' => $max]);

        return Vehiculo::factory()->create(['tara_kg' => $tara, 'tipo_vehiculo_id' => $tipo->id]);
    }

    private function payload(Vehiculo $vehiculo, array $overrides = []): array
    {
        return array_merge([
            'vehiculo_id'      => $vehiculo->id,
            'tipo_servicio_id' => TipoServicio::factory()->create()->id,
            'zona_id'          => Zona::factory()->create()->id,
            'turno'            => 'Mañana',
            'peso_bruto_kg'    => 20000,
            'observaciones'    => null,
        ], $overrides);
    }

    // ── crear ─────────────────────────────────────────────────────────

    #[Test]
    public function crear_calcula_neto_restando_la_tara_del_vehiculo(): void
    {
        $vehiculo = $this->vehiculo(tara: 8000);

        $pesaje = $this->service->crear(
            $this->payload($vehiculo, ['peso_bruto_kg' => 20000]),
            $this->operador()
        );

        $this->assertSame(8000, $pesaje->peso_tara_kg);
        $this->assertSame(12000, $pesaje->peso_neto_kg);
    }

    #[Test]
    public function crear_clampea_el_neto_a_cero_cuando_la_tara_supera_al_bruto(): void
    {
        $vehiculo = $this->vehiculo(tara: 8000);

        $pesaje = $this->service->crear(
            $this->payload($vehiculo, ['peso_bruto_kg' => 5000]),
            $this->operador()
        );

        $this->assertSame(0, $pesaje->peso_neto_kg);
    }

    #[Test]
    public function crear_marca_alerta_cuando_el_bruto_cae_por_debajo_del_rango(): void
    {
        $vehiculo = $this->vehiculo(tara: 3000, min: 8000, max: 30000);

        $pesaje = $this->service->crear(
            $this->payload($vehiculo, ['peso_bruto_kg' => 6000]),
            $this->operador()
        );

        $this->assertTrue($pesaje->alerta_peso);
    }

    #[Test]
    public function crear_marca_alerta_cuando_el_bruto_supera_el_rango(): void
    {
        $vehiculo = $this->vehiculo(tara: 3000, min: 8000, max: 30000);

        $pesaje = $this->service->crear(
            $this->payload($vehiculo, ['peso_bruto_kg' => 35000]),
            $this->operador()
        );

        $this->assertTrue($pesaje->alerta_peso);
    }

    #[Test]
    public function crear_no_marca_alerta_dentro_del_rango(): void
    {
        $vehiculo = $this->vehiculo(tara: 3000, min: 8000, max: 30000);

        $pesaje = $this->service->crear(
            $this->payload($vehiculo, ['peso_bruto_kg' => 15000]),
            $this->operador()
        );

        $this->assertFalse($pesaje->alerta_peso);
    }

    #[Test]
    public function crear_no_marca_alerta_en_el_limite_exacto_del_minimo(): void
    {
        // La condición es < min || > max, así que exactamente en min NO debe alertar.
        $vehiculo = $this->vehiculo(tara: 3000, min: 8000, max: 30000);

        $pesaje = $this->service->crear(
            $this->payload($vehiculo, ['peso_bruto_kg' => 8000]),
            $this->operador()
        );

        $this->assertFalse($pesaje->alerta_peso);
    }

    #[Test]
    public function crear_no_marca_alerta_en_el_limite_exacto_del_maximo(): void
    {
        $vehiculo = $this->vehiculo(tara: 3000, min: 8000, max: 30000);

        $pesaje = $this->service->crear(
            $this->payload($vehiculo, ['peso_bruto_kg' => 30000]),
            $this->operador()
        );

        $this->assertFalse($pesaje->alerta_peso);
    }

    #[Test]
    public function crear_inicializa_estado_en_predio_y_no_editado(): void
    {
        $vehiculo = $this->vehiculo();

        $pesaje = $this->service->crear($this->payload($vehiculo), $this->operador());

        $this->assertSame('En predio', $pesaje->estado);
        $this->assertFalse($pesaje->editado);
    }

    #[Test]
    public function crear_persiste_el_operador_autenticado(): void
    {
        $vehiculo = $this->vehiculo();
        $operador = $this->operador();

        $pesaje = $this->service->crear($this->payload($vehiculo), $operador);

        $this->assertDatabaseHas('pesajes', [
            'id'          => $pesaje->id,
            'operador_id' => $operador->id,
            'vehiculo_id' => $vehiculo->id,
        ]);
    }

    // ── marcarEgreso ──────────────────────────────────────────────────

    #[Test]
    public function marcarEgreso_cierra_el_pesaje_y_setea_hora_salida(): void
    {
        $pesaje = Pesaje::factory()->enPredio()->create(['hora_salida' => null]);

        $actualizado = $this->service->marcarEgreso($pesaje, []);

        $this->assertSame('Cerrado', $actualizado->estado);
        $this->assertNotNull($actualizado->hora_salida);
    }

    #[Test]
    public function marcarEgreso_guarda_el_bruto_de_salida_cuando_viene(): void
    {
        $pesaje = Pesaje::factory()->enPredio()->create();

        $actualizado = $this->service->marcarEgreso($pesaje, ['bruto_salida_kg' => 9000]);

        $this->assertSame(9000, $actualizado->bruto_salida_kg);
    }

    #[Test]
    public function marcarEgreso_deja_el_bruto_de_salida_nulo_cuando_no_viene(): void
    {
        $pesaje = Pesaje::factory()->enPredio()->create();

        $actualizado = $this->service->marcarEgreso($pesaje, []);

        $this->assertNull($actualizado->bruto_salida_kg);
    }

    #[Test]
    public function marcarEgreso_lanza_si_el_pesaje_ya_esta_cerrado(): void
    {
        $pesaje = Pesaje::factory()->create(['estado' => 'Cerrado']);

        try {
            $this->service->marcarEgreso($pesaje, []);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('estado', $e->errors());
        }
    }

    // ── editar ────────────────────────────────────────────────────────

    #[Test]
    public function editar_exige_motivo_no_vacio(): void
    {
        $pesaje = Pesaje::factory()->create();

        try {
            $this->service->editar($pesaje, ['motivo' => '   ', 'zona_id' => Zona::factory()->create()->id], $this->operador());
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('motivo', $e->errors());
        }
    }

    #[Test]
    public function editar_registra_en_log_solo_los_campos_que_cambian(): void
    {
        $pesaje = Pesaje::factory()->create();
        $nuevaZona = Zona::factory()->create();
        $operador = $this->operador();

        // Capturar el id anterior ANTES de editar (el service puede mutar el modelo en memoria).
        $zonaIdAnterior = (string) $pesaje->zona_id;

        $this->service->editar(
            $pesaje,
            ['motivo' => 'Zona mal cargada', 'zona_id' => $nuevaZona->id],
            $operador
        );

        $this->assertDatabaseCount('pesajes_log', 1);
        $this->assertDatabaseHas('pesajes_log', [
            'pesaje_id'      => $pesaje->id,
            'campo'          => 'zona_id',
            'valor_anterior' => $zonaIdAnterior,
            'valor_nuevo'    => (string) $nuevaZona->id,
            'motivo'         => 'Zona mal cargada',
            'usuario_id'     => $operador->id,
        ]);
    }

    #[Test]
    public function editar_recalcula_el_neto_al_cambiar_el_bruto_y_marca_editado(): void
    {
        $pesaje = Pesaje::factory()->create([
            'peso_bruto_kg' => 20000,
            'peso_tara_kg'  => 8000,
            'peso_neto_kg'  => 12000,
            'editado'       => false,
        ]);

        $actualizado = $this->service->editar(
            $pesaje,
            ['motivo' => 'Corrección de bruto', 'peso_bruto_kg' => 25000],
            $this->operador()
        );

        $this->assertSame(25000, $actualizado->peso_bruto_kg);
        $this->assertSame(17000, $actualizado->peso_neto_kg);
        $this->assertTrue($actualizado->editado);
        $this->assertDatabaseHas('pesajes_log', ['pesaje_id' => $pesaje->id, 'campo' => 'peso_bruto_kg']);
    }

    #[Test]
    public function editar_no_escribe_log_ni_marca_editado_si_nada_cambia(): void
    {
        $pesaje = Pesaje::factory()->create(['editado' => false]);

        $this->service->editar($pesaje, ['motivo' => 'Sin cambios reales'], $this->operador());

        $this->assertDatabaseCount('pesajes_log', 0);
        $this->assertFalse($pesaje->fresh()->editado);
    }

    #[Test]
    public function editar_registra_una_entrada_por_cada_campo_cambiado(): void
    {
        $pesaje = Pesaje::factory()->create(['turno' => 'Mañana']);
        $nuevaZona = Zona::factory()->create();

        $this->service->editar(
            $pesaje,
            [
                'motivo'        => 'Varios ajustes',
                'peso_bruto_kg' => $pesaje->peso_bruto_kg + 1000,
                'zona_id'       => $nuevaZona->id,
                'turno'         => 'Tarde',
            ],
            $this->operador()
        );

        $this->assertDatabaseCount('pesajes_log', 3);
    }

    // ── cancelar ──────────────────────────────────────────────────────

    #[Test]
    public function cancelar_marca_cancelado_con_metadatos_y_deja_log(): void
    {
        $pesaje = Pesaje::factory()->create(['estado' => 'En predio']);
        $usuario = $this->operador();

        $actualizado = $this->service->cancelar($pesaje, ['motivo' => 'Carga duplicada'], $usuario);

        $this->assertSame('Cancelado', $actualizado->estado);
        $this->assertSame($usuario->id, $actualizado->cancelado_por_id);
        $this->assertNotNull($actualizado->cancelado_at);
        $this->assertSame('Carga duplicada', $actualizado->motivo_cancelacion);

        $this->assertDatabaseHas('pesajes_log', [
            'pesaje_id'   => $pesaje->id,
            'campo'       => 'estado',
            'valor_nuevo' => 'Cancelado',
        ]);
    }

    #[Test]
    public function cancelar_lanza_si_ya_estaba_cancelado(): void
    {
        $pesaje = Pesaje::factory()->cancelado()->create();

        try {
            $this->service->cancelar($pesaje, ['motivo' => 'Otra vez'], $this->operador());
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('estado', $e->errors());
        }
    }
}
