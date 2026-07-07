<?php

namespace Tests\Feature\Reporte;

use App\Jobs\EnviarReporteJob;
use App\Jobs\GenerarReporteJob;
use App\Mail\ReporteAlertaMail;
use App\Mail\ReporteMensualMail;
use App\Mail\ReportePendienteRevisionMail;
use App\Models\Alerta;
use App\Models\Pesaje;
use App\Models\ReporteConfiguracion;
use App\Models\ReporteGenerado;
use App\Models\ReporteProgramado;
use App\Repositories\PesajeRepository;
use App\Services\PdfService;
use App\Services\ReporteService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * El job de generación opera sobre un registro creado al despachar
 * (iniciarGeneracion): genera datos + snapshot y, según la configuración de
 * revisión, deja el registro en_revision o encadena EnviarReporteJob. Con la
 * cola sync de los tests, el camino directo ejecuta la cadena completa inline
 * (generar → enviar), por eso los asserts de mails siguen siendo posibles acá.
 */
class GenerarReporteJobTest extends TestCase
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
            'activo'         => true,
        ], $overrides));
    }

    /**
     * Crea el registro 'generando' tal como lo deja iniciarGeneracion: período,
     * destinatarios y formato congelados. El job se despacha con su id.
     */
    private function generado(ReporteProgramado $programado, array $overrides = []): ReporteGenerado
    {
        return ReporteGenerado::create(array_merge([
            'organizacion_id'       => $programado->organizacion_id,
            'reporte_programado_id' => $programado->id,
            'origen'                => 'programado',
            'tipo'                  => $programado->tipo,
            'formato'               => implode('+', $programado->formatos()),
            'periodo_desde'         => now()->subDays(30)->toDateString(),
            'periodo_hasta'         => now()->toDateString(),
            'destinatarios'         => $programado->destinatarios,
            'estado'                => ReporteGenerado::ESTADO_GENERANDO,
        ], $overrides));
    }

    /**
     * Config con la revisión desactivada. El default global es revisar antes
     * de enviar: los tests del camino directo deben elegirlo explícitamente.
     */
    private function sinRevision(): void
    {
        ReporteConfiguracion::create([
            'municipalidad_nombre' => 'Test',
            'revision_requerida'   => false,
        ]);
    }

    /** Registra fakes de ReporteService y PdfService para no generar datos/PDF reales. */
    private function mockDependencies(array $reporteData = []): void
    {
        $reporte = array_merge($this->fakeReporte(), $reporteData);

        $this->instance(ReporteService::class, \Mockery::mock(ReporteService::class, function ($m) use ($reporte) {
            $m->shouldReceive('generar')->andReturn($reporte);
            $this->stubReporteV2($m, $reporte);
        }));

        $this->instance(PdfService::class, \Mockery::mock(PdfService::class, function ($m) {
            $m->shouldReceive('fromView')->andReturn('fake-pdf-content');
        }));
    }

    /**
     * Stubs de los métodos v2 del ReporteService con estructuras vacías pero válidas:
     * se calculan con el service real sobre una colección de pesajes vacía, de modo que
     * capturarV2/rehidratarV2 y los export v2 armen el reporte desde el snapshot sin
     * tocar la base.
     */
    private function stubReporteV2($m, array $reporte): void
    {
        $real = new ReporteService(new PesajeRepository);
        $m->shouldReceive('porSemana')->andReturn($real->porSemana(collect(), $reporte['desde'], $reporte['hasta']));
        $m->shouldReceive('porDiaSemana')->andReturn($real->porDiaSemana(collect()));
        $m->shouldReceive('vehiculosOperativos')->andReturn(0);
        $m->shouldReceive('porServicio')->andReturn($real->porServicio(collect()));
        $m->shouldReceive('zonasPorServicio')->andReturn($real->zonasPorServicio(collect(), $reporte['desde'], $reporte['hasta']));
        $m->shouldReceive('datosExcelV2')->andReturn($real->datosExcelV2(collect(), $reporte['desde'], $reporte['hasta']));
    }

    // ── camino directo: generar → encadenar envío ─────────────────────

    #[Test]
    public function direct_path_generates_snapshot_chains_envio_and_sends_mail_per_recipient(): void
    {
        Mail::fake();
        $this->mockDependencies();
        $this->sinRevision();

        $programado = $this->programado(['destinatarios' => ['a@test.com', 'b@test.com']]);
        $generado = $this->generado($programado);

        GenerarReporteJob::dispatchSync($generado->id);

        Mail::assertSent(ReporteMensualMail::class, 2);
        Mail::assertSent(ReporteMensualMail::class, fn ($m) => $m->hasTo('a@test.com'));
        Mail::assertSent(ReporteMensualMail::class, fn ($m) => $m->hasTo('b@test.com'));
        Mail::assertNotSent(ReporteAlertaMail::class);

        $generado->refresh();
        $this->assertSame(ReporteGenerado::ESTADO_ENVIADO, $generado->estado);
        $this->assertNotNull($generado->snapshot);
        $this->assertArrayHasKey('kpis', $generado->snapshot);
        $this->assertNotNull($generado->enviado_at);
        $this->assertDatabaseCount('reportes_generados', 1);
    }

    #[Test]
    public function chain_updates_ultimo_envio_at_but_not_proximo_envio_at(): void
    {
        Mail::fake();
        $this->mockDependencies();
        $this->sinRevision();

        // proximo_envio_at ya fue avanzado al despachar (iniciarGeneracion):
        // el job no debe volver a tocarlo.
        $programado = $this->programado(['proximo_envio_at' => now()->addDays(10)]);
        $proximoOriginal = $programado->proximo_envio_at;
        $generado = $this->generado($programado);

        GenerarReporteJob::dispatchSync($generado->id);

        $programado->refresh();
        $this->assertNotNull($programado->ultimo_envio_at);
        // Comparación al segundo: el datetime de SQL Server no guarda microsegundos.
        $this->assertSame(
            $proximoOriginal->format('Y-m-d H:i:s'),
            $programado->proximo_envio_at->format('Y-m-d H:i:s'),
        );
    }

    // ── camino revisión ───────────────────────────────────────────────

    #[Test]
    public function review_path_leaves_record_en_revision_without_sending(): void
    {
        Mail::fake();
        $this->mockDependencies();

        ReporteConfiguracion::create([
            'municipalidad_nombre' => 'Test',
            'revision_requerida'   => true,
        ]);

        $programado = $this->programado();
        $generado = $this->generado($programado);

        GenerarReporteJob::dispatchSync($generado->id);

        Mail::assertNotSent(ReporteMensualMail::class);

        $generado->refresh();
        $this->assertSame(ReporteGenerado::ESTADO_EN_REVISION, $generado->estado);
        $this->assertNotNull($generado->snapshot);
        $this->assertArrayHasKey('kpis', $generado->snapshot);
        $this->assertNull($generado->revisado_por_id);
        $this->assertNull($generado->enviado_at);
        $this->assertSame(['dest1@test.com', 'dest2@test.com'], $generado->destinatarios);
    }

    #[Test]
    public function override_revisar_forces_review_when_global_is_disabled(): void
    {
        Mail::fake();
        $this->mockDependencies();

        ReporteConfiguracion::create([
            'municipalidad_nombre' => 'Test',
            'revision_requerida'   => false,
        ]);

        $programado = $this->programado(['opciones' => ['formatos' => ['pdf'], 'revision' => 'revisar']]);
        $generado = $this->generado($programado);

        GenerarReporteJob::dispatchSync($generado->id);

        Mail::assertNotSent(ReporteMensualMail::class);
        $this->assertSame(ReporteGenerado::ESTADO_EN_REVISION, $generado->refresh()->estado);
    }

    #[Test]
    public function override_directo_skips_review_when_global_is_enabled(): void
    {
        Mail::fake();
        $this->mockDependencies();

        ReporteConfiguracion::create([
            'municipalidad_nombre' => 'Test',
            'revision_requerida'   => true,
        ]);

        $programado = $this->programado(['opciones' => ['formatos' => ['pdf'], 'revision' => 'directo']]);
        $generado = $this->generado($programado);

        GenerarReporteJob::dispatchSync($generado->id);

        Mail::assertSent(ReporteMensualMail::class, 2);
        $this->assertSame(ReporteGenerado::ESTADO_ENVIADO, $generado->refresh()->estado);
    }

    #[Test]
    public function review_notifies_only_active_admins_of_the_organizacion(): void
    {
        Mail::fake();
        $this->mockDependencies();

        ReporteConfiguracion::create([
            'municipalidad_nombre' => 'Test',
            'revision_requerida'   => true,
        ]);

        // El factory adjunta cada usuario a la org bindeada: estos tres quedan
        // en la organización de test; el ajeno se crea dentro de la otra org.
        $this->admin(['email' => 'activo@test.com']);
        $this->admin(['email' => 'inactivo@test.com', 'activo' => false]);
        $this->operador(['email' => 'operador@test.com']);

        $otraOrg = $this->createOrganizacion('Otra Org');
        $this->actingInOrg($otraOrg, fn () => $this->admin(['email' => 'ajeno@test.com']));

        $generado = $this->generado($this->programado());

        GenerarReporteJob::dispatchSync($generado->id);

        Mail::assertSent(ReportePendienteRevisionMail::class, 1);
        Mail::assertSent(ReportePendienteRevisionMail::class, fn ($m) => $m->hasTo('activo@test.com'));
        Mail::assertNotSent(ReporteMensualMail::class);
    }

    #[Test]
    public function notification_failure_does_not_fail_the_record(): void
    {
        $this->mockDependencies();

        ReporteConfiguracion::create([
            'municipalidad_nombre' => 'Test',
            'revision_requerida'   => true,
        ]);

        $this->admin(); // el factory lo adjunta a la org bindeada

        // SMTP caído: el aviso a admins es best-effort, el reporte generado
        // debe quedar en revisión igual (el badge/banner cubren la señal).
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP caído'));

        $generado = $this->generado($this->programado());

        GenerarReporteJob::dispatchSync($generado->id);

        $this->assertSame(ReporteGenerado::ESTADO_EN_REVISION, $generado->refresh()->estado);
        $this->assertNotNull($generado->snapshot);
    }

    // ── período congelado en el registro ──────────────────────────────

    #[Test]
    public function job_uses_period_frozen_in_record_not_recalculated_from_now(): void
    {
        Mail::fake();

        $capturedDesde = null;
        $capturedHasta = null;

        $this->instance(ReporteService::class, \Mockery::mock(ReporteService::class, function ($m) use (&$capturedDesde, &$capturedHasta) {
            $m->shouldReceive('generar')->withArgs(function (Carbon $desde, Carbon $hasta) use (&$capturedDesde, &$capturedHasta) {
                $capturedDesde = $desde;
                $capturedHasta = $hasta;

                return true;
            })->andReturn($this->fakeReporte());
            $this->stubReporteV2($m, $this->fakeReporte());
        }));
        $this->instance(PdfService::class, \Mockery::mock(PdfService::class, function ($m) {
            $m->shouldReceive('fromView')->andReturn('pdf');
        }));

        // Período fijo del pasado, distinto de cualquier ventana relativa a now():
        // simula un reintento días después de la generación original.
        $generado = $this->generado($this->programado(), [
            'periodo_desde' => '2026-04-01',
            'periodo_hasta' => '2026-04-30',
        ]);

        GenerarReporteJob::dispatchSync($generado->id);

        $this->assertSame('2026-04-01', $capturedDesde->toDateString());
        $this->assertSame('2026-04-30', $capturedHasta->toDateString());
    }

    // ── fallos: update in place ───────────────────────────────────────

    #[Test]
    public function failed_marks_record_fallido_in_place_truncating_error(): void
    {
        $generado = $this->generado($this->programado());

        (new GenerarReporteJob($generado->id))->failed(new \RuntimeException(str_repeat('e', 600)));

        $this->assertDatabaseCount('reportes_generados', 1);

        $generado->refresh();
        $this->assertSame(ReporteGenerado::ESTADO_FALLIDO, $generado->estado);
        $this->assertSame(500, mb_strlen((string) $generado->error));
        $this->assertNull($generado->snapshot);
    }

    #[Test]
    public function job_marks_fallido_when_programado_was_deleted(): void
    {
        Mail::fake();
        $this->mockDependencies();

        $programado = $this->programado();
        $generado = $this->generado($programado);
        $programado->delete(); // FK nullOnDelete → reporte_programado_id queda null

        GenerarReporteJob::dispatchSync($generado->id);

        Mail::assertNotSent(ReporteMensualMail::class);

        $generado->refresh();
        $this->assertSame(ReporteGenerado::ESTADO_FALLIDO, $generado->estado);
        $this->assertStringContainsString('eliminado', $generado->error);
    }

    // ── idempotencia entre attempts ───────────────────────────────────

    #[Test]
    public function job_redispatches_envio_when_record_is_already_enviando(): void
    {
        Queue::fake();

        // Un attempt previo completó la generación pero falló el dispatch del
        // envío: el retry no debe regenerar (ni re-llamar a la IA), solo re-despachar.
        $this->instance(ReporteService::class, \Mockery::mock(ReporteService::class, function ($m) {
            $m->shouldReceive('generar')->never();
        }));

        $generado = $this->generado($this->programado(), [
            'estado'   => ReporteGenerado::ESTADO_ENVIANDO,
            'snapshot' => ['kpis' => ['total' => 1]],
        ]);

        // Con Queue::fake, dispatchSync también quedaría capturado por el fake:
        // se invoca handle() directamente para ejecutar el job de verdad.
        app()->call([new GenerarReporteJob($generado->id), 'handle']);

        Queue::assertPushed(EnviarReporteJob::class, fn ($job) => $job->generadoId === $generado->id);
        $this->assertSame(ReporteGenerado::ESTADO_ENVIANDO, $generado->refresh()->estado);
    }

    #[Test]
    public function job_skips_records_outside_generando_state(): void
    {
        Mail::fake();
        Queue::fake();

        $this->instance(ReporteService::class, \Mockery::mock(ReporteService::class, function ($m) {
            $m->shouldReceive('generar')->never();
        }));

        foreach ([ReporteGenerado::ESTADO_EN_REVISION, ReporteGenerado::ESTADO_ENVIADO, ReporteGenerado::ESTADO_DESCARTADO] as $estado) {
            $generado = $this->generado($this->programado(), ['estado' => $estado]);

            app()->call([new GenerarReporteJob($generado->id), 'handle']);

            $this->assertSame($estado, $generado->refresh()->estado);
        }

        Mail::assertNothingSent();
        Queue::assertNotPushed(EnviarReporteJob::class);
    }

    // ── IA condicional (sin cambios de comportamiento) ────────────────

    #[Test]
    public function no_instancia_ai_service_cuando_ai_enabled_es_false(): void
    {
        Mail::fake();
        $this->mockDependencies();

        ReporteConfiguracion::create([
            'municipalidad_nombre' => 'Test',
            'ai_enabled'           => false,
            'ai_api_key'           => null,
            'revision_requerida'   => false,
        ]);

        $generado = $this->generado($this->programado());

        // Si el job intentara llamar a la AI con api_key null lanzaría una excepción.
        GenerarReporteJob::dispatchSync($generado->id);

        Mail::assertSent(ReporteMensualMail::class);
        $this->assertNull($generado->refresh()->conclusiones);
    }

    // ── formatos: PDF / Excel / ambos ─────────────────────────────────

    #[Test]
    public function formato_pdf_adjunta_solo_el_pdf(): void
    {
        Mail::fake();
        $this->mockDependencies();
        $this->sinRevision();

        $generado = $this->generado($this->programado(['opciones' => ['formatos' => ['pdf']]]));

        GenerarReporteJob::dispatchSync($generado->id);

        Mail::assertSent(ReporteMensualMail::class, function (ReporteMensualMail $m) {
            return count($m->adjuntos) === 1
                && $m->adjuntos[0]['mime'] === 'application/pdf'
                && str_ends_with($m->adjuntos[0]['filename'], '.pdf');
        });
    }

    #[Test]
    public function formato_excel_adjunta_solo_el_xlsx_sin_generar_pdf(): void
    {
        Mail::fake();
        $this->mockForExcel();
        $this->sinRevision();

        $generado = $this->generado($this->programado(['opciones' => ['formatos' => ['excel']]]));

        GenerarReporteJob::dispatchSync($generado->id);

        Mail::assertSent(ReporteMensualMail::class, function (ReporteMensualMail $m) {
            return count($m->adjuntos) === 1
                && str_contains($m->adjuntos[0]['mime'], 'spreadsheetml')
                && str_ends_with($m->adjuntos[0]['filename'], '.xlsx')
                && strlen($m->adjuntos[0]['content']) > 0;
        });
    }

    #[Test]
    public function ambos_formatos_adjuntan_pdf_y_xlsx(): void
    {
        Mail::fake();
        $this->mockForExcel();
        $this->sinRevision();

        $generado = $this->generado($this->programado(['opciones' => ['formatos' => ['pdf', 'excel']]]));

        GenerarReporteJob::dispatchSync($generado->id);

        Mail::assertSent(ReporteMensualMail::class, function (ReporteMensualMail $m) {
            $mimes = array_column($m->adjuntos, 'mime');

            return count($m->adjuntos) === 2
                && in_array('application/pdf', $mimes, true)
                && collect($mimes)->contains(fn ($mime) => str_contains($mime, 'spreadsheetml'));
        });
    }

    #[Test]
    public function sin_formatos_configurados_envia_pdf_por_defecto(): void
    {
        Mail::fake();
        $this->mockDependencies();
        $this->sinRevision();

        // Programados creados antes de esta opción: opciones es null → formatos() = ['pdf'].
        $generado = $this->generado($this->programado(['opciones' => null]));

        GenerarReporteJob::dispatchSync($generado->id);

        Mail::assertSent(ReporteMensualMail::class, function (ReporteMensualMail $m) {
            return count($m->adjuntos) === 1
                && $m->adjuntos[0]['mime'] === 'application/pdf';
        });
    }

    // ── regresión de tenancy: withoutGlobalScopes ─────────────────────

    #[Test]
    public function el_job_encuentra_el_registro_aunque_sea_de_otra_org(): void
    {
        Mail::fake();
        $this->mockDependencies();

        $orgA = $this->createOrganizacion('Org A');
        $orgB = $this->createOrganizacion('Org B');

        [$programado, $generado] = $this->actingInOrg($orgB, function () {
            $this->sinRevision(); // config de orgB: camino directo
            $programado = $this->programado();

            return [$programado, $this->generado($programado)];
        });

        // Bindeamos orgA como tenant activo (distinta de la org del registro).
        app()->instance('organizacion', $orgA);

        // No debe lanzar ModelNotFoundException.
        GenerarReporteJob::dispatchSync($generado->id);

        Mail::assertSent(ReporteMensualMail::class);
    }

    #[Test]
    public function job_filtra_pesajes_por_organizacion_del_programado(): void
    {
        // Regresión: el job debe bindear app('organizacion') antes de generar,
        // o PesajeRepository::paraReporte mezclaría pesajes de TODAS las orgs.
        Mail::fake();

        $orgA = $this->createOrganizacion('Org A');
        $orgB = $this->createOrganizacion('Org B');

        // 3 pesajes de org A en el último mes (1000 kg c/u → 3 t en total)
        $this->actingInOrg($orgA, function () {
            Pesaje::factory()->count(3)->create([
                'estado'       => 'Cerrado',
                'peso_neto_kg' => 1000,
                'created_at'   => now()->subDays(5)->format('Y-m-d\TH:i:s'),
                'updated_at'   => now()->subDays(5)->format('Y-m-d\TH:i:s'),
            ]);
        });

        // 5 pesajes de org B (2000 kg c/u → 10 t). Sin el binding se mezclarían.
        $this->actingInOrg($orgB, function () {
            Pesaje::factory()->count(5)->create([
                'estado'       => 'Cerrado',
                'peso_neto_kg' => 2000,
                'created_at'   => now()->subDays(5)->format('Y-m-d\TH:i:s'),
                'updated_at'   => now()->subDays(5)->format('Y-m-d\TH:i:s'),
            ]);
        });

        [$programado, $generado] = $this->actingInOrg($orgA, function () {
            $programado = $this->programado();

            return [$programado, $this->generado($programado)];
        });

        $this->instance(PdfService::class, \Mockery::mock(PdfService::class, function ($m) {
            $m->shouldReceive('fromView')->andReturn('pdf');
        }));

        // Simula el contexto del worker: sin organización bindeada.
        app()->forgetInstance('organizacion');

        GenerarReporteJob::dispatchSync($generado->id);

        $snapshot = ReporteGenerado::withoutGlobalScopes()->find($generado->id)->snapshot;
        $this->assertSame(3, $snapshot['kpis']['total'],
            'El job debe aislar los 3 pesajes de Org A; sin el binding incluiría los 5 de Org B (total=8).'
        );
        // 3 × 1000 kg = 3000 kg = 3 t. Mezclado: 13 t.
        $this->assertEqualsWithDelta(3.0, $snapshot['kpis']['toneladas'], 0.01);
    }

    // ── tipo alertas ──────────────────────────────────────────────────

    #[Test]
    public function envia_ReporteAlertaMail_para_tipo_alertas(): void
    {
        Mail::fake();
        $this->mockDependencies();
        $this->sinRevision();

        $programado = $this->programado(['tipo' => 'alertas', 'destinatarios' => ['alerta@test.com']]);
        $generado = $this->generado($programado);

        GenerarReporteJob::dispatchSync($generado->id);

        Mail::assertSent(ReporteAlertaMail::class, 1);
        Mail::assertSent(ReporteAlertaMail::class, fn ($m) => $m->hasTo('alerta@test.com'));
        Mail::assertNotSent(ReporteMensualMail::class);
    }

    #[Test]
    public function tipo_alertas_freezes_deduplicated_alertas_and_counts_unique_events(): void
    {
        Mail::fake();
        $this->mockDependencies();
        $this->sinRevision();

        $adminA = $this->admin();
        $adminB = $this->admin();

        // Mismo evento (mismo tipo+titulo+fecha) para dos admins → deduplicado a 1
        $titulo = 'Volumen atípico — '.now()->subDays(5)->format('d/m/Y');
        $fecha = now()->subDays(5)->toDateString();

        Alerta::create(['user_id' => $adminA->id, 'tipo' => 'volumen_diario_atipico', 'titulo' => $titulo, 'fecha_deteccion' => $fecha]);
        Alerta::create(['user_id' => $adminB->id, 'tipo' => 'volumen_diario_atipico', 'titulo' => $titulo, 'fecha_deteccion' => $fecha]);
        // Evento diferente → cuenta como 1 más
        Alerta::create(['user_id' => $adminA->id, 'tipo' => 'gap_registro', 'titulo' => 'Sin actividad', 'fecha_deteccion' => $fecha]);

        $generado = $this->generado($this->programado(['tipo' => 'alertas']));

        GenerarReporteJob::dispatchSync($generado->id);

        // 3 filas en DB, deduplicadas = 2 eventos únicos: congeladas en el
        // snapshot y reflejadas en el contador del mail (enviado desde el snapshot).
        $this->assertCount(2, $generado->refresh()->snapshot['alertas']);
        Mail::assertSent(ReporteAlertaMail::class, fn ($m) => $m->totalAlertas === 2);
    }

    #[Test]
    public function tipo_alertas_does_not_include_alertas_outside_period(): void
    {
        Mail::fake();
        $this->mockDependencies();
        $this->sinRevision();

        Alerta::create([
            'user_id'         => $this->admin()->id,
            'tipo'            => 'gap_registro',
            'titulo'          => 'Fuera del período',
            'fecha_deteccion' => now()->subMonths(3)->toDateString(),
        ]);

        $generado = $this->generado($this->programado(['tipo' => 'alertas']));

        GenerarReporteJob::dispatchSync($generado->id);

        $this->assertCount(0, $generado->refresh()->snapshot['alertas']);
        Mail::assertSent(ReporteAlertaMail::class, fn ($m) => $m->totalAlertas === 0);
    }

    // ── helpers privados ──────────────────────────────────────────────

    private function fakeReporte(): array
    {
        return [
            'kpis'      => ['total' => 0, 'toneladas' => 0.0, 'dias_op' => 0, 'dias_rango' => 30, 'promedio_ton_dia' => 0, 'promedio_kg_viaje' => 0],
            'evolucion' => ['datos' => [], 'promedio' => 0, 'maximo' => 0, 'minimo' => 0],
            'zonas'     => collect(),
            'vehiculos' => collect(),
            'detalle'   => collect(),
            'desde'     => now()->subMonth()->startOfMonth(),
            'hasta'     => now()->subMonth()->endOfMonth(),
        ];
    }

    /**
     * Mocks para el camino con Excel: generar() + pivotsParaExcel() devuelven
     * estructuras vacías pero válidas, de modo que ReporteExcelExport (que se
     * instancia real en el envío) arme un .xlsx desde el snapshot sin tocar la base.
     */
    private function mockForExcel(): void
    {
        $reporte = $this->fakeReporte();

        $this->instance(ReporteService::class, \Mockery::mock(ReporteService::class, function ($m) use ($reporte) {
            $m->shouldReceive('generar')->andReturn($reporte);
            $m->shouldReceive('detalleParaExcel')->andReturn([]);
            $this->stubReporteV2($m, $reporte);
        }));

        $this->instance(PdfService::class, \Mockery::mock(PdfService::class, function ($m) {
            $m->shouldReceive('fromView')->andReturn('fake-pdf-content');
        }));
    }
}
