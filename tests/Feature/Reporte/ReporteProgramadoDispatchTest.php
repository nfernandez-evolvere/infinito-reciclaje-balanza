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
        // Frecuencia mensual: últimos 30 días, congelados en el registro.
        $this->assertSame(now()->subDays(30)->toDateString(), $generado->periodo_desde->toDateString());
        $this->assertSame(now()->toDateString(), $generado->periodo_hasta->toDateString());
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

    // ── calcularPeriodo / calcularProximoEnvio (movidos del job) ──────

    #[Test]
    public function calcular_periodo_preserves_previous_job_behavior_per_frecuencia(): void
    {
        $service = app(ReporteProgramadoService::class);

        [$desde, $hasta] = $service->calcularPeriodo('mensual');
        $this->assertTrue($desde->isSameDay(now()->subDays(30)));
        $this->assertTrue($hasta->isSameDay(now()));

        [$desde, $hasta] = $service->calcularPeriodo('quincenal');
        $this->assertTrue($desde->isSameDay(now()->subDays(15)));

        [$desde, $hasta] = $service->calcularPeriodo('semanal');
        $this->assertTrue($desde->isSameDay(now()->subDays(7)));

        [$desde, $hasta] = $service->calcularPeriodo('diaria');
        $this->assertTrue($desde->isSameDay(now()->subDay()));
        $this->assertTrue($hasta->isSameDay(now()->subDay()));
        $this->assertSame('00:00:00', $desde->format('H:i:s'));
        $this->assertSame('23:59:59', $hasta->format('H:i:s'));
    }

    #[Test]
    public function calcular_proximo_envio_quincenal_borders(): void
    {
        $service = app(ReporteProgramadoService::class);

        // Borde exacto: el día 14 va al 15 del mismo mes; el 15 salta al 1° del siguiente.
        Carbon::setTestNow(Carbon::parse('2026-06-14 09:00:00'));
        $this->assertSame('2026-06-15 08:00', $service->calcularProximoEnvio('quincenal')->format('Y-m-d H:i'));

        Carbon::setTestNow(Carbon::parse('2026-06-15 09:00:00'));
        $this->assertSame('2026-07-01 08:00', $service->calcularProximoEnvio('quincenal')->format('Y-m-d H:i'));

        // Mensual sin overflow: desde el 31/01 va al 1/02.
        Carbon::setTestNow(Carbon::parse('2026-01-31 09:00:00'));
        $this->assertSame('2026-02-01 08:00', $service->calcularProximoEnvio('mensual')->format('Y-m-d H:i'));

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
