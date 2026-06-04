<?php

namespace Tests\Feature\Reporte;

use App\Jobs\GenerarEnviarReporteJob;
use App\Models\ReporteProgramado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReporteProgramadoTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'nombre'         => 'Informe mensual Norte',
            'tipo'           => 'informe_mensual',
            'frecuencia'     => 'mensual',
            'cron_expresion' => '0 8 1 * *',
            'destinatarios'  => 'dest@municipio.gob.ar',
            'formatos'       => ['pdf'],
            'activo'         => true,
        ], $overrides);
    }

    private function programado(array $overrides = []): ReporteProgramado
    {
        return ReporteProgramado::create(array_merge([
            'tipo'           => 'informe_mensual',
            'nombre'         => 'Informe mensual',
            'frecuencia'     => 'mensual',
            'cron_expresion' => '0 8 1 * *',
            'destinatarios'  => ['dest@test.com'],
            'opciones'       => ['periodo' => 'mes_anterior'],
            'activo'         => true,
        ], $overrides));
    }

    // ── Acceso ────────────────────────────────────────────────────────

    #[Test]
    public function solo_admin_puede_crear_programado(): void
    {
        $this->actingAs($this->operador())
            ->post(route('admin.reportes.programados.store'), $this->payload())
            ->assertForbidden();
    }

    // ── storeProgramado ───────────────────────────────────────────────

    #[Test]
    public function store_crea_programado_y_persiste_destinatarios(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.reportes.programados.store'), $this->payload([
                'nombre'        => 'Informe Norte',
                'destinatarios' => 'a@test.com, b@test.com',
            ]))
            ->assertRedirect(route('admin.reportes.index', ['tab' => 'programados']));

        $programado = ReporteProgramado::where('nombre', 'Informe Norte')->first();
        $this->assertNotNull($programado);
        $this->assertContains('a@test.com', $programado->destinatarios);
        $this->assertContains('b@test.com', $programado->destinatarios);
    }

    #[Test]
    public function store_validates_nombre_required(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.reportes.programados.store'), $this->payload(['nombre' => '']))
            ->assertSessionHasErrors('nombre');
    }

    #[Test]
    public function store_validates_tipo_valido(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.reportes.programados.store'), $this->payload(['tipo' => 'invalido']))
            ->assertSessionHasErrors('tipo');
    }

    #[Test]
    public function store_validates_destinatarios_required(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.reportes.programados.store'), $this->payload(['destinatarios' => '']))
            ->assertSessionHasErrors('destinatarios');
    }

    // ── formatos del informe mensual ──────────────────────────────────

    #[Test]
    public function store_persiste_los_formatos_seleccionados(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.reportes.programados.store'), $this->payload([
                'nombre'   => 'Informe con Excel',
                'formatos' => ['pdf', 'excel'],
            ]))
            ->assertRedirect(route('admin.reportes.index', ['tab' => 'programados']));

        $programado = ReporteProgramado::where('nombre', 'Informe con Excel')->first();
        $this->assertNotNull($programado);
        $this->assertSame(['pdf', 'excel'], $programado->opciones['formatos']);
        $this->assertSame(['pdf', 'excel'], $programado->formatos());
    }

    #[Test]
    public function store_normaliza_formatos_a_orden_canonico_pdf_excel(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.reportes.programados.store'), $this->payload([
                'nombre'   => 'Informe orden',
                'formatos' => ['excel', 'pdf'],
            ]))
            ->assertRedirect();

        $programado = ReporteProgramado::where('nombre', 'Informe orden')->first();
        $this->assertSame(['pdf', 'excel'], $programado->opciones['formatos']);
    }

    #[Test]
    public function store_informe_mensual_requiere_al_menos_un_formato(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.reportes.programados.store'), $this->payload(['formatos' => []]))
            ->assertSessionHasErrors('formatos');

        $this->assertDatabaseMissing('reportes_programados', ['nombre' => 'Informe mensual Norte']);
    }

    #[Test]
    public function store_rechaza_un_formato_no_soportado(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.reportes.programados.store'), $this->payload(['formatos' => ['word']]))
            ->assertSessionHasErrors('formatos.0');
    }

    #[Test]
    public function store_alertas_no_requiere_formatos_y_queda_en_pdf(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.reportes.programados.store'), $this->payload([
                'nombre'   => 'Alertas Norte',
                'tipo'     => 'alertas',
                'formatos' => [],
            ]))
            ->assertRedirect(route('admin.reportes.index', ['tab' => 'programados']))
            ->assertSessionHasNoErrors();

        $programado = ReporteProgramado::where('nombre', 'Alertas Norte')->first();
        $this->assertNotNull($programado);
        $this->assertSame(['pdf'], $programado->formatos());
    }

    #[Test]
    public function update_preserva_otras_opciones_al_guardar_formatos(): void
    {
        $programado = $this->programado(['opciones' => ['periodo' => 'mes_anterior']]);

        $this->actingAs($this->admin())
            ->put(route('admin.reportes.programados.update', $programado), $this->payload([
                'formatos' => ['excel'],
            ]))
            ->assertRedirect();

        $programado->refresh();
        $this->assertSame(['excel'], $programado->opciones['formatos']);
        $this->assertSame('mes_anterior', $programado->opciones['periodo']);
    }

    // ── updateProgramado ──────────────────────────────────────────────

    #[Test]
    public function update_modifica_el_nombre_y_persiste(): void
    {
        $programado = $this->programado(['nombre' => 'Nombre Viejo']);

        $this->actingAs($this->admin())
            ->put(route('admin.reportes.programados.update', $programado), $this->payload([
                'nombre' => 'Nombre Nuevo',
            ]))
            ->assertRedirect(route('admin.reportes.index', ['tab' => 'programados']));

        $this->assertDatabaseHas('reportes_programados', [
            'id'     => $programado->id,
            'nombre' => 'Nombre Nuevo',
        ]);
    }

    // ── destroyProgramado ─────────────────────────────────────────────

    #[Test]
    public function destroy_elimina_el_programado(): void
    {
        $programado = $this->programado();

        $this->actingAs($this->admin())
            ->delete(route('admin.reportes.programados.destroy', $programado))
            ->assertRedirect(route('admin.reportes.index', ['tab' => 'programados']));

        $this->assertDatabaseMissing('reportes_programados', ['id' => $programado->id]);
    }

    // ── enviarAhoraProgramado ─────────────────────────────────────────

    #[Test]
    public function enviar_ahora_despacha_el_job_a_la_cola(): void
    {
        Queue::fake();

        $programado = $this->programado();

        $this->actingAs($this->admin())
            ->post(route('admin.reportes.programados.enviar-ahora', $programado))
            ->assertRedirect(route('admin.reportes.index', ['tab' => 'programados']));

        Queue::assertPushed(GenerarEnviarReporteJob::class, fn ($job) => $job->programadoId === $programado->id);
    }

    #[Test]
    public function enviar_ahora_despacha_exactamente_un_job(): void
    {
        Queue::fake();

        $programado = $this->programado();

        $this->actingAs($this->admin())
            ->post(route('admin.reportes.programados.enviar-ahora', $programado));

        Queue::assertPushed(GenerarEnviarReporteJob::class, 1);
    }
}
