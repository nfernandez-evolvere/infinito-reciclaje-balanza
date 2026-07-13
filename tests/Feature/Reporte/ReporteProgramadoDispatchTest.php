<?php

namespace Tests\Feature\Reporte;

use App\Jobs\GenerarReporteJob;
use App\Models\ReporteGenerado;
use App\Models\ReporteProgramado;
use App\Repositories\ReporteGeneradoRepository;
use App\Services\ReporteGeneradoService;
use App\Services\ReporteProgramadoService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * El despacho de un envío programado crea el registro ANTES de encolar el job
 * (iniciarGeneracion): congela período/destinatarios/formato y avanza
 * proximo_envio_at para que el scheduler no re-despache cada 15 minutos
 * mientras el reporte se genera o espera revisión.
 */
class ReporteProgramadoDispatchTest extends TestCase
{
    use RefreshDatabase;

    private function programado(array $overrides = []): ReporteProgramado
    {
        return ReporteProgramado::create(array_merge([
            'tipo'           => 'informe_mensual',
            'nombre'         => 'Informe mensual',
            'frecuencia'     => 'mensual',
            'cron_expresion' => '0 8 1 * *',
            'destinatarios'  => ['a@test.com', 'b@test.com'],
            'opciones'       => ['formatos' => ['pdf', 'excel']],
            'activo'         => true,
        ], $overrides));
    }

    private function service(): ReporteGeneradoService
    {
        return app(ReporteGeneradoService::class);
    }

    // ── iniciarGeneracion ─────────────────────────────────────────────

    #[Test]
    public function iniciar_generacion_creates_generando_record_with_frozen_data(): void
    {
        Queue::fake();

        $programado = $this->programado();

        $generado = $this->service()->iniciarGeneracion($programado);

        Queue::assertPushed(GenerarReporteJob::class, 1);
        Queue::assertPushed(GenerarReporteJob::class, fn ($job) => $job->generadoId === $generado->id);

        $this->assertSame(ReporteGenerado::ESTADO_GENERANDO, $generado->estado);
        $this->assertSame('programado', $generado->origen);
        $this->assertSame('informe_mensual', $generado->tipo);
        $this->assertSame('pdf+excel', $generado->formato);
        $this->assertEquals($programado->id, $generado->reporte_programado_id);
        $this->assertEquals($programado->organizacion_id, $generado->organizacion_id);
        $this->assertNull($generado->usuario_id);
        $this->assertSame(['a@test.com', 'b@test.com'], $generado->fresh()->destinatarios);
        // Frecuencia mensual sin cronograma vencido (corrida = hoy): cubre el
        // mes anterior a hoy, terminando ayer — congelado en el registro.
        $this->assertSame(now()->subMonthNoOverflow()->toDateString(), $generado->periodo_desde->toDateString());
        $this->assertSame(now()->subDay()->toDateString(), $generado->periodo_hasta->toDateString());
    }

    #[Test]
    public function iniciar_generacion_anchors_the_period_to_the_overdue_run_date(): void
    {
        Queue::fake();

        // Catch-up tras downtime: la corrida vencida del 1/08 define el período
        // (julio completo), aunque el scheduler recién la levante el 3/08.
        Carbon::setTestNow(Carbon::parse('2026-08-03 10:00:00'));

        $programado = $this->programado(['proximo_envio_at' => Carbon::parse('2026-08-01 08:00:00')]);

        $generado = $this->service()->iniciarGeneracion($programado);

        $this->assertSame('2026-07-01', $generado->periodo_desde->toDateString());
        $this->assertSame('2026-07-31', $generado->periodo_hasta->toDateString());

        Carbon::setTestNow();
    }

    #[Test]
    public function iniciar_generacion_advances_proximo_envio_at_so_scheduler_does_not_redispatch(): void
    {
        Queue::fake();

        // Programado vencido: el scheduler lo levantaría ahora mismo.
        $programado = $this->programado(['proximo_envio_at' => now()->subMinute()]);

        $this->service()->iniciarGeneracion($programado);

        // proximo_envio_at quedó en el futuro: una segunda corrida del closure
        // del scheduler (cada 15 min) ya no lo encuentra vencido, aunque el
        // reporte siga generándose o esperando revisión.
        $this->assertTrue($programado->fresh()->proximo_envio_at->isFuture());

        $vencidos = ReporteProgramado::withoutGlobalScopes()
            ->activos()
            ->where('proximo_envio_at', '<=', now())
            ->count();
        $this->assertSame(0, $vencidos);

        Queue::assertPushed(GenerarReporteJob::class, 1);
        $this->assertDatabaseCount('reportes_generados', 1);
    }

    #[Test]
    public function iniciar_generacion_without_advancing_keeps_proximo_envio_at(): void
    {
        Queue::fake();

        $programado = $this->programado(['proximo_envio_at' => now()->addDays(5)]);
        $proximoOriginal = $programado->proximo_envio_at;

        $this->service()->iniciarGeneracion($programado, avanzarProximo: false);

        // Comparación al segundo: el datetime de SQL Server no guarda microsegundos.
        $this->assertSame(
            $proximoOriginal->format('Y-m-d H:i:s'),
            $programado->fresh()->proximo_envio_at->format('Y-m-d H:i:s'),
        );
    }

