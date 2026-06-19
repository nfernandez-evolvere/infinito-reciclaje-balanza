<?php

namespace Tests\Feature\Reporte;

use App\Jobs\EnviarReporteJob;
use App\Jobs\GenerarReporteJob;
use App\Models\Organizacion;
use App\Models\ReporteConfiguracion;
use App\Models\ReporteGenerado;
use App\Models\ReporteProgramado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Acciones HTTP del flujo de revisión: aprobar (encola el envío), descartar,
 * reintentar fallos y editar la narrativa IA. Todas las transiciones usan el
 * lock optimista del repository: la carrera entre dos admins nunca duplica
 * un envío al municipio.
 */
class ReporteRevisionTest extends TestCase
{
    use RefreshDatabase;

    private function programado(array $overrides = []): ReporteProgramado
    {
        return ReporteProgramado::create(array_merge([
            'tipo'           => 'informe_mensual',
            'nombre'         => 'Informe mensual',
            'frecuencia'     => 'mensual',
            'cron_expresion' => '0 8 1 * *',
            'destinatarios'  => ['muni@test.gob'],
            'activo'         => true,
        ], $overrides));
    }

    private function generado(array $overrides = []): ReporteGenerado
    {
        $programado = $this->programado();

        return ReporteGenerado::create(array_merge([
            'organizacion_id'       => $programado->organizacion_id,
            'reporte_programado_id' => $programado->id,
            'origen'                => 'programado',
            'tipo'                  => 'informe_mensual',
            'formato'               => 'pdf',
            'periodo_desde'         => '2026-05-01',
            'periodo_hasta'         => '2026-05-31',
            'destinatarios'         => ['muni@test.gob'],
            'estado'                => ReporteGenerado::ESTADO_EN_REVISION,
            'snapshot'              => ['kpis' => ['total' => 3], 'conclusiones' => []],
        ], $overrides));
    }

    // ── aprobar ───────────────────────────────────────────────────────

    #[Test]
    public function admin_can_approve_pending_report_and_envio_job_is_dispatched(): void
    {
        Queue::fake();

        $admin = $this->admin();
        $generado = $this->generado();

        $this->actingAs($admin)
            ->post(route('admin.reportes.historial.aprobar', $generado))
            ->assertRedirect(route('admin.reportes.index', ['tab' => 'historial']));

        Queue::assertPushed(EnviarReporteJob::class, 1);
        Queue::assertPushed(EnviarReporteJob::class, fn ($job) => $job->generadoId === $generado->id);

        $generado->refresh();
        $this->assertSame(ReporteGenerado::ESTADO_ENVIANDO, $generado->estado);
        $this->assertEquals($admin->id, $generado->revisado_por_id);
        $this->assertNotNull($generado->revisado_at);
    }

    #[Test]
    public function concurrent_approval_loses_and_does_not_dispatch(): void
    {
        Queue::fake();

        // Otro usuario ya aprobó: el registro está 'enviando'. El segundo
        // approve debe perder el lock optimista sin encolar un nuevo envío.
        $generado = $this->generado(['estado' => ReporteGenerado::ESTADO_ENVIANDO]);

        $this->actingAs($this->admin())
            ->post(route('admin.reportes.historial.aprobar', $generado))
            ->assertRedirect(route('admin.reportes.index', ['tab' => 'historial']));

        Queue::assertNotPushed(EnviarReporteJob::class);

        $generado->refresh();
        $this->assertSame(ReporteGenerado::ESTADO_ENVIANDO, $generado->estado);
        $this->assertNull($generado->revisado_por_id); // no pisa la autoría del que ganó
    }

    // ── descartar ─────────────────────────────────────────────────────

    #[Test]
    public function admin_can_discard_pending_report_with_optional_reason(): void
    {
        $admin = $this->admin();
        $generado = $this->generado();

        $this->actingAs($admin)
            ->post(route('admin.reportes.historial.descartar', $generado), ['motivo' => 'Los datos del período están incompletos'])
            ->assertRedirect(route('admin.reportes.index', ['tab' => 'historial']));

        $generado->refresh();
        $this->assertSame(ReporteGenerado::ESTADO_DESCARTADO, $generado->estado);
        $this->assertSame('Los datos del período están incompletos', $generado->motivo_descarte);
        $this->assertEquals($admin->id, $generado->revisado_por_id);
        $this->assertNotNull($generado->revisado_at);
        // El registro queda como auditoría, no se elimina.
        $this->assertDatabaseCount('reportes_generados', 1);
    }

