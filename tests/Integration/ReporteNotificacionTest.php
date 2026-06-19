<?php

namespace Tests\Integration;

use App\Events\ReporteEstadoActualizado;
use App\Models\Alerta;
use App\Models\ReporteGenerado;
use App\Services\ReporteGeneradoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Cada transición exitosa del reporte (en_revision | enviado | fallido) crea la
 * notificación persistente (tabla alertas) para el destinatario correcto y
 * emite el evento de tiempo real. Una carrera perdida no notifica nada.
 */
class ReporteNotificacionTest extends TestCase
{
    use RefreshDatabase;

    private function service(): ReporteGeneradoService
    {
        return app(ReporteGeneradoService::class);
    }

    private function generado(array $attrs = []): ReporteGenerado
    {
        return ReporteGenerado::create([
            'origen'        => 'programado',
            'tipo'          => 'informe_mensual',
            'formato'       => 'pdf',
            'periodo_desde' => '2026-05-01',
            'periodo_hasta' => '2026-05-31',
            'destinatarios' => ['muni@x.gob'],
            'estado'        => ReporteGenerado::ESTADO_GENERANDO,
            ...$attrs,
        ]);
    }

    #[Test]
    public function en_revision_notifies_the_owner(): void
    {
        $owner = $this->admin();
        $g = $this->generado(['usuario_id' => $owner->id]);
        Event::fake([ReporteEstadoActualizado::class]);

        $ok = $this->service()->marcarEnRevision($g, 'Narrativa IA.', ['kpis' => ['total' => 1]]);

        $this->assertTrue($ok);
        $this->assertDatabaseCount('alertas', 1);
        $this->assertDatabaseHas('alertas', [
            'user_id'             => $owner->id,
            'tipo'                => Alerta::TIPO_REPORTE_REVISION,
            'reporte_generado_id' => $g->id,
            'titulo'              => 'Reporte listo para revisar',
            'leida'               => false,
        ]);

        Event::assertDispatched(
            ReporteEstadoActualizado::class,
            fn (ReporteEstadoActualizado $e) => $e->userId === $owner->id
                && $e->payload['reporte_id'] === $g->id
                && $e->payload['estado'] === ReporteGenerado::ESTADO_EN_REVISION
                && $e->payload['toast']['variant'] === 'warning'
                && $e->payload['alerta']['tipo'] === Alerta::TIPO_REPORTE_REVISION
                && $e->payload['pendientes_revision'] === 1
        );
    }

    #[Test]
    public function enviado_notifies_the_reviewer_over_the_owner(): void
    {
        $owner = $this->admin();
        $reviewer = $this->admin();
        $g = $this->generado([
            'estado'          => ReporteGenerado::ESTADO_ENVIANDO,
            'usuario_id'      => $owner->id,
            'revisado_por_id' => $reviewer->id,
        ]);
        Event::fake([ReporteEstadoActualizado::class]);

        $ok = $this->service()->marcarEnviado($g);

        $this->assertTrue($ok);
        $this->assertDatabaseCount('alertas', 1);
        $this->assertDatabaseHas('alertas', [
            'user_id'             => $reviewer->id,
            'tipo'                => Alerta::TIPO_REPORTE_ENVIADO,
            'reporte_generado_id' => $g->id,
            'titulo'              => 'Reporte enviado',
        ]);
        $this->assertDatabaseMissing('alertas', ['user_id' => $owner->id]);

        Event::assertDispatched(
            ReporteEstadoActualizado::class,
            fn (ReporteEstadoActualizado $e) => $e->userId === $reviewer->id
                && $e->payload['estado'] === ReporteGenerado::ESTADO_ENVIADO
                && $e->payload['toast']['variant'] === 'success'
        );
    }

    #[Test]
    public function enviado_falls_back_to_the_owner_without_a_reviewer(): void
    {
        $owner = $this->admin();
        $g = $this->generado([
            'estado'     => ReporteGenerado::ESTADO_ENVIANDO,
            'usuario_id' => $owner->id,
        ]);
        Event::fake([ReporteEstadoActualizado::class]);

        $this->service()->marcarEnviado($g);

        $this->assertDatabaseCount('alertas', 1);
        $this->assertDatabaseHas('alertas', [
            'user_id' => $owner->id,
            'tipo'    => Alerta::TIPO_REPORTE_ENVIADO,
        ]);
        Event::assertDispatched(
            ReporteEstadoActualizado::class,
            fn (ReporteEstadoActualizado $e) => $e->userId === $owner->id
        );
    }

    #[Test]
    public function fallido_notifies_the_owner(): void
    {
        $owner = $this->admin();
        $g = $this->generado([
            'estado'     => ReporteGenerado::ESTADO_ENVIANDO,
            'usuario_id' => $owner->id,
        ]);
        Event::fake([ReporteEstadoActualizado::class]);

        $this->service()->marcarFallo($g, 'SMTP caído');

        $this->assertDatabaseCount('alertas', 1);
        $this->assertDatabaseHas('alertas', [
            'user_id'             => $owner->id,
            'tipo'                => Alerta::TIPO_REPORTE_FALLIDO,
            'reporte_generado_id' => $g->id,
            'titulo'              => 'No se pudo procesar el reporte',
        ]);
        Event::assertDispatched(
            ReporteEstadoActualizado::class,
            fn (ReporteEstadoActualizado $e) => $e->userId === $owner->id
                && $e->payload['estado'] === ReporteGenerado::ESTADO_FALLIDO
                && $e->payload['toast']['variant'] === 'destructive'
        );
    }

    #[Test]
    public function falls_back_to_org_admins_when_the_owner_is_unknown(): void
    {
        $admin1 = $this->admin();
        $admin2 = $this->admin();
        $operador = $this->operador(); // no debe recibir nada
        $g = $this->generado(['usuario_id' => null]);
        Event::fake([ReporteEstadoActualizado::class]);

        $this->service()->marcarEnRevision($g, null, []);

        $this->assertDatabaseCount('alertas', 2);
        $this->assertDatabaseHas('alertas', ['user_id' => $admin1->id, 'tipo' => Alerta::TIPO_REPORTE_REVISION]);
        $this->assertDatabaseHas('alertas', ['user_id' => $admin2->id, 'tipo' => Alerta::TIPO_REPORTE_REVISION]);
        $this->assertDatabaseMissing('alertas', ['user_id' => $operador->id]);
        Event::assertDispatchedTimes(ReporteEstadoActualizado::class, 2);
    }

    #[Test]
    public function a_lost_race_notifies_nothing(): void
    {
        $owner = $this->admin();
        // Ya enviado: marcarEnviado no transiciona → no debe notificar.
        $g = $this->generado([
            'estado'     => ReporteGenerado::ESTADO_ENVIADO,
            'usuario_id' => $owner->id,
        ]);
        Event::fake([ReporteEstadoActualizado::class]);

        $this->assertFalse($this->service()->marcarEnviado($g));

        $this->assertDatabaseCount('alertas', 0);
        Event::assertNotDispatched(ReporteEstadoActualizado::class);
    }
}