    #[Test]
    public function enviar_ahora_creates_record_and_respects_review_config(): void
    {
        Queue::fake();

        $programado = $this->programado();

        $this->actingAs($this->admin())
            ->post(route('admin.reportes.programados.enviar-ahora', $programado))
            ->assertRedirect(route('admin.reportes.index', ['tab' => 'programados']));

        Queue::assertPushed(GenerarReporteJob::class, 1);

        $generado = ReporteGenerado::sole();
        $this->assertSame(ReporteGenerado::ESTADO_GENERANDO, $generado->estado);
        $this->assertEquals($programado->id, $generado->reporte_programado_id);
    }

    #[Test]
    public function enviar_ahora_does_not_advance_a_future_schedule(): void
    {
        Queue::fake();

        // El envío manual no mueve el ancla: la corrida programada del día
        // elegido sigue en pie (avanzarla la saltearía).
        $programado = $this->programado(['proximo_envio_at' => now()->addDays(5)->setTime(8, 0)]);
        $proximoOriginal = $programado->proximo_envio_at;

        $this->actingAs($this->admin())
            ->post(route('admin.reportes.programados.enviar-ahora', $programado))
            ->assertRedirect();

        $this->assertSame(
            $proximoOriginal->format('Y-m-d H:i:s'),
            $programado->fresh()->proximo_envio_at->format('Y-m-d H:i:s'),
        );
        Queue::assertPushed(GenerarReporteJob::class, 1);
    }

    #[Test]
    public function enviar_ahora_advances_an_overdue_schedule_to_avoid_double_dispatch(): void
    {
        Queue::fake();

        // Vencido: si no avanza, el scheduler re-dispararía el mismo
        // vencimiento en su próximo tick (doble envío).
        $programado = $this->programado(['proximo_envio_at' => now()->subHour()]);

        $this->actingAs($this->admin())
            ->post(route('admin.reportes.programados.enviar-ahora', $programado))
            ->assertRedirect();

        $this->assertTrue($programado->fresh()->proximo_envio_at->isFuture());
        Queue::assertPushed(GenerarReporteJob::class, 1);
    }

    // ── calcularPeriodo / calcularProximoEnvio ────────────────────────

    #[Test]
    public function calcular_periodo_covers_the_full_interval_before_the_run(): void
    {
        $service = app(ReporteProgramadoService::class);
        $corrida = Carbon::parse('2026-08-01 08:00:00');

        // Mensual anclado el 1: el mes calendario anterior completo.
        [$desde, $hasta] = $service->calcularPeriodo('mensual', $corrida);
        $this->assertSame('2026-07-01 00:00:00', $desde->format('Y-m-d H:i:s'));
        $this->assertSame('2026-07-31 23:59:59', $hasta->format('Y-m-d H:i:s'));

        // Semanal: los 7 días previos, terminando el día anterior a la corrida.
        [$desde, $hasta] = $service->calcularPeriodo('semanal', $corrida);
        $this->assertSame('2026-07-25 00:00:00', $desde->format('Y-m-d H:i:s'));
        $this->assertSame('2026-07-31 23:59:59', $hasta->format('Y-m-d H:i:s'));

        // Quincenal: los 15 días previos.
        [$desde, $hasta] = $service->calcularPeriodo('quincenal', $corrida);
        $this->assertSame('2026-07-17 00:00:00', $desde->format('Y-m-d H:i:s'));
        $this->assertSame('2026-07-31 23:59:59', $hasta->format('Y-m-d H:i:s'));

        // Diaria: ayer completo (mismo comportamiento que antes).
        [$desde, $hasta] = $service->calcularPeriodo('diaria', $corrida);
        $this->assertSame('2026-07-31 00:00:00', $desde->format('Y-m-d H:i:s'));
        $this->assertSame('2026-07-31 23:59:59', $hasta->format('Y-m-d H:i:s'));

        // Sin corrida explícita (descargas manuales, envío al día) usa hoy.
        [$desde, $hasta] = $service->calcularPeriodo('diaria');
        $this->assertTrue($desde->isSameDay(now()->subDay()));
        $this->assertTrue($hasta->isSameDay(now()->subDay()));
    }

