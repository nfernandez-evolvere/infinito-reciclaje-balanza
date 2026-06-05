<?php

namespace Tests\Integration;

use App\Models\Alerta;
use App\Models\ConfigAlerta;
use App\Models\TipoServicio;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use App\Models\Zona;
use App\Repositories\AlertaRepository;
use App\Services\AlertaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Cubre la lógica de detección automática de alertas que corre vía scheduler.
 *
 * Estrategia de fechas: Carbon::setTestNow fija "hoy" en un miércoles (2026-06-10)
 * para que "ayer" (el día analizado) sea un martes hábil (2026-06-09).
 * La ventana histórica resultante es 2026-05-10 → 2026-06-08.
 * El test de domingo fija "hoy" en lunes (2026-06-08) para que ayer sea domingo.
 */
class AlertaDeteccionTest extends TestCase
{
    use RefreshDatabase;

    private AlertaService $service;

    private int $orgId;

    private Zona $zona;

    private Vehiculo $vehiculo;

    private TipoServicio $tipoServicio;

    // Fechas de referencia
    private const HOY = '2026-06-10 01:00:00'; // miércoles

    private const AYER = '2026-06-09';          // martes — día analizado

    private const HOY_DOMINGO = '2026-06-08 01:00:00'; // lunes → ayer = domingo

    private const AYER_DOMINGO = '2026-06-07';          // domingo

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AlertaService(new AlertaRepository);
        $this->orgId = app('organizacion')->id;

        // Admin adjunto a la org para que createParaAdmins cree registros
        $admin = $this->admin();
        app('organizacion')->users()->syncWithoutDetaching([$admin->id]);

