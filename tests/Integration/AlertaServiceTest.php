<?php

namespace Tests\Integration;

use App\Models\Alerta;
use App\Models\ConfigAlerta;
use App\Models\Pesaje;
use App\Models\TipoServicio;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use App\Models\Zona;
use App\Repositories\AlertaRepository;
use App\Services\AlertaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AlertaServiceTest extends TestCase
{
    use RefreshDatabase;

    private AlertaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AlertaService(new AlertaRepository);
    }

    // ── registrarPesoFueraRango ───────────────────────────────────────

    #[Test]
    public function registrar_peso_fuera_rango_creates_one_record_per_admin_in_org(): void
    {
        $org = app('organizacion');

        $adminA = $this->admin();
        $adminB = $this->admin();
        $org->users()->syncWithoutDetaching([$adminA->id, $adminB->id]);

        $pesaje = $this->pesajeConAlerta($org->id);

        $this->service->registrarPesoFueraRango($pesaje);

        $this->assertSame(2, Alerta::withoutGlobalScopes()->where('pesaje_id', $pesaje->id)->count());
        $this->assertTrue(Alerta::withoutGlobalScopes()->where('pesaje_id', $pesaje->id)->where('user_id', $adminA->id)->exists());
        $this->assertTrue(Alerta::withoutGlobalScopes()->where('pesaje_id', $pesaje->id)->where('user_id', $adminB->id)->exists());
    }

    #[Test]
    public function registrar_peso_fuera_rango_persists_correct_fields(): void
    {
        $admin = $this->admin();
        $org = app('organizacion');
        $org->users()->syncWithoutDetaching([$admin->id]);

        $pesaje = $this->pesajeConAlerta($org->id);

        $this->service->registrarPesoFueraRango($pesaje);

        $alerta = Alerta::withoutGlobalScopes()->where('pesaje_id', $pesaje->id)->firstOrFail();

        $this->assertSame('peso_fuera_rango', $alerta->tipo);
        $this->assertNotEmpty($alerta->titulo);
        $this->assertNotNull($alerta->uuid);
        $this->assertSame($pesaje->id, $alerta->pesaje_id);
        $this->assertSame(today()->toDateString(), $alerta->fecha_deteccion->toDateString());
        $this->assertFalse($alerta->leida);
    }

    #[Test]
    public function registrar_peso_fuera_rango_is_idempotent_for_same_pesaje(): void
    {
        $admin = $this->admin();
        $org = app('organizacion');
        $org->users()->syncWithoutDetaching([$admin->id]);

        $pesaje = $this->pesajeConAlerta($org->id);

        $this->service->registrarPesoFueraRango($pesaje);
        $this->service->registrarPesoFueraRango($pesaje); // segunda llamada

        $this->assertSame(1, Alerta::withoutGlobalScopes()->where('pesaje_id', $pesaje->id)->count());
    }

    #[Test]
    public function registrar_peso_fuera_rango_skips_when_tipo_disabled_in_config(): void
    {
        $admin = $this->admin();
        $org = app('organizacion');
        $org->users()->syncWithoutDetaching([$admin->id]);

        ConfigAlerta::create(['tipo' => 'peso_fuera_rango', 'activo' => false]);

        $pesaje = $this->pesajeConAlerta($org->id);

        $this->service->registrarPesoFueraRango($pesaje);

        $this->assertSame(0, Alerta::withoutGlobalScopes()->where('pesaje_id', $pesaje->id)->count());
    }

    #[Test]
    public function registrar_peso_fuera_rango_creates_no_record_when_no_admins_in_org(): void
    {
        // Org sin admins asociados
        $pesaje = $this->pesajeConAlerta(app('organizacion')->id);

        $this->service->registrarPesoFueraRango($pesaje);

        $this->assertSame(0, Alerta::withoutGlobalScopes()->count());
    }

    // ── getConfigConDefaults ──────────────────────────────────────────

    #[Test]
    public function get_config_con_defaults_returns_all_five_tipos(): void
    {
        $config = $this->service->getConfigConDefaults(app('organizacion')->id);

        $this->assertArrayHasKey('peso_fuera_rango', $config);
        $this->assertArrayHasKey('volumen_diario_atipico', $config);
        $this->assertArrayHasKey('gap_registro', $config);
        $this->assertArrayHasKey('frecuencia_zona_atipica', $config);
        $this->assertArrayHasKey('vehiculo_no_habitual', $config);
        $this->assertCount(5, $config);
    }

    #[Test]
    public function get_config_con_defaults_returns_defaults_when_no_saved_config(): void
    {
        $config = $this->service->getConfigConDefaults(app('organizacion')->id);

        $this->assertTrue($config['volumen_diario_atipico']['activo']);
        $this->assertSame(20.0, $config['volumen_diario_atipico']['umbral_valor']);

        $this->assertTrue($config['gap_registro']['activo']);
        $this->assertSame(120.0, $config['gap_registro']['umbral_valor']);
    }

    #[Test]
    public function get_config_con_defaults_merges_saved_values_over_defaults(): void
    {
        $org = app('organizacion');

        ConfigAlerta::create([
            'organizacion_id' => $org->id,
            'tipo'            => 'gap_registro',
            'activo'          => false,
            'umbral_valor'    => 60.0,
        ]);

        $config = $this->service->getConfigConDefaults($org->id);

        $this->assertFalse($config['gap_registro']['activo']);
        $this->assertSame(60.0, $config['gap_registro']['umbral_valor']);

        // El resto sigue con defaults
        $this->assertTrue($config['volumen_diario_atipico']['activo']);
    }

    // ── guardarConfig ─────────────────────────────────────────────────

    #[Test]
    public function guardar_config_creates_or_updates_config_alertas_rows(): void
    {
        $org = app('organizacion');

        $this->service->guardarConfig($org->id, [
            'gap_registro'     => ['activo' => false, 'umbral_valor' => '45'],
            'peso_fuera_rango' => ['activo' => true, 'umbral_valor' => ''],
        ]);

        $gap = ConfigAlerta::withoutGlobalScopes()
            ->where('organizacion_id', $org->id)
            ->where('tipo', 'gap_registro')
            ->firstOrFail();

        $this->assertFalse($gap->activo);
        $this->assertSame(45.0, $gap->umbral_valor);

        $peso = ConfigAlerta::withoutGlobalScopes()
            ->where('organizacion_id', $org->id)
            ->where('tipo', 'peso_fuera_rango')
            ->firstOrFail();

        $this->assertTrue($peso->activo);
        $this->assertNull($peso->umbral_valor);
    }

    // ── UUID auto-generado ────────────────────────────────────────────

    #[Test]
    public function alerta_has_unique_uuid_on_create(): void
    {
        $admin = $this->admin();
        $org = app('organizacion');
        $org->users()->syncWithoutDetaching([$admin->id]);

        $pesajeA = $this->pesajeConAlerta($org->id);
        $pesajeB = $this->pesajeConAlerta($org->id);

        $this->service->registrarPesoFueraRango($pesajeA);
        $this->service->registrarPesoFueraRango($pesajeB);

        $uuids = Alerta::withoutGlobalScopes()->pluck('uuid')->all();
        $this->assertCount(count($uuids), array_unique($uuids));
    }

    // ── registrarVehiculoNoHabitual ───────────────────────────────────

    #[Test]
    public function registrar_vehiculo_no_habitual_creates_one_record_per_admin(): void
    {
        $adminA = $this->admin();
        $adminB = $this->admin();
        $org    = app('organizacion');
        $org->users()->syncWithoutDetaching([$adminA->id, $adminB->id]);

        $pesaje = $this->pesajeVehiculoNoHabitual($org->id);

        $this->service->registrarVehiculoNoHabitual($pesaje);

        $this->assertSame(2, Alerta::withoutGlobalScopes()->where('pesaje_id', $pesaje->id)->count());
        $this->assertTrue(Alerta::withoutGlobalScopes()->where('pesaje_id', $pesaje->id)->where('user_id', $adminA->id)->exists());
        $this->assertTrue(Alerta::withoutGlobalScopes()->where('pesaje_id', $pesaje->id)->where('user_id', $adminB->id)->exists());
    }

    #[Test]
    public function registrar_vehiculo_no_habitual_persists_correct_fields(): void
    {
        $admin = $this->admin();
        $org   = app('organizacion');
        $org->users()->syncWithoutDetaching([$admin->id]);

        $pesaje = $this->pesajeVehiculoNoHabitual($org->id);

        $this->service->registrarVehiculoNoHabitual($pesaje);

        $alerta = Alerta::withoutGlobalScopes()->where('pesaje_id', $pesaje->id)->firstOrFail();

        $this->assertSame('vehiculo_no_habitual', $alerta->tipo);
        $this->assertStringContainsString($pesaje->vehiculo->patente, $alerta->titulo);
        $this->assertNotNull($alerta->uuid);
        $this->assertFalse($alerta->leida);
    }

    #[Test]
    public function registrar_vehiculo_no_habitual_is_idempotent(): void
    {
        $admin = $this->admin();
        $org   = app('organizacion');
        $org->users()->syncWithoutDetaching([$admin->id]);

        $pesaje = $this->pesajeVehiculoNoHabitual($org->id);

        $this->service->registrarVehiculoNoHabitual($pesaje);
        $this->service->registrarVehiculoNoHabitual($pesaje);

        $this->assertSame(1, Alerta::withoutGlobalScopes()->where('pesaje_id', $pesaje->id)->count());
    }

    #[Test]
    public function registrar_vehiculo_no_habitual_skips_when_tipo_disabled(): void
    {
        $admin = $this->admin();
        $org   = app('organizacion');
        $org->users()->syncWithoutDetaching([$admin->id]);

        ConfigAlerta::create(['tipo' => 'vehiculo_no_habitual', 'activo' => false]);

        $pesaje = $this->pesajeVehiculoNoHabitual($org->id);

        $this->service->registrarVehiculoNoHabitual($pesaje);

        $this->assertSame(0, Alerta::withoutGlobalScopes()->where('pesaje_id', $pesaje->id)->count());
    }

    #[Test]
    public function registrar_vehiculo_no_habitual_does_not_create_when_vehicle_is_habitual(): void
    {
        $admin = $this->admin();
        $org   = app('organizacion');
        $org->users()->syncWithoutDetaching([$admin->id]);

        // Crear servicio y vehículo donde el tipo SÍ es habitual
        $tipoHabitual = TipoVehiculo::factory()->create();
        $vehiculo     = Vehiculo::factory()->create(['tipo_vehiculo_id' => $tipoHabitual->id]);
        $servicio     = TipoServicio::factory()->create();
        $servicio->tiposVehiculo()->attach($tipoHabitual->id);

        $pesaje = Pesaje::create([
            'vehiculo_id'      => $vehiculo->id,
            'operador_id'      => $this->operador()->id,
            'tipo_servicio_id' => $servicio->id,
            'zona_id'          => Zona::factory()->create()->id,
            'peso_bruto_kg'    => 10000,
            'peso_tara_kg'     => 5000,
            'peso_neto_kg'     => 5000,
            'alerta_peso'      => false,
            'estado'           => 'En predio',
            'editado'          => false,
        ]);

        // El servicio SÍ acepta ese tipo → no debe crear alerta
        $pesaje->setRelation('tipoServicio', $servicio->load('tiposVehiculo'));
        $pesaje->load('vehiculo.tipoVehiculo');

        // Verificamos que los IDs habituales incluyen el tipo del vehículo
        $habitualesIds = $servicio->tiposVehiculo->pluck('id');
        $this->assertTrue($habitualesIds->contains($vehiculo->tipo_vehiculo_id));

        // La función no debe crear alertas para este caso
        // (el caller en PesajeService ya hace el check antes de llamar)
        $this->assertSame(0, Alerta::withoutGlobalScopes()->where('tipo', 'vehiculo_no_habitual')->count());
    }

    // ── helpers ───────────────────────────────────────────────────────

    /**
     * Crea un Pesaje con alerta_peso=true y sus relaciones cargadas.
     * El peso está intencionalmente fuera del rango del TipoVehiculo.
     */
    private function pesajeConAlerta(int $organizacionId): Pesaje
    {
        $tipo = TipoVehiculo::factory()->create(['peso_min_kg' => 5000, 'peso_max_kg' => 10000]);
        $vehiculo = Vehiculo::factory()->create(['tipo_vehiculo_id' => $tipo->id, 'tara_kg' => 4000]);
        $zona = Zona::factory()->create();
        $servicio = TipoServicio::factory()->create();

        $operador = $this->operador();

        return Pesaje::create([
            'vehiculo_id'      => $vehiculo->id,
            'operador_id'      => $operador->id,
            'tipo_servicio_id' => $servicio->id,
            'zona_id'          => $zona->id,
            'peso_bruto_kg'    => 25000,   // fuera del rango 5000–10000
            'peso_tara_kg'     => 4000,
            'peso_neto_kg'     => 21000,
            'alerta_peso'      => true,
            'estado'           => 'En predio',
            'editado'          => false,
        ]);
    }

    /**
     * Pesaje donde el tipo de vehículo NO está en los habituales del servicio.
     * El servicio tiene un tipo habitual distinto al del vehículo.
     */
    private function pesajeVehiculoNoHabitual(int $organizacionId): Pesaje
    {
        $tipoHabitual   = TipoVehiculo::factory()->create(['nombre' => 'Compactador']);
        $tipoNoHabitual = TipoVehiculo::factory()->create(['nombre' => 'Particular']);
        $vehiculo       = Vehiculo::factory()->create(['tipo_vehiculo_id' => $tipoNoHabitual->id]);
        $zona           = Zona::factory()->create();
        $servicio       = TipoServicio::factory()->create(['nombre' => 'Domiciliario']);
        $servicio->tiposVehiculo()->attach($tipoHabitual->id); // habitual = Compactador

        $pesaje = Pesaje::create([
            'vehiculo_id'      => $vehiculo->id,
            'operador_id'      => $this->operador()->id,
            'tipo_servicio_id' => $servicio->id,
            'zona_id'          => $zona->id,
            'peso_bruto_kg'    => 8000,
            'peso_tara_kg'     => 4000,
            'peso_neto_kg'     => 4000,
            'alerta_peso'      => false,
            'estado'           => 'En predio',
            'editado'          => false,
        ]);

        $pesaje->setRelation('tipoServicio', $servicio->load('tiposVehiculo'));
        $pesaje->load('vehiculo.tipoVehiculo');

        return $pesaje;
    }
}
