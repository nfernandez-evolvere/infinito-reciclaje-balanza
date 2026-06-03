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
