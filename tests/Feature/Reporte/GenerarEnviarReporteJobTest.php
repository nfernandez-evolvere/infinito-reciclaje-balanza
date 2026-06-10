<?php

namespace Tests\Feature\Reporte;

use App\Jobs\GenerarEnviarReporteJob;
use App\Mail\ReporteAlertaMail;
use App\Mail\ReporteMensualMail;
use App\Models\Alerta;
use App\Models\Pesaje;
use App\Models\ReporteConfiguracion;
use App\Models\ReporteProgramado;
use App\Services\PdfService;
use App\Services\ReporteService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
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

    // ── formatos: PDF / Excel / ambos ─────────────────────────────────

    #[Test]
    public function formato_pdf_adjunta_solo_el_pdf(): void
    {
        Mail::fake();
        $this->mockDependencies();

        $programado = $this->programado(['opciones' => ['formatos' => ['pdf']]]);

        GenerarEnviarReporteJob::dispatchSync($programado->id);

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

        $programado = $this->programado(['opciones' => ['formatos' => ['excel']]]);

        GenerarEnviarReporteJob::dispatchSync($programado->id);

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

        $programado = $this->programado(['opciones' => ['formatos' => ['pdf', 'excel']]]);

        GenerarEnviarReporteJob::dispatchSync($programado->id);

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

        // Programados creados antes de esta opción: opciones es null → formatos() = ['pdf'].
        $programado = $this->programado(['opciones' => null]);

        GenerarEnviarReporteJob::dispatchSync($programado->id);

        Mail::assertSent(ReporteMensualMail::class, function (ReporteMensualMail $m) {
            return count($m->adjuntos) === 1
                && $m->adjuntos[0]['mime'] === 'application/pdf';
        });
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

    // ── calcularPeriodo según frecuencia ─────────────────────────────

    #[Test]
    public function frecuencia_mensual_usa_ultimos_30_dias(): void
    {
        Mail::fake();

        $esperadoDesde = now()->subDays(30)->startOfDay();
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
            $this->programado(['frecuencia' => 'mensual'])->id
        );

        $this->assertTrue($capturedDesde->isSameDay($esperadoDesde));
    }

    #[Test]
    public function frecuencia_semanal_usa_ultimos_7_dias(): void
    {
        Mail::fake();

        $esperadoDesde = now()->subDays(7)->startOfDay();
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
            $this->programado(['frecuencia' => 'semanal'])->id
        );

        $this->assertTrue($capturedDesde->isSameDay($esperadoDesde));
    }

    #[Test]
    public function frecuencia_quincenal_usa_ultimos_15_dias(): void
    {
        Mail::fake();

        $esperadoDesde = now()->subDays(15)->startOfDay();
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
            $this->programado(['frecuencia' => 'quincenal'])->id
        );

        $this->assertTrue($capturedDesde->isSameDay($esperadoDesde));
    }

    #[Test]
    public function frecuencia_diaria_usa_ayer(): void
    {
        Mail::fake();

        $esperadoDesde = now()->subDay()->startOfDay();
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
            $this->programado(['frecuencia' => 'diaria'])->id
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

    #[Test]
    public function job_filtra_pesajes_por_organizacion_del_programado(): void
    {
        // Regresión: antes del fix el job no bindeaba app('organizacion'), por lo que
        // PesajeRepository::paraReporte devolvía pesajes de TODAS las organizaciones.
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

        // 5 pesajes de org B (2000 kg c/u → 10 t). Sin el fix se mezclarían:
        // total=8, toneladas=13. Con el fix: total=3, toneladas=3.
        $this->actingInOrg($orgB, function () {
            Pesaje::factory()->count(5)->create([
                'estado'       => 'Cerrado',
                'peso_neto_kg' => 2000,
                'created_at'   => now()->subDays(5)->format('Y-m-d\TH:i:s'),
                'updated_at'   => now()->subDays(5)->format('Y-m-d\TH:i:s'),
            ]);
        });

        $programado = $this->actingInOrg($orgA, fn () => $this->programado(['frecuencia' => 'mensual']));

        $capturedReporte = null;
        $this->instance(PdfService::class, \Mockery::mock(PdfService::class, function ($m) use (&$capturedReporte) {
            $m->shouldReceive('fromView')->withArgs(function ($view, $data) use (&$capturedReporte) {
                $capturedReporte = $data['reporte'] ?? null;

                return true;
            })->andReturn('pdf');
        }));

        // Simula el contexto del worker: sin organización bindeada.
        // El job debe bindearlo él mismo antes de llamar a ReporteService.
        app()->forgetInstance('organizacion');

        GenerarEnviarReporteJob::dispatchSync($programado->id);

        $this->assertNotNull($capturedReporte);
        $this->assertSame(3, $capturedReporte['kpis']['total'],
            'El job debe aislar los 3 pesajes de Org A; sin el fix incluiría los 5 de Org B (total=8).'
        );
        // 3 × 1000 kg = 3000 kg = 3 t. Sin el fix: 3000 + 10000 = 13000 kg = 13 t.
        $this->assertEqualsWithDelta(3.0, $capturedReporte['kpis']['toneladas'], 0.01);
    }

    // ── tipo alertas — integración con tabla alertas ──────────────────

    #[Test]
    public function tipo_alertas_includes_alertas_from_db_table_not_pesajes(): void
    {
        Mail::fake();

        $admin = $this->admin();

        // Creamos alertas dentro de la ventana móvil de mensual (últimos 30 días)
        Alerta::create([
            'user_id'         => $admin->id,
            'tipo'            => 'peso_fuera_rango',
            'titulo'          => 'Peso fuera de rango — ABC123',
            'fecha_deteccion' => now()->subDays(5)->toDateString(),
            'leida'           => false,
        ]);
        Alerta::create([
            'user_id'         => $admin->id,
            'tipo'            => 'gap_registro',
            'titulo'          => 'Sin actividad 140 min',
            'fecha_deteccion' => now()->subDays(10)->toDateString(),
            'leida'           => true,
        ]);

        $capturedReporte = null;
        $this->instance(PdfService::class, \Mockery::mock(PdfService::class, function ($m) use (&$capturedReporte) {
            $m->shouldReceive('fromView')->withArgs(function ($_view, $data) use (&$capturedReporte) {
                $capturedReporte = $data['reporte'] ?? null;

                return true;
            })->andReturn('fake-pdf');
        }));
        $this->instance(ReporteService::class, \Mockery::mock(ReporteService::class, function ($m) {
            $m->shouldReceive('generar')->andReturn($this->fakeReporte());
        }));

        $programado = $this->programado(['tipo' => 'alertas', 'frecuencia' => 'mensual']);
        GenerarEnviarReporteJob::dispatchSync($programado->id);

        $this->assertNotNull($capturedReporte);
        $this->assertArrayHasKey('alertas', $capturedReporte);
        $this->assertInstanceOf(Collection::class, $capturedReporte['alertas']);
        $this->assertSame(2, $capturedReporte['alertas']->count());
    }

    #[Test]
    public function tipo_alertas_total_alertas_in_mail_equals_unique_event_count(): void
    {
        Mail::fake();

        $adminA = $this->admin();
        $adminB = $this->admin();

        // Mismo evento (mismo titulo+fecha) para dos admins → deduplicado a 1
        $titulo = 'Volumen atípico — '.now()->subDays(5)->format('d/m/Y');
        $fecha = now()->subDays(5)->toDateString();

        Alerta::create(['user_id' => $adminA->id, 'tipo' => 'volumen_diario_atipico', 'titulo' => $titulo, 'fecha_deteccion' => $fecha]);
        Alerta::create(['user_id' => $adminB->id, 'tipo' => 'volumen_diario_atipico', 'titulo' => $titulo, 'fecha_deteccion' => $fecha]);
        // Evento diferente → cuenta como 1 más
        Alerta::create(['user_id' => $adminA->id, 'tipo' => 'gap_registro', 'titulo' => 'Sin actividad', 'fecha_deteccion' => $fecha]);

        $this->instance(ReporteService::class, \Mockery::mock(ReporteService::class, function ($m) {
            $m->shouldReceive('generar')->andReturn($this->fakeReporte());
        }));
        $this->instance(PdfService::class, \Mockery::mock(PdfService::class, function ($m) {
            $m->shouldReceive('fromView')->andReturn('pdf');
        }));

        $programado = $this->programado(['tipo' => 'alertas', 'frecuencia' => 'mensual']);
        GenerarEnviarReporteJob::dispatchSync($programado->id);

        // 3 filas en DB, deduplicadas = 2 eventos únicos
        Mail::assertSent(ReporteAlertaMail::class, fn ($m) => $m->totalAlertas === 2);
    }

    #[Test]
    public function tipo_alertas_does_not_include_alertas_outside_period(): void
    {
        Mail::fake();

        $admin = $this->admin();

        // Alerta fuera del período (hace 3 meses)
        Alerta::create([
            'user_id'         => $admin->id,
            'tipo'            => 'gap_registro',
            'titulo'          => 'Fuera del período',
            'fecha_deteccion' => now()->subMonths(3)->toDateString(),
        ]);

        $capturedReporte = null;
        $this->instance(PdfService::class, \Mockery::mock(PdfService::class, function ($m) use (&$capturedReporte) {
            $m->shouldReceive('fromView')->withArgs(function ($_view, $data) use (&$capturedReporte) {
                $capturedReporte = $data['reporte'] ?? null;

                return true;
            })->andReturn('pdf');
        }));
        $this->instance(ReporteService::class, \Mockery::mock(ReporteService::class, function ($m) {
            $m->shouldReceive('generar')->andReturn($this->fakeReporte());
        }));

        $programado = $this->programado(['tipo' => 'alertas', 'frecuencia' => 'mensual']);
        GenerarEnviarReporteJob::dispatchSync($programado->id);

        $this->assertSame(0, $capturedReporte['alertas']->count());
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
     * instancia real dentro del job) arme un .xlsx sin tocar la base.
     */
    private function mockForExcel(): void
    {
        $reporte = $this->fakeReporte();

        $this->instance(ReporteService::class, \Mockery::mock(ReporteService::class, function ($m) use ($reporte) {
            $m->shouldReceive('generar')->andReturn($reporte);
            $m->shouldReceive('pivotsParaExcel')->andReturn($this->emptyPivots());
            $m->shouldReceive('detalleParaExcel')->andReturn([]);
        }));

        $this->instance(PdfService::class, \Mockery::mock(PdfService::class, function ($m) {
            $m->shouldReceive('fromView')->andReturn('fake-pdf-content');
        }));
    }

    /** Pivots vacíos con todas las claves que lee ReporteExcelExport. */
    private function emptyPivots(): array
    {
        $stat = ['total_viajes' => 0, 'total_kg' => 0, 'tipos' => []];

        return [
            'tipos'    => collect(),
            'diario'   => ['filas' => [], 'totales' => $stat, 'promedio' => $stat, 'maximo' => $stat, 'minimo' => $stat],
            'zonaTipo' => ['filas' => [], 'totales' => ['total_viajes' => 0, 'total_kg' => 0, 'tipos' => [], 'porcentaje' => 0.0]],
            'zonaDia'  => ['fechas' => [], 'filas' => [], 'totales' => ['dias' => [], 'total' => 0]],
        ];
    }
}