    #[Test]
    public function discard_works_without_a_reason(): void
    {
        $generado = $this->generado();

        $this->actingAs($this->admin())
            ->post(route('admin.reportes.historial.descartar', $generado));

        $generado->refresh();
        $this->assertSame(ReporteGenerado::ESTADO_DESCARTADO, $generado->estado);
        $this->assertNull($generado->motivo_descarte);
    }

    #[Test]
    public function discard_motivo_over_500_chars_returns_validation_error(): void
    {
        $generado = $this->generado();

        $this->actingAs($this->admin())
            ->post(route('admin.reportes.historial.descartar', $generado), ['motivo' => str_repeat('m', 501)])
            ->assertSessionHasErrors('motivo');

        $this->assertSame(ReporteGenerado::ESTADO_EN_REVISION, $generado->refresh()->estado);
    }

    #[Test]
    public function discard_is_rejected_when_record_is_not_in_review(): void
    {
        $generado = $this->generado(['estado' => ReporteGenerado::ESTADO_ENVIADO]);

        $this->actingAs($this->admin())
            ->post(route('admin.reportes.historial.descartar', $generado));

        $this->assertSame(ReporteGenerado::ESTADO_ENVIADO, $generado->refresh()->estado);
    }

    // ── reintentar ────────────────────────────────────────────────────

    #[Test]
    public function admin_can_retry_failed_generation_and_period_is_preserved(): void
    {
        Queue::fake();

        // Sin snapshot: la generación nunca terminó. El período vive en el
        // registro y NO debe recalcularse; proximo_envio_at tampoco se toca.
        $generado = $this->generado([
            'estado'   => ReporteGenerado::ESTADO_FALLIDO,
            'snapshot' => null,
            'error'    => 'Timeout en la generación',
        ]);
        $programado = ReporteProgramado::findOrFail($generado->reporte_programado_id);
        $programado->update(['proximo_envio_at' => now()->addDays(10)]);
        $proximoOriginal = $programado->fresh()->proximo_envio_at;

        $this->actingAs($this->admin())
            ->post(route('admin.reportes.historial.reintentar', $generado))
            ->assertRedirect(route('admin.reportes.index', ['tab' => 'historial']));

        Queue::assertPushed(GenerarReporteJob::class, fn ($job) => $job->generadoId === $generado->id);
        Queue::assertNotPushed(EnviarReporteJob::class);

        $generado->refresh();
        $this->assertSame(ReporteGenerado::ESTADO_GENERANDO, $generado->estado);
        $this->assertNull($generado->error);
        $this->assertSame('2026-05-01', $generado->periodo_desde->toDateString());
        $this->assertSame('2026-05-31', $generado->periodo_hasta->toDateString());
        $this->assertTrue($programado->fresh()->proximo_envio_at->equalTo($proximoOriginal));
        $this->assertDatabaseCount('reportes_generados', 1);
    }