    #[Test]
    public function calcular_proximo_envio_advances_from_the_anchor_preserving_the_chosen_day(): void
    {
        $service = app(ReporteProgramadoService::class);
        Carbon::setTestNow(Carbon::parse('2026-06-05 09:00:00'));

        // Mensual anclado el 5: la corrida vencida del 5/06 pasa al 5/07 (no al 1°).
        $mensual = $this->programado([
            'inicio_en'        => '2026-06-05',
            'proximo_envio_at' => Carbon::parse('2026-06-05 08:00:00'),
        ]);
        $this->assertSame('2026-07-05 08:00:00', $service->calcularProximoEnvio($mensual)->format('Y-m-d H:i:s'));

        // Semanal: +7 días exactos — conserva el día de semana elegido (viernes).
        $semanal = $this->programado([
            'frecuencia'       => 'semanal',
            'inicio_en'        => '2026-06-05',
            'proximo_envio_at' => Carbon::parse('2026-06-05 08:00:00'),
        ]);
        $this->assertSame('2026-06-12 08:00:00', $service->calcularProximoEnvio($semanal)->format('Y-m-d H:i:s'));

        // Quincenal: +15 días exactos desde el ancla.
        $quincenal = $this->programado([
            'frecuencia'       => 'quincenal',
            'inicio_en'        => '2026-06-05',
            'proximo_envio_at' => Carbon::parse('2026-06-05 08:00:00'),
        ]);
        $this->assertSame('2026-06-20 08:00:00', $service->calcularProximoEnvio($quincenal)->format('Y-m-d H:i:s'));

        Carbon::setTestNow();
    }

    #[Test]
    public function calcular_proximo_envio_mensual_recovers_the_anchor_day_after_short_months(): void
    {
        $service = app(ReporteProgramadoService::class);

        // Ancla 31: febrero clampea al 28…
        Carbon::setTestNow(Carbon::parse('2026-01-31 09:00:00'));
        $programado = $this->programado([
            'inicio_en'        => '2026-01-31',
            'proximo_envio_at' => Carbon::parse('2026-01-31 08:00:00'),
        ]);
        $this->assertSame('2026-02-28 08:00:00', $service->calcularProximoEnvio($programado)->format('Y-m-d H:i:s'));

        // …y marzo recupera el 31 gracias al día ancla de inicio_en.
        Carbon::setTestNow(Carbon::parse('2026-02-28 09:00:00'));
        $programado->proximo_envio_at = Carbon::parse('2026-02-28 08:00:00');
        $this->assertSame('2026-03-31 08:00:00', $service->calcularProximoEnvio($programado)->format('Y-m-d H:i:s'));

        Carbon::setTestNow();
    }

    #[Test]
    public function calcular_proximo_envio_catches_up_to_the_first_future_run_after_downtime(): void
    {
        $service = app(ReporteProgramadoService::class);

        // Vencido hace más de 3 meses: itera hasta la primera fecha futura
        // conservando la fase (todos los 1), sin ráfaga de envíos atrasados.
        Carbon::setTestNow(Carbon::parse('2026-06-20 09:00:00'));
        $programado = $this->programado([
            'inicio_en'        => '2026-03-01',
            'proximo_envio_at' => Carbon::parse('2026-03-01 08:00:00'),
        ]);
        $this->assertSame('2026-07-01 08:00:00', $service->calcularProximoEnvio($programado)->format('Y-m-d H:i:s'));

        Carbon::setTestNow();
    }

    // ── barrido de registros varados ──────────────────────────────────

    #[Test]
    public function stale_processing_records_are_marked_fallido_and_recent_ones_survive(): void
    {
        $programado = $this->programado();

        $crear = fn (string $estado) => ReporteGenerado::create([
            'organizacion_id'       => $programado->organizacion_id,
            'reporte_programado_id' => $programado->id,
            'origen'                => 'programado',
            'tipo'                  => 'informe_mensual',
            'formato'               => 'pdf',
            'periodo_desde'         => '2026-05-01',
            'periodo_hasta'         => '2026-05-31',
            'estado'                => $estado,
        ]);

        $generandoViejo = $crear(ReporteGenerado::ESTADO_GENERANDO);
        $enviandoViejo = $crear(ReporteGenerado::ESTADO_ENVIANDO);
        $generandoReciente = $crear(ReporteGenerado::ESTADO_GENERANDO);
        $enRevision = $crear(ReporteGenerado::ESTADO_EN_REVISION);

        // Envejecemos los dos primeros más allá del umbral de 2 horas.
        ReporteGenerado::withoutGlobalScopes()
            ->whereKey([$generandoViejo->id, $enviandoViejo->id])
            ->update(['updated_at' => now()->subHours(3)]);

        $marcados = app(ReporteGeneradoRepository::class)->marcarVaradosComoFallidos();

        $this->assertSame(2, $marcados);
        $this->assertSame(ReporteGenerado::ESTADO_FALLIDO, $generandoViejo->refresh()->estado);
        $this->assertStringContainsString('interrumpido', $generandoViejo->error);
        $this->assertSame(ReporteGenerado::ESTADO_FALLIDO, $enviandoViejo->refresh()->estado);
        // Los que siguen dentro de la ventana o no están en proceso no se tocan.
        $this->assertSame(ReporteGenerado::ESTADO_GENERANDO, $generandoReciente->refresh()->estado);
        $this->assertSame(ReporteGenerado::ESTADO_EN_REVISION, $enRevision->refresh()->estado);
    }
}