        // Modelos compartidos para crear pesajes vía DB::table
        $tipo = TipoVehiculo::factory()->create(['peso_min_kg' => 5000, 'peso_max_kg' => 30000]);
        $this->vehiculo = Vehiculo::factory()->create(['tipo_vehiculo_id' => $tipo->id, 'tara_kg' => 5000]);
        $this->zona = Zona::factory()->create();
        $this->tipoServicio = TipoServicio::factory()->create();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }

    // ══════════════════════════════════════════════════════════════════
    // detectarGapRegistro
    // ══════════════════════════════════════════════════════════════════

    #[Test]
    public function gap_registro_creates_alerta_when_no_pesajes_on_weekday(): void
    {
        Carbon::setTestNow(self::HOY);
        // Ayer (martes) sin pesajes → gap completo del horario operativo

        $this->service->detectarParaOrganizacion($this->orgId);

        $alerta = Alerta::withoutGlobalScopes()
            ->where('tipo', 'gap_registro')
            ->where('organizacion_id', $this->orgId)
            ->first();

        $this->assertNotNull($alerta);
        $this->assertSame(self::AYER, $alerta->fecha_deteccion->toDateString());
        $this->assertStringContainsString('08:00', $alerta->descripcion);
        $this->assertStringContainsString('18:00', $alerta->descripcion);
    }

    #[Test]
    public function gap_registro_skips_on_sunday(): void
    {
        Carbon::setTestNow(self::HOY_DOMINGO); // hoy=lunes → ayer=domingo

        $this->service->detectarParaOrganizacion($this->orgId);

        $this->assertSame(0, Alerta::withoutGlobalScopes()
            ->where('tipo', 'gap_registro')
            ->where('organizacion_id', $this->orgId)
            ->count());
    }

    #[Test]
    public function gap_registro_creates_alerta_when_gap_meets_threshold(): void
    {
        Carbon::setTestNow(self::HOY);

        // Pesaje a las 08:00, siguiente a las 10:00 → gap exacto de 120 min (>= umbral → ALERTA)
        $this->pesaje(self::AYER.' 08:00:00');
        $this->pesaje(self::AYER.' 10:00:00');
        // Resto del día cubierto: 10:00-18:00 = 480 min pero ya se rompe en el primero

        $this->service->detectarParaOrganizacion($this->orgId);

        $this->assertSame(1, Alerta::withoutGlobalScopes()
            ->where('tipo', 'gap_registro')
            ->where('organizacion_id', $this->orgId)
            ->count());
    }

    #[Test]
    public function gap_registro_does_not_create_when_all_gaps_are_below_threshold(): void
    {
        Carbon::setTestNow(self::HOY);
        // Pesajes cada 90 min de 08:00 a 17:30 → ningún gap >= 120 min (incluyendo 17:30→18:00 = 30 min)
        foreach (['08:00', '09:30', '11:00', '12:30', '14:00', '15:30', '17:00', '17:30'] as $hora) {
            $this->pesaje(self::AYER." {$hora}:00");
        }

        $this->service->detectarParaOrganizacion($this->orgId);

        $this->assertSame(0, Alerta::withoutGlobalScopes()
            ->where('tipo', 'gap_registro')
            ->where('organizacion_id', $this->orgId)
            ->count());
    }

    #[Test]
    public function gap_registro_creates_exactly_one_alerta_even_with_multiple_gaps(): void
    {
        Carbon::setTestNow(self::HOY);
        // Solo un pesaje a las 08:00 → hay dos gaps: 08:00→siguiente y eventual→18:00
        $this->pesaje(self::AYER.' 08:00:00');

        $this->service->detectarParaOrganizacion($this->orgId);

        // El algoritmo hace break después del primer gap detectado
        $this->assertSame(1, Alerta::withoutGlobalScopes()
            ->where('tipo', 'gap_registro')
            ->where('organizacion_id', $this->orgId)
            ->count());
    }

    #[Test]
    public function gap_registro_is_idempotent(): void
    {
        Carbon::setTestNow(self::HOY);

        $this->service->detectarParaOrganizacion($this->orgId);
        $this->service->detectarParaOrganizacion($this->orgId); // segunda ejecución

        $this->assertSame(1, Alerta::withoutGlobalScopes()
            ->where('tipo', 'gap_registro')
            ->where('organizacion_id', $this->orgId)
            ->count());
    }

    #[Test]
    public function gap_registro_does_not_create_when_tipo_disabled(): void
    {
        Carbon::setTestNow(self::HOY);
        ConfigAlerta::create(['tipo' => 'gap_registro', 'activo' => false]);

        $this->service->detectarParaOrganizacion($this->orgId);

        $this->assertSame(0, Alerta::withoutGlobalScopes()
            ->where('tipo', 'gap_registro')
            ->where('organizacion_id', $this->orgId)
            ->count());
    }

    #[Test]
    public function gap_registro_uses_configured_horario_operativo_in_description(): void
    {
        Carbon::setTestNow(self::HOY);

        ConfigAlerta::create([
            'tipo'        => 'gap_registro',
            'activo'      => true,
            'hora_inicio' => '06:00',
            'hora_fin'    => '22:00',
        ]);
        // Ayer (martes) sin pesajes → gap completo del horario operativo configurado

        $this->service->detectarParaOrganizacion($this->orgId);

        $alerta = Alerta::withoutGlobalScopes()
            ->where('tipo', 'gap_registro')
            ->where('organizacion_id', $this->orgId)
            ->firstOrFail();

        $this->assertStringContainsString('06:00', $alerta->descripcion);
        $this->assertStringContainsString('22:00', $alerta->descripcion);
        $this->assertStringNotContainsString('08:00', $alerta->descripcion);
    }

    #[Test]
    public function gap_registro_respects_configured_window_and_ignores_pesajes_outside_it(): void
    {
        Carbon::setTestNow(self::HOY);

        // Ventana angosta 09:00–11:00. Con pesajes cada 60 min adentro no hay gap >= 120.
        // Con la ventana default (08–18) el pesaje de las 14:00 generaría un gap → este test
        // pasa solo si la ventana configurada se respeta y los pesajes externos se ignoran.
        ConfigAlerta::create([
            'tipo'        => 'gap_registro',
            'activo'      => true,
            'hora_inicio' => '09:00',
            'hora_fin'    => '11:00',
        ]);

        $this->pesaje(self::AYER.' 09:00:00');
        $this->pesaje(self::AYER.' 10:00:00');
        $this->pesaje(self::AYER.' 11:00:00');
        $this->pesaje(self::AYER.' 14:00:00'); // fuera de la ventana → debe ignorarse

        $this->service->detectarParaOrganizacion($this->orgId);

        $this->assertSame(0, Alerta::withoutGlobalScopes()
            ->where('tipo', 'gap_registro')
            ->where('organizacion_id', $this->orgId)
            ->count());
    }

    // ══════════════════════════════════════════════════════════════════
    // detectarVolumenAtipico
    // ══════════════════════════════════════════════════════════════════

    #[Test]
    public function volumen_atipico_creates_alerta_when_volume_significantly_below_average(): void
    {
        Carbon::setTestNow(self::HOY);

        // Historial: 5 días × 20.000 kg/día → promedio = 20 t/día
        foreach (['2026-05-10', '2026-05-15', '2026-05-20', '2026-05-25', '2026-05-30'] as $fecha) {
            $this->pesaje($fecha.' 09:00:00', 20000);
        }

        // Ayer: 5.000 kg = 5 t → 75% por debajo del promedio (threshold=20%)
        $this->pesaje(self::AYER.' 09:00:00', 5000);

        $this->service->detectarParaOrganizacion($this->orgId);

        $alerta = Alerta::withoutGlobalScopes()
            ->where('tipo', 'volumen_diario_atipico')
            ->where('organizacion_id', $this->orgId)
            ->first();

        $this->assertNotNull($alerta);
        $this->assertSame(self::AYER, $alerta->fecha_deteccion->toDateString());
        $this->assertStringContainsString('5.0 t', $alerta->descripcion);
        $this->assertStringContainsString('por debajo', $alerta->descripcion);
    }

    #[Test]
    public function volumen_atipico_creates_alerta_when_volume_significantly_above_average(): void
    {
        Carbon::setTestNow(self::HOY);

        foreach (['2026-05-10', '2026-05-15', '2026-05-20', '2026-05-25', '2026-05-30'] as $fecha) {
            $this->pesaje($fecha.' 09:00:00', 20000); // 20 t/día
        }

        // Ayer: 50.000 kg = 50 t → 150% por encima del promedio
        $this->pesaje(self::AYER.' 09:00:00', 50000);

        $this->service->detectarParaOrganizacion($this->orgId);

        $alerta = Alerta::withoutGlobalScopes()
            ->where('tipo', 'volumen_diario_atipico')
            ->where('organizacion_id', $this->orgId)
            ->first();

        $this->assertNotNull($alerta);
        $this->assertStringContainsString('por encima', $alerta->descripcion);
    }

    #[Test]
    public function volumen_atipico_does_not_create_when_deviation_is_below_threshold(): void
    {
        Carbon::setTestNow(self::HOY);

        foreach (['2026-05-10', '2026-05-15', '2026-05-20', '2026-05-25', '2026-05-30'] as $fecha) {
            $this->pesaje($fecha.' 09:00:00', 20000); // promedio = 20 t/día
        }

        // Ayer: 17.000 kg = 17 t → 15% por debajo (< threshold 20%) → sin alerta
        $this->pesaje(self::AYER.' 09:00:00', 17000);

        $this->service->detectarParaOrganizacion($this->orgId);

        $this->assertSame(0, Alerta::withoutGlobalScopes()
            ->where('tipo', 'volumen_diario_atipico')
            ->where('organizacion_id', $this->orgId)
            ->count());
    }

    #[Test]
    public function volumen_atipico_boundary_exactly_at_threshold_creates_alerta(): void
    {
        Carbon::setTestNow(self::HOY);

        foreach (['2026-05-10', '2026-05-15', '2026-05-20', '2026-05-25', '2026-05-30'] as $fecha) {
            $this->pesaje($fecha.' 09:00:00', 20000); // promedio = 20 t/día
        }

        // Ayer: 16.000 kg = 16 t → exactamente 20% por debajo (= threshold → ALERTA, condición >=)
        $this->pesaje(self::AYER.' 09:00:00', 16000);

        $this->service->detectarParaOrganizacion($this->orgId);

        $this->assertSame(1, Alerta::withoutGlobalScopes()
            ->where('tipo', 'volumen_diario_atipico')
            ->where('organizacion_id', $this->orgId)
            ->count());
    }

    #[Test]
    public function volumen_atipico_skips_when_fewer_than_5_days_of_history(): void
    {
        Carbon::setTestNow(self::HOY);

        // Solo 3 días de historial → diasHistorial < 5 → no se detecta
        foreach (['2026-05-10', '2026-05-15', '2026-05-20'] as $fecha) {
            $this->pesaje($fecha.' 09:00:00', 20000);
        }
        $this->pesaje(self::AYER.' 09:00:00', 1000); // volumen muy bajo

        $this->service->detectarParaOrganizacion($this->orgId);

        $this->assertSame(0, Alerta::withoutGlobalScopes()
            ->where('tipo', 'volumen_diario_atipico')
            ->where('organizacion_id', $this->orgId)
            ->count());
    }

    #[Test]
    public function volumen_atipico_skips_when_ayer_has_no_pesajes(): void
    {
        Carbon::setTestNow(self::HOY);

        foreach (['2026-05-10', '2026-05-15', '2026-05-20', '2026-05-25', '2026-05-30'] as $fecha) {
            $this->pesaje($fecha.' 09:00:00', 20000);
        }
        // Sin pesajes en ayer → toneladasDia == 0 → el gap_registro lo cubre, volumen lo ignora

        $this->service->detectarParaOrganizacion($this->orgId);

        $this->assertSame(0, Alerta::withoutGlobalScopes()
            ->where('tipo', 'volumen_diario_atipico')
            ->where('organizacion_id', $this->orgId)
            ->count());
    }

    #[Test]
    public function volumen_atipico_is_idempotent(): void
    {
        Carbon::setTestNow(self::HOY);

        foreach (['2026-05-10', '2026-05-15', '2026-05-20', '2026-05-25', '2026-05-30'] as $fecha) {
            $this->pesaje($fecha.' 09:00:00', 20000);
        }
        $this->pesaje(self::AYER.' 09:00:00', 2000);

        $this->service->detectarParaOrganizacion($this->orgId);
        $this->service->detectarParaOrganizacion($this->orgId);

        $this->assertSame(1, Alerta::withoutGlobalScopes()
            ->where('tipo', 'volumen_diario_atipico')
            ->where('organizacion_id', $this->orgId)
            ->count());
    }

    // ══════════════════════════════════════════════════════════════════
    // detectarFrecuenciaZonaAtipica
    // ══════════════════════════════════════════════════════════════════

    #[Test]
    public function frecuencia_zona_creates_alerta_when_zone_has_atypical_count(): void
    {
        Carbon::setTestNow(self::HOY);

        // Historial: 5 días × 10 pesajes/día en zona → promedio = 10/día
        foreach (['2026-05-10', '2026-05-15', '2026-05-20', '2026-05-25', '2026-05-30'] as $fecha) {
            for ($i = 0; $i < 10; $i++) {
                $this->pesaje($fecha.' 09:00:00', 5000, $this->zona->id);
            }
        }

        // Ayer: 20 pesajes en zona → 100% por encima del promedio (threshold=30%)
        for ($i = 0; $i < 20; $i++) {
            $this->pesaje(self::AYER.' 09:00:00', 5000, $this->zona->id);
        }

        $this->service->detectarParaOrganizacion($this->orgId);

        $alerta = Alerta::withoutGlobalScopes()
            ->where('tipo', 'frecuencia_zona_atipica')
            ->where('zona_id', $this->zona->id)
            ->first();

        $this->assertNotNull($alerta);
        $this->assertSame(self::AYER, $alerta->fecha_deteccion->toDateString());
        $this->assertStringContainsString('por encima', $alerta->descripcion);
    }

    #[Test]
    public function frecuencia_zona_does_not_create_when_deviation_below_threshold(): void
    {
        Carbon::setTestNow(self::HOY);

        foreach (['2026-05-10', '2026-05-15', '2026-05-20', '2026-05-25', '2026-05-30'] as $fecha) {
            for ($i = 0; $i < 10; $i++) {
                $this->pesaje($fecha.' 09:00:00', 5000, $this->zona->id);
            }
        }

        // Ayer: 11 pesajes → 10% por encima (< threshold 30%) → sin alerta
        for ($i = 0; $i < 11; $i++) {
            $this->pesaje(self::AYER.' 09:00:00', 5000, $this->zona->id);
        }

        $this->service->detectarParaOrganizacion($this->orgId);

        $this->assertSame(0, Alerta::withoutGlobalScopes()
            ->where('tipo', 'frecuencia_zona_atipica')
            ->where('organizacion_id', $this->orgId)
            ->count());
    }

    #[Test]
    public function frecuencia_zona_skips_when_fewer_than_5_days_of_history(): void
    {
        Carbon::setTestNow(self::HOY);

        // Solo 4 días de historial → skip
        foreach (['2026-05-10', '2026-05-15', '2026-05-20', '2026-05-25'] as $fecha) {
            for ($i = 0; $i < 10; $i++) {
                $this->pesaje($fecha.' 09:00:00', 5000, $this->zona->id);
            }
        }
        for ($i = 0; $i < 50; $i++) {
            $this->pesaje(self::AYER.' 09:00:00', 5000, $this->zona->id);
        }

        $this->service->detectarParaOrganizacion($this->orgId);

        $this->assertSame(0, Alerta::withoutGlobalScopes()
            ->where('tipo', 'frecuencia_zona_atipica')
            ->where('organizacion_id', $this->orgId)
            ->count());
    }

    #[Test]
    public function frecuencia_zona_creates_one_alerta_per_atypical_zone(): void
    {
        Carbon::setTestNow(self::HOY);

        $zonaB = Zona::factory()->create();

        foreach (['2026-05-10', '2026-05-15', '2026-05-20', '2026-05-25', '2026-05-30'] as $fecha) {
            for ($i = 0; $i < 10; $i++) {
                $this->pesaje($fecha.' 09:00:00', 5000, $this->zona->id);
                $this->pesaje($fecha.' 09:00:00', 5000, $zonaB->id);
            }
        }

        // Zona A atípica (doble), zona B normal
        for ($i = 0; $i < 20; $i++) {
            $this->pesaje(self::AYER.' 09:00:00', 5000, $this->zona->id);
        }
        for ($i = 0; $i < 10; $i++) {
            $this->pesaje(self::AYER.' 09:00:00', 5000, $zonaB->id);
        }

        $this->service->detectarParaOrganizacion($this->orgId);

        $alertas = Alerta::withoutGlobalScopes()
            ->where('tipo', 'frecuencia_zona_atipica')
            ->where('organizacion_id', $this->orgId)
            ->get();

        // Solo 1 alerta (zona A), zona B normal no genera
        $this->assertSame(1, $alertas->count());
        $this->assertSame($this->zona->id, $alertas->first()->zona_id);
    }

    // ══════════════════════════════════════════════════════════════════
    // detectarParaOrganizacion — aislamiento entre orgs
    // ══════════════════════════════════════════════════════════════════

    #[Test]
    public function detection_does_not_create_alertas_for_other_organizations(): void
    {
        Carbon::setTestNow(self::HOY);

        $orgB = $this->createOrganizacion('Org B');
        $adminB = $this->actingInOrg($orgB, fn () => $this->admin());
        $orgB->users()->syncWithoutDetaching([$adminB->id]);

        // Solo corremos la detección para orgB — orgA no debería recibir alertas
        $this->service->detectarParaOrganizacion($orgB->id);

        $this->assertSame(0, Alerta::withoutGlobalScopes()
            ->where('organizacion_id', $this->orgId)
            ->count());
    }

    // ══════════════════════════════════════════════════════════════════
    // Helper
    // ══════════════════════════════════════════════════════════════════

    /**
     * Inserta un pesaje directamente en DB con fecha/hora controlada.
     * Usamos DB::table para poder fijar created_at arbitrariamente.
     * ISO 8601 con separador T → requerimiento de SQL Server.
     */
    private function pesaje(string $fechaHora, int $kgNeto = 10000, ?int $zonaId = null): void
    {
        $ts = Carbon::parse($fechaHora)->format('Y-m-d\TH:i:s');

        DB::table('pesajes')->insert([
            'organizacion_id'  => $this->orgId,
            'uuid'             => (string) Str::uuid(),
            'vehiculo_id'      => $this->vehiculo->id,
            'operador_id'      => $this->operador()->id,
            'tipo_servicio_id' => $this->tipoServicio->id,
            'zona_id'          => $zonaId ?? $this->zona->id,
            'peso_bruto_kg'    => $kgNeto + 5000,
            'peso_tara_kg'     => 5000,
            'peso_neto_kg'     => $kgNeto,
            'alerta_peso'      => 0,
            'estado'           => 'Cerrado',
            'editado'          => 0,
            'created_at'       => $ts,
            'updated_at'       => $ts,
        ]);
    }
}
