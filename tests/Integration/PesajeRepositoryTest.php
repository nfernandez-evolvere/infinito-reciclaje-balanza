<?php

namespace Tests\Integration;

use App\Models\Pesaje;
use App\Models\Vehiculo;
use App\Repositories\PesajeRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PesajeRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PesajeRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PesajeRepository;
    }

    /** Pesaje cerrado de hoy con neto explícito. */
    private function cerrado(int $neto, array $overrides = []): Pesaje
    {
        return Pesaje::factory()->create(array_merge([
            'created_at'   => today(),
            'estado'       => 'Cerrado',
            'peso_neto_kg' => $neto,
        ], $overrides));
    }

    // ── kpisFiltrado ──────────────────────────────────────────────────

    #[Test]
    public function kpisFiltrado_retorna_ceros_cuando_no_hay_pesajes(): void
    {
        $kpis = $this->repository->kpisFiltrado([]);

        $this->assertSame(0, $kpis['total']);
        $this->assertSame(0.0, $kpis['toneladas_netas']);
        $this->assertSame(0, $kpis['promedio_kg']);
        $this->assertSame(0, $kpis['en_predio']);
    }

    #[Test]
    public function kpisFiltrado_calcula_total_toneladas_y_promedio(): void
    {
        $this->cerrado(5000);
        $this->cerrado(3000);

        $kpis = $this->repository->kpisFiltrado([]);

        $this->assertSame(2, $kpis['total']);
        $this->assertSame(8.0, $kpis['toneladas_netas']);   // 8000 kg → 8.0 t
        $this->assertSame(4000, $kpis['promedio_kg']);      // avg(5000, 3000)
        $this->assertSame(0, $kpis['en_predio']);
    }

    #[Test]
    public function kpisFiltrado_excluye_cancelados_del_total_toneladas_y_promedio(): void
    {
        $this->cerrado(5000);
        $this->cerrado(5000);
        // El cancelado no debe sumar ni alterar el promedio.
        $this->cerrado(9000, ['estado' => 'Cancelado']);

        $kpis = $this->repository->kpisFiltrado([]);

        $this->assertSame(2, $kpis['total']);
        $this->assertSame(10.0, $kpis['toneladas_netas']);  // 9000 excluido
        $this->assertSame(5000, $kpis['promedio_kg']);      // avg(5000, 5000), sin el 9000
        $this->assertSame(0, $kpis['en_predio']);
    }

    #[Test]
    public function kpisFiltrado_en_predio_cuenta_solo_estado_en_predio(): void
    {
        $this->cerrado(6000, ['estado' => 'En predio']);
        $this->cerrado(4000, ['estado' => 'Cerrado']);
        $this->cerrado(9000, ['estado' => 'Cancelado']);

        $kpis = $this->repository->kpisFiltrado([]);

        // total y toneladas incluyen En predio + Cerrado, nunca el Cancelado.
        $this->assertSame(2, $kpis['total']);
        $this->assertSame(10.0, $kpis['toneladas_netas']);
        $this->assertSame(5000, $kpis['promedio_kg']);      // avg(6000, 4000)
        $this->assertSame(1, $kpis['en_predio']);
    }

    #[Test]
    public function kpisFiltrado_respeta_el_filtro_por_patente(): void
    {
        $buscado = Vehiculo::factory()->create(['patente' => 'AAA111']);
        $otro = Vehiculo::factory()->create(['patente' => 'BBB222']);

        $this->cerrado(5000, ['vehiculo_id' => $buscado->id]);
        $this->cerrado(3000, ['vehiculo_id' => $otro->id]);

        $kpis = $this->repository->kpisFiltrado(['patente' => 'AAA']);

        // Los KPIs del encabezado deben reflejar el mismo subconjunto que la tabla.
        $this->assertSame(1, $kpis['total']);
        $this->assertSame(5.0, $kpis['toneladas_netas']);
        $this->assertSame(5000, $kpis['promedio_kg']);
        $this->assertSame(0, $kpis['en_predio']);
    }

    #[Test]
    public function kpisFiltrado_respeta_el_rango_de_fechas(): void
    {
        $this->cerrado(5000, ['created_at' => today()]);
        // Fuera del rango — no debe contarse.
        $this->cerrado(3000, ['created_at' => today()->subDays(10)]);

        $kpis = $this->repository->kpisFiltrado([
            'desde' => today()->toDateString(),
            'hasta' => today()->toDateString(),
        ]);

        $this->assertSame(1, $kpis['total']);
        $this->assertSame(5.0, $kpis['toneladas_netas']);
        $this->assertSame(5000, $kpis['promedio_kg']);
    }

    #[Test]
    public function kpisFiltrado_redondea_toneladas_a_un_decimal(): void
    {
        // 5460 kg → 5.46 t → round(5.46, 1) = 5.5
        $this->cerrado(5460);

        $kpis = $this->repository->kpisFiltrado([]);

        $this->assertSame(5.5, $kpis['toneladas_netas']);
    }

    #[Test]
    public function kpisFiltrado_redondea_promedio_al_entero_igual_que_el_turno(): void
    {
        // avg(5000, 2000, 4000) = 11000 / 3 = 3666.67 → round = 3667.
        // SQL Server con AVG sobre columna entera daría 3666 (trunca); el cálculo
        // SUM/COUNT en PHP redondea correctamente y coincide con kpisDelTurno.
        $this->cerrado(5000);
        $this->cerrado(2000);
        $this->cerrado(4000);

        $kpis = $this->repository->kpisFiltrado([]);

        $this->assertSame(3, $kpis['total']);
        $this->assertSame(11.0, $kpis['toneladas_netas']);
        $this->assertSame(3667, $kpis['promedio_kg']);

        // El mismo conjunto, mirado por el turno de hoy, da el mismo promedio.
        $this->assertSame($kpis['promedio_kg'], $this->repository->kpisDelTurno()['promedio_kg']);
    }

    // ── kpisDelTurno ──────────────────────────────────────────────────

    #[Test]
    public function kpisDelTurno_retorna_ceros_cuando_no_hay_pesajes(): void
    {
        $kpis = $this->repository->kpisDelTurno();

        $this->assertSame(0, $kpis['total']);
        $this->assertSame(0.0, $kpis['toneladas_netas']);
        $this->assertSame(0, $kpis['promedio_kg']);
        $this->assertSame(0, $kpis['en_predio']);
    }

    #[Test]
    public function kpisDelTurno_solo_cuenta_pesajes_de_hoy(): void
    {
        $this->cerrado(5000, ['created_at' => today()]);
        // De ayer — fuera del turno.
        $this->cerrado(9000, ['created_at' => today()->subDay()]);

        $kpis = $this->repository->kpisDelTurno();

        $this->assertSame(1, $kpis['total']);
        $this->assertSame(5.0, $kpis['toneladas_netas']);
        $this->assertSame(5000, $kpis['promedio_kg']);
        $this->assertSame(0, $kpis['en_predio']);
    }

    #[Test]
    public function kpisDelTurno_excluye_cancelados(): void
    {
        $this->cerrado(5000);
        $this->cerrado(9000, ['estado' => 'Cancelado']);

        $kpis = $this->repository->kpisDelTurno();

        $this->assertSame(1, $kpis['total']);
        $this->assertSame(5.0, $kpis['toneladas_netas']);
        $this->assertSame(5000, $kpis['promedio_kg']);
        $this->assertSame(0, $kpis['en_predio']);
    }

    #[Test]
    public function kpisDelTurno_en_predio_cuenta_solo_estado_en_predio(): void
    {
        $this->cerrado(6000, ['estado' => 'En predio']);
        $this->cerrado(4000, ['estado' => 'Cerrado']);
        $this->cerrado(9000, ['estado' => 'Cancelado']);

        $kpis = $this->repository->kpisDelTurno();

        $this->assertSame(2, $kpis['total']);
        $this->assertSame(10.0, $kpis['toneladas_netas']);
        $this->assertSame(5000, $kpis['promedio_kg']);      // avg(6000, 4000)
        $this->assertSame(1, $kpis['en_predio']);
    }

    #[Test]
    public function kpisDelTurno_redondea_promedio_al_entero(): void
    {
        // avg(5000, 2000, 4000) = 11000 / 3 = 3666.67 → round = 3667
        $this->cerrado(5000);
        $this->cerrado(2000);
        $this->cerrado(4000);

        $kpis = $this->repository->kpisDelTurno();

        $this->assertSame(3, $kpis['total']);
        $this->assertSame(11.0, $kpis['toneladas_netas']);
        $this->assertSame(3667, $kpis['promedio_kg']);
    }
}
