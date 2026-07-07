<?php

namespace Tests\Unit;

use App\Models\ReporteConfiguracion;
use App\Models\ReporteProgramado;
use App\Support\ReporteSecciones;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Catálogo de secciones del reporte v2 y la cascada de resolución
 * (personalización del programado → configuración general → todas).
 * Sin base de datos: modelos en memoria.
 */
class ReporteSeccionesTest extends TestCase
{
    // ── Sanitización contra el catálogo ────────────────────────────────

    #[Test]
    public function sanitizar_pdf_null_devuelve_todas_las_claves_en_orden(): void
    {
        $this->assertSame(
            ['quienes_somos', 'resumen_ejecutivo', 'ingresos_semana', 'dia_semana', 'tipo_vehiculo', 'que_es_servicio', 'recoleccion_servicio', 'zonas_servicio'],
            ReporteSecciones::sanitizarPdf(null),
        );
    }

    #[Test]
    public function sanitizar_pdf_filtra_claves_invalidas_y_preserva_orden_canonico(): void
    {
        $this->assertSame(
            ['quienes_somos', 'tipo_vehiculo'],
            ReporteSecciones::sanitizarPdf(['tipo_vehiculo', 'inventada', 'quienes_somos']),
        );
    }

    #[Test]
    public function sanitizar_pdf_vacio_devuelve_lista_vacia(): void
    {
        // El PDF admite quedar sin páginas de contenido: portada y cierre son fijas.
        $this->assertSame([], ReporteSecciones::sanitizarPdf([]));
    }

    #[Test]
    public function sanitizar_excel_null_devuelve_todas_las_hojas_en_orden(): void
    {
        $this->assertSame(
            ['resumen', 'por_interno', 'por_servicio', 'base_datos'],
            ReporteSecciones::sanitizarExcel(null),
        );
    }

    #[Test]
    public function sanitizar_excel_vacio_o_invalido_cae_a_resumen(): void
    {
        // Un workbook no puede quedar sin hojas.
        $this->assertSame(['resumen'], ReporteSecciones::sanitizarExcel([]));
        $this->assertSame(['resumen'], ReporteSecciones::sanitizarExcel(['inventada']));
    }

    #[Test]
    public function es_todo_detecta_la_seleccion_completa(): void
    {
        $this->assertTrue(ReporteSecciones::esTodo(null));
        $this->assertTrue(ReporteSecciones::esTodo([
            'pdf'   => ReporteSecciones::pdfKeys(),
            'excel' => ReporteSecciones::excelKeys(),
        ]));
        $this->assertFalse(ReporteSecciones::esTodo([
            'pdf'   => ['quienes_somos'],
            'excel' => ReporteSecciones::excelKeys(),
        ]));
    }

    // ── ReporteConfiguracion (default de la organización) ──────────────

    #[Test]
    public function configuracion_sin_secciones_guardadas_devuelve_todas(): void
    {
        $config = new ReporteConfiguracion;

        $this->assertSame(ReporteSecciones::pdfKeys(), $config->seccionesPdf());
        $this->assertSame(ReporteSecciones::excelKeys(), $config->seccionesExcel());
    }

    #[Test]
    public function configuracion_con_subconjunto_devuelve_solo_lo_guardado_saneado(): void
    {
        $config = new ReporteConfiguracion([
            'secciones' => ['pdf' => ['tipo_vehiculo', 'quienes_somos', 'inventada'], 'excel' => ['base_datos']],
        ]);

        $this->assertSame(['quienes_somos', 'tipo_vehiculo'], $config->seccionesPdf());
        $this->assertSame(['base_datos'], $config->seccionesExcel());
    }

    // ── ReporteProgramado (cascada) ─────────────────────────────────────

    #[Test]
    public function programado_personalizado_pisa_la_configuracion_general(): void
    {
        $programado = new ReporteProgramado([
            'opciones' => ['secciones' => ['pdf' => ['resumen_ejecutivo'], 'excel' => ['resumen']]],
        ]);
        $config = new ReporteConfiguracion([
            'secciones' => ['pdf' => ['quienes_somos'], 'excel' => ['base_datos']],
        ]);

        $this->assertTrue($programado->seccionesPersonalizadas());
        $this->assertSame(
            ['pdf' => ['resumen_ejecutivo'], 'excel' => ['resumen']],
            $programado->secciones($config),
        );
    }

    #[Test]
    public function programado_sin_personalizar_hereda_la_configuracion_general(): void
    {
        $programado = new ReporteProgramado(['opciones' => ['formatos' => ['pdf']]]);
        $config = new ReporteConfiguracion([
            'secciones' => ['pdf' => ['dia_semana'], 'excel' => ['por_servicio']],
        ]);

        $this->assertFalse($programado->seccionesPersonalizadas());
        $this->assertSame(
            ['pdf' => ['dia_semana'], 'excel' => ['por_servicio']],
            $programado->secciones($config),
        );
    }

    #[Test]
    public function programado_sin_personalizar_ni_configuracion_usa_todas(): void
    {
        $programado = new ReporteProgramado(['opciones' => []]);

        $this->assertSame(
            ['pdf' => ReporteSecciones::pdfKeys(), 'excel' => ReporteSecciones::excelKeys()],
            $programado->secciones(null),
        );
    }
}
