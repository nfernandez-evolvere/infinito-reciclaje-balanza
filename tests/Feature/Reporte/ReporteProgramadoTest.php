<?php

namespace Tests\Feature\Reporte;

use App\Jobs\GenerarReporteJob;
use App\Models\ReporteConfiguracion;
use App\Models\ReporteGenerado;
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
        // El tipo "alertas" solo es un tipo válido si la organización lo habilitó
        // en su configuración de reportes (default en la migración: deshabilitado).
        ReporteConfiguracion::create(['tipo_alertas_activo' => true]);

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

    // ── secciones del informe ─────────────────────────────────────────

    #[Test]
    public function store_personalizado_persiste_las_secciones_saneadas(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.reportes.programados.store'), $this->payload([
                'nombre'                   => 'Informe recortado',
                'formatos'                 => ['pdf', 'excel'],
                'secciones_personalizadas' => '1',
                'secciones'                => [
                    'pdf'   => ['tipo_vehiculo', 'quienes_somos'], // desordenadas a propósito
                    'excel' => ['base_datos'],
                ],
            ]))
            ->assertRedirect(route('admin.reportes.index', ['tab' => 'programados']))
            ->assertSessionHasNoErrors();

        $programado = ReporteProgramado::where('nombre', 'Informe recortado')->first();
        $this->assertNotNull($programado);
        $this->assertTrue($programado->seccionesPersonalizadas());
        // Saneadas al orden canónico del catálogo.
        $this->assertSame(
            ['pdf' => ['quienes_somos', 'tipo_vehiculo'], 'excel' => ['base_datos']],
            $programado->opciones['secciones'],
        );
    }

    #[Test]
    public function store_sin_personalizar_no_guarda_secciones_y_hereda(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.reportes.programados.store'), $this->payload([
                'nombre' => 'Informe heredado',
                // El modal siempre manda las listas (arrancan con el default general):
                // sin personalizar deben ignorarse.
                'secciones_personalizadas' => '0',
                'secciones'                => ['pdf' => ['quienes_somos'], 'excel' => ['resumen']],
            ]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $programado = ReporteProgramado::where('nombre', 'Informe heredado')->first();
        $this->assertFalse($programado->seccionesPersonalizadas());
        $this->assertArrayNotHasKey('secciones', $programado->opciones);

        $config = new ReporteConfiguracion(['secciones' => ['pdf' => ['dia_semana'], 'excel' => ['por_servicio']]]);
        $this->assertSame(['pdf' => ['dia_semana'], 'excel' => ['por_servicio']], $programado->secciones($config));
    }

    #[Test]
    public function store_personalizado_con_excel_requiere_al_menos_una_hoja(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.reportes.programados.store'), $this->payload([
                'formatos'                 => ['pdf', 'excel'],
                'secciones_personalizadas' => '1',
                'secciones'                => ['pdf' => ['quienes_somos']],
            ]))
            ->assertSessionHasErrors('secciones.excel');

        $this->assertDatabaseMissing('reportes_programados', ['nombre' => 'Informe mensual Norte']);
    }

    #[Test]
    public function store_personalizado_sin_formato_excel_no_exige_hojas(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.reportes.programados.store'), $this->payload([
                'nombre'                   => 'Solo PDF recortado',
                'formatos'                 => ['pdf'],
                'secciones_personalizadas' => '1',
                'secciones'                => ['pdf' => ['resumen_ejecutivo']],
            ]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $programado = ReporteProgramado::where('nombre', 'Solo PDF recortado')->first();
        $this->assertSame(['resumen_ejecutivo'], $programado->opciones['secciones']['pdf']);
        // Sin hojas elegidas, el guard del catálogo asegura un Excel válido si
        // algún día se agrega el formato.
        $this->assertSame(['resumen'], $programado->opciones['secciones']['excel']);
    }

    #[Test]
    public function store_rechaza_una_seccion_pdf_invalida(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.reportes.programados.store'), $this->payload([
                'secciones_personalizadas' => '1',
                'secciones'                => ['pdf' => ['inventada'], 'excel' => ['resumen']],
            ]))
            ->assertSessionHasErrors('secciones.pdf.0');
    }

    #[Test]
    public function update_quita_la_personalizacion_al_volver_a_heredar(): void
    {
        $programado = $this->programado([
            'opciones' => ['formatos' => ['pdf'], 'secciones' => ['pdf' => ['quienes_somos'], 'excel' => ['resumen']]],
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.reportes.programados.update', $programado), $this->payload([
                'secciones_personalizadas' => '0',
            ]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $programado->refresh();
        $this->assertArrayNotHasKey('secciones', $programado->opciones);
        $this->assertFalse($programado->seccionesPersonalizadas());
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
    public function enviar_ahora_crea_el_registro_y_despacha_el_job_a_la_cola(): void
    {
        Queue::fake();

        $programado = $this->programado();

        $this->actingAs($this->admin())
            ->post(route('admin.reportes.programados.enviar-ahora', $programado))
            ->assertRedirect(route('admin.reportes.index', ['tab' => 'programados']));

        // El registro se crea ANTES de despachar (iniciarGeneracion) y el job
        // recibe su id, no el del programado.
        $generado = ReporteGenerado::sole();
        $this->assertSame(ReporteGenerado::ESTADO_GENERANDO, $generado->estado);
        $this->assertEquals($programado->id, $generado->reporte_programado_id);
        $this->assertSame(['dest@test.com'], $generado->destinatarios);

        Queue::assertPushed(GenerarReporteJob::class, fn ($job) => $job->generadoId === $generado->id);
    }

    #[Test]
    public function enviar_ahora_despacha_exactamente_un_job(): void
    {
        Queue::fake();

        $programado = $this->programado();

        $this->actingAs($this->admin())
            ->post(route('admin.reportes.programados.enviar-ahora', $programado));

        Queue::assertPushed(GenerarReporteJob::class, 1);
    }

    #[Test]
    public function enviar_ahora_por_ajax_devuelve_el_toast_en_json_sin_redirigir(): void
    {
        Queue::fake();

        $programado = $this->programado();

        $this->actingAs($this->admin())
            ->postJson(route('admin.reportes.programados.enviar-ahora', $programado))
            ->assertOk()
            ->assertJsonPath('toast.variant', 'success')
            ->assertJsonStructure(['toast' => ['message', 'description', 'variant']]);

        Queue::assertPushed(GenerarReporteJob::class, 1);
    }
}
