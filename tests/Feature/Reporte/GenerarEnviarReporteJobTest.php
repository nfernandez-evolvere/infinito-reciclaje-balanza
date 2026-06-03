<?php

namespace Tests\Feature\Reporte;

use App\Jobs\GenerarEnviarReporteJob;
use App\Mail\ReporteAlertaMail;
use App\Mail\ReporteMensualMail;
use App\Models\ReporteConfiguracion;
use App\Models\ReporteProgramado;
use App\Services\PdfService;
use App\Services\ReporteService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GenerarEnviarReporteJobTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Crea un ReporteProgramado con destinatarios y periodo controlados.
     * ReporteProgramado usa BelongsToOrganizacion → requiere organizacion bindeada (ya lo hace TestCase).
     */
    private function programado(array $overrides = []): ReporteProgramado
    {
        return ReporteProgramado::create(array_merge([
            'tipo'           => 'informe_mensual',
            'nombre'         => 'Informe mensual',
            'frecuencia'     => 'mensual',
            'cron_expresion' => '0 8 1 * *',
            'destinatarios'  => ['dest1@test.com', 'dest2@test.com'],
            'opciones'       => ['periodo' => 'mes_anterior'],
            'activo'         => true,
        ], $overrides));
    }

    /** Registra fakes de ReporteService y PdfService para no generar datos/PDF reales. */
    private function mockDependencies(array $reporteData = []): void
    {
        $reporte = array_merge([
            'kpis'      => ['total' => 0, 'toneladas' => 0.0, 'dias_op' => 0, 'dias_rango' => 30, 'promedio_ton_dia' => 0, 'promedio_kg_viaje' => 0],
            'evolucion' => ['datos' => [], 'promedio' => 0, 'maximo' => 0, 'minimo' => 0],
            'zonas'     => collect(),
            'vehiculos' => collect(),
            'desde'     => now()->subMonth()->startOfMonth(),
            'hasta'     => now()->subMonth()->endOfMonth(),
        ], $reporteData);

        $this->instance(ReporteService::class, \Mockery::mock(ReporteService::class, function ($m) use ($reporte) {
            $m->shouldReceive('generar')->andReturn($reporte);
        }));

        $this->instance(PdfService::class, \Mockery::mock(PdfService::class, function ($m) {
            $m->shouldReceive('fromView')->andReturn('fake-pdf-content');
        }));
    }

    // ── tipo informe_mensual ──────────────────────────────────────────

    #[Test]
    public function envia_ReporteMensualMail_a_cada_destinatario(): void
    {
        Mail::fake();
        $this->mockDependencies();

        $programado = $this->programado(['tipo' => 'informe_mensual', 'destinatarios' => ['a@test.com', 'b@test.com']]);

        GenerarEnviarReporteJob::dispatchSync($programado->id);

        Mail::assertSent(ReporteMensualMail::class, 2);
        Mail::assertSent(ReporteMensualMail::class, fn ($m) => $m->hasTo('a@test.com'));
        Mail::assertSent(ReporteMensualMail::class, fn ($m) => $m->hasTo('b@test.com'));
        Mail::assertNotSent(ReporteAlertaMail::class);
    }

    // ── tipo alertas ──────────────────────────────────────────────────

    #[Test]
    public function envia_ReporteAlertaMail_para_tipo_alertas(): void
    {
        Mail::fake();
        $this->mockDependencies();

        $programado = $this->programado(['tipo' => 'alertas', 'destinatarios' => ['alerta@test.com']]);

        GenerarEnviarReporteJob::dispatchSync($programado->id);

        Mail::assertSent(ReporteAlertaMail::class, 1);
        Mail::assertSent(ReporteAlertaMail::class, fn ($m) => $m->hasTo('alerta@test.com'));
        Mail::assertNotSent(ReporteMensualMail::class);
    }

    // ── timestamps después del envío ──────────────────────────────────

    #[Test]
    public function actualiza_ultimo_envio_at_y_proximo_envio_at_tras_ejecutar(): void
    {
        Mail::fake();
        $this->mockDependencies();

        $programado = $this->programado();
        $this->assertNull($programado->ultimo_envio_at);

        GenerarEnviarReporteJob::dispatchSync($programado->id);

        $programado->refresh();
        $this->assertNotNull($programado->ultimo_envio_at);
        $this->assertNotNull($programado->proximo_envio_at);
        $this->assertTrue($programado->proximo_envio_at->isAfter($programado->ultimo_envio_at));
    }

    // ── ai_enabled = false ────────────────────────────────────────────

    #[Test]
    public function no_instancia_ai_service_cuando_ai_enabled_es_false(): void
    {
        Mail::fake();
        $this->mockDependencies();

        // Config con AI desactivada — el job no debe intentar llamar a la API.
        ReporteConfiguracion::create([
            'municipalidad_nombre' => 'Test',
            'ai_enabled'           => false,
            'ai_api_key'           => null,
        ]);

        $programado = $this->programado(['tipo' => 'informe_mensual']);

        // Si el job intentara llamar a la AI con api_key null lanzaría una excepción.
        // Que termine sin error ya prueba que el bloque de AI no se ejecutó.
        GenerarEnviarReporteJob::dispatchSync($programado->id);

        Mail::assertSent(ReporteMensualMail::class);
    }

    // ── calcularPeriodo ───────────────────────────────────────────────

    #[Test]
    public function periodo_mes_anterior_usa_el_mes_pasado(): void
    {
        Mail::fake();

        $esperadoDesde = now()->subMonth()->startOfMonth()->startOfDay();
        $esperadoHasta = now()->subMonth()->endOfMonth();

        $capturedDesde = null;

        $this->instance(ReporteService::class, \Mockery::mock(ReporteService::class, function ($m) use (&$capturedDesde) {
            $m->shouldReceive('generar')->withArgs(function (Carbon $desde) use (&$capturedDesde) {
                $capturedDesde = $desde;

                return true;
            })->andReturn($this->fakeReporte());
        }));
        $this->instance(PdfService::class, \Mockery::mock(PdfService::class, function ($m) {
            $m->shouldReceive('fromView')->andReturn('pdf');
        }));

        GenerarEnviarReporteJob::dispatchSync(
            $this->programado(['opciones' => ['periodo' => 'mes_anterior']])->id
        );

        $this->assertTrue($capturedDesde->isSameDay($esperadoDesde));
    }

    #[Test]
    public function periodo_mes_actual_usa_el_mes_en_curso(): void
    {
        Mail::fake();

        $esperadoDesde = now()->startOfMonth()->startOfDay();
        $capturedDesde = null;

        $this->instance(ReporteService::class, \Mockery::mock(ReporteService::class, function ($m) use (&$capturedDesde) {
            $m->shouldReceive('generar')->withArgs(function (Carbon $desde) use (&$capturedDesde) {
                $capturedDesde = $desde;

                return true;
            })->andReturn($this->fakeReporte());
        }));
        $this->instance(PdfService::class, \Mockery::mock(PdfService::class, function ($m) {
            $m->shouldReceive('fromView')->andReturn('pdf');
        }));

        GenerarEnviarReporteJob::dispatchSync(
            $this->programado(['opciones' => ['periodo' => 'mes_actual']])->id
        );

        $this->assertTrue($capturedDesde->isSameDay($esperadoDesde));
    }

    // ── regresión de tenancy: withoutGlobalScopes ─────────────────────

    #[Test]
    public function el_job_encuentra_el_programado_aunque_sea_de_otra_org(): void
    {
        Mail::fake();
        $this->mockDependencies();

        // El Job usa withoutGlobalScopes() — debe encontrar el programado sin importar
        // cuál es la org bindeada en ese momento (el job corre en el worker, sin sesión).
        $orgA = $this->createOrganizacion('Org A');
        $orgB = $this->createOrganizacion('Org B');

        $programado = $this->actingInOrg($orgB, fn () => $this->programado());

        // Bindeamos orgA como tenant activo (distinta de la org del programado).
        app()->instance('organizacion', $orgA);

        // No debe lanzar ModelNotFoundException.
        GenerarEnviarReporteJob::dispatchSync($programado->id);

        Mail::assertSent(ReporteMensualMail::class);
    }

    // ── helpers privados ──────────────────────────────────────────────

    private function fakeReporte(): array
    {
        return [
            'kpis'      => ['total' => 0, 'toneladas' => 0.0, 'dias_op' => 0, 'dias_rango' => 30, 'promedio_ton_dia' => 0, 'promedio_kg_viaje' => 0],
            'evolucion' => ['datos' => [], 'promedio' => 0, 'maximo' => 0, 'minimo' => 0],
            'zonas'     => collect(),
            'vehiculos' => collect(),
            'desde'     => now()->subMonth()->startOfMonth(),
            'hasta'     => now()->subMonth()->endOfMonth(),
        ];
    }
}