    #[Test]
    public function admin_can_retry_failed_send_reusing_existing_snapshot(): void
    {
        Queue::fake();

        // Con snapshot: solo falló el envío. Se reenvía lo congelado sin
        // regenerar (ni re-llamar a la IA).
        $generado = $this->generado([
            'estado' => ReporteGenerado::ESTADO_FALLIDO,
            'error'  => 'SMTP timeout',
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.reportes.historial.reintentar', $generado));

        Queue::assertPushed(EnviarReporteJob::class, fn ($job) => $job->generadoId === $generado->id);
        Queue::assertNotPushed(GenerarReporteJob::class);

        $generado->refresh();
        $this->assertSame(ReporteGenerado::ESTADO_ENVIANDO, $generado->estado);
        $this->assertNull($generado->error);
        $this->assertSame(3, $generado->snapshot['kpis']['total']);
    }

    #[Test]
    public function retry_is_rejected_when_estado_is_not_fallido(): void
    {
        Queue::fake();

        $generado = $this->generado(); // en_revision

        $this->actingAs($this->admin())
            ->post(route('admin.reportes.historial.reintentar', $generado));

        Queue::assertNothingPushed();
        $this->assertSame(ReporteGenerado::ESTADO_EN_REVISION, $generado->refresh()->estado);
    }

    // ── editar narrativa IA ───────────────────────────────────────────

    #[Test]
    public function admin_can_edit_ai_conclusions_while_in_review(): void
    {
        $generado = $this->generado([
            'conclusiones' => 'Texto original de la IA.',
            'snapshot'     => ['kpis' => ['total' => 3], 'conclusiones' => ['analisis' => 'Texto original de la IA.', 'modelo' => 'gemini-2.5-flash']],
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.reportes.historial.conclusiones.update', $generado), ['conclusiones' => 'Texto corregido por el admin.'])
            ->assertRedirect(route('admin.reportes.index', ['tab' => 'historial']));

        $generado->refresh();
        $this->assertSame('Texto corregido por el admin.', $generado->conclusiones);
        $this->assertSame('Texto corregido por el admin.', $generado->snapshot['conclusiones']['analisis']);
        // El texto original de la IA se preserva para auditoría.
        $this->assertSame('Texto original de la IA.', $generado->snapshot['conclusiones']['original']);
        $this->assertSame(ReporteGenerado::ESTADO_EN_REVISION, $generado->estado);

        // Segunda edición: el original NO se pisa.
        $this->actingAs($this->admin())
            ->put(route('admin.reportes.historial.conclusiones.update', $generado), ['conclusiones' => 'Texto final.']);

        $generado->refresh();
        $this->assertSame('Texto final.', $generado->snapshot['conclusiones']['analisis']);
        $this->assertSame('Texto original de la IA.', $generado->snapshot['conclusiones']['original']);
    }

    #[Test]
    public function editing_conclusions_is_rejected_when_not_in_review(): void
    {
        $generado = $this->generado([
            'estado'       => ReporteGenerado::ESTADO_ENVIADO,
            'conclusiones' => 'Narrativa ya enviada.',
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.reportes.historial.conclusiones.update', $generado), ['conclusiones' => 'Edición tardía.']);

        $this->assertSame('Narrativa ya enviada.', $generado->refresh()->conclusiones);
    }

    // ── autorización y tenancy ────────────────────────────────────────

    #[Test]
    public function admin_cannot_act_on_report_from_another_organizacion(): void
    {
        $otra = Organizacion::create(['nombre' => 'Otra organización', 'activo' => true]);

        $ajeno = ReporteGenerado::withoutGlobalScopes()->create([
            'organizacion_id' => $otra->id,
            'origen'          => 'programado',
            'tipo'            => 'informe_mensual',
            'formato'         => 'pdf',
            'periodo_desde'   => '2026-05-01',
            'periodo_hasta'   => '2026-05-31',
            'estado'          => ReporteGenerado::ESTADO_EN_REVISION,
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.reportes.historial.aprobar', $ajeno))
            ->assertNotFound();
    }

    #[Test]
    public function operador_cannot_approve_reports(): void
    {
        $generado = $this->generado();

        $this->actingAs($this->operador())
            ->post(route('admin.reportes.historial.aprobar', $generado))
            ->assertForbidden();
    }

    // ── visibilidad de pendientes ─────────────────────────────────────

    #[Test]
    public function index_seeds_pending_review_count_and_shows_review_actions(): void
    {
        $this->generado();
        $this->generado();

        $html = $this->actingAs($this->admin())
            ->get(route('admin.reportes.index', ['tab' => 'historial']))
            ->assertOk()
            ->getContent();

        // El contador del badge/banner se siembra en el store y luego se actualiza
        // en vivo por WebSocket (el texto del banner es client-side).
        $this->assertStringContainsString('reportesPendientes.count = 2', $html);
        // Estado y acción de revisión vienen server-rendered en la tabla.
        $this->assertStringContainsString('En revisión', $html);
        $this->assertStringContainsString('Revisar', $html);
    }

    #[Test]
    public function index_seeds_zero_pending_reviews_when_none(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.reportes.index', ['tab' => 'historial']))
            ->assertOk()
            ->assertSee('reportesPendientes.count = 0', false);
    }

    // ── configuración global + override por programado ────────────────

    #[Test]
    public function admin_can_toggle_global_review_requirement(): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.reportes.configuracion.update'), [
                'municipalidad_nombre' => 'Municipio Test',
                'revision_requerida'   => '1',
            ])
            ->assertRedirect(route('admin.reportes.index', ['tab' => 'configuracion']));

        $this->assertTrue(ReporteConfiguracion::sole()->revision_requerida);

        // El default global es revisar: desactivarlo es una decisión explícita
        // (switch apagado → el campo no viaja → boolean() lo persiste en false).
        $this->actingAs($this->admin())
            ->put(route('admin.reportes.configuracion.update'), [
                'municipalidad_nombre' => 'Municipio Test',
            ]);

        $this->assertFalse(ReporteConfiguracion::sole()->revision_requerida);
    }

    #[Test]
    public function revision_option_outside_allowed_values_returns_validation_error(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.reportes.programados.store'), [
                'nombre'        => 'Informe mensual',
                'tipo'          => 'informe_mensual',
                'frecuencia'    => 'mensual',
                'destinatarios' => 'muni@test.gob',
                'formatos'      => ['pdf'],
                'revision'      => 'invalido',
            ])
            ->assertSessionHasErrors('revision');

        $this->assertDatabaseCount('reportes_programados', 0);
    }

    #[Test]
    public function revision_option_is_stored_in_opciones_and_preserved_on_update(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.reportes.programados.store'), [
                'nombre'        => 'Informe mensual',
                'tipo'          => 'informe_mensual',
                'frecuencia'    => 'mensual',
                'destinatarios' => 'muni@test.gob',
                'formatos'      => ['pdf', 'excel'],
                'revision'      => 'revisar',
            ]);

        $programado = ReporteProgramado::sole();
        $this->assertSame('revisar', $programado->opciones['revision']);
        $this->assertSame(['pdf', 'excel'], $programado->opciones['formatos']);

        $this->actingAs($this->admin())
            ->put(route('admin.reportes.programados.update', $programado), [
                'nombre'        => 'Informe mensual',
                'tipo'          => 'informe_mensual',
                'frecuencia'    => 'mensual',
                'destinatarios' => 'muni@test.gob',
                'formatos'      => ['pdf', 'excel'],
                'revision'      => 'directo',
            ]);

        $programado->refresh();
        $this->assertSame('directo', $programado->opciones['revision']);
        $this->assertSame(['pdf', 'excel'], $programado->opciones['formatos']);
    }

    #[Test]
    public function requiere_revision_resolves_all_combinations(): void
    {
        $configOn = new ReporteConfiguracion(['revision_requerida' => true]);
        $configOff = new ReporteConfiguracion(['revision_requerida' => false]);

        $heredar = new ReporteProgramado(['opciones' => ['revision' => 'heredar']]);
        $revisar = new ReporteProgramado(['opciones' => ['revision' => 'revisar']]);
        $directo = new ReporteProgramado(['opciones' => ['revision' => 'directo']]);
        $sinOpcion = new ReporteProgramado(['opciones' => ['formatos' => ['pdf']]]);
        $invalido = new ReporteProgramado(['opciones' => ['revision' => 'cualquiera']]);

        // 'heredar' (explícito, ausente o inválido) sigue el default global.
        $this->assertTrue($heredar->requiereRevision($configOn));
        $this->assertFalse($heredar->requiereRevision($configOff));
        $this->assertTrue($sinOpcion->requiereRevision($configOn));
        $this->assertFalse($sinOpcion->requiereRevision($configOff));
        $this->assertTrue($invalido->requiereRevision($configOn));

        // 'revisar'/'directo' sobreescriben la config global en ambos sentidos.
        $this->assertTrue($revisar->requiereRevision($configOff));
        $this->assertTrue($revisar->requiereRevision($configOn));
        $this->assertFalse($directo->requiereRevision($configOn));
        $this->assertFalse($directo->requiereRevision($configOff));

        // Sin configuración creada también se revisa (default seguro): solo
        // el override 'directo' del programado permite el envío sin aprobación.
        $this->assertTrue($heredar->requiereRevision(null));
        $this->assertTrue($revisar->requiereRevision(null));
        $this->assertFalse($directo->requiereRevision(null));
    }
}
