<?php

namespace App\Support;

/**
 * Catálogo canónico de las secciones configurables del reporte mensual v2.
 * Única fuente de verdad para la validación (requests), la UI (checkboxes de
 * configuración/programado/popover) y los generadores (Excel v2 y PDF v2).
 *
 * El formato de almacenamiento es una lista de claves habilitadas por formato:
 * `['pdf' => ['quienes_somos', ...], 'excel' => ['resumen', ...]]`. Null o
 * clave ausente significa "todas" — así los registros previos a esta opción
 * siguen generando el documento completo sin migrar datos.
 *
 * En el PDF la portada, los separadores de grupo y el cierre no son
 * configurables: el informe institucional nunca sale sin identidad. Los
 * separadores se imprimen solo si alguna página de su grupo quedó activa.
 */
class ReporteSecciones
{
    /**
     * Páginas configurables del PDF v2, en el orden del documento.
     * 'grupo' referencia el separador al que pertenece la página (null = sin grupo).
     *
     * @var array<string, array{label: string, descripcion: string, grupo: ?string}>
     */
    private const PDF = [
        'quienes_somos' => [
            'label'       => 'Quiénes somos',
            'descripcion' => 'Presentación institucional y servicios destacados.',
            'grupo'       => null,
        ],
        'resumen_ejecutivo' => [
            'label'       => 'Resumen ejecutivo',
            'descripcion' => 'KPIs principales del período.',
            'grupo'       => 'resumen_periodo',
        ],
        'ingresos_semana' => [
            'label'       => '¿Cuánto ingresa por semana?',
            'descripcion' => 'Ingresos agrupados por semana del período.',
            'grupo'       => 'resumen_periodo',
        ],
        'dia_semana' => [
            'label'       => 'Recolección según el día',
            'descripcion' => 'Acumulado por día de la semana (lunes a domingo).',
            'grupo'       => 'resumen_periodo',
        ],
        'tipo_vehiculo' => [
            'label'       => 'Composición por tipo de vehículo',
            'descripcion' => 'Reparto de kilos y viajes por tipo de vehículo.',
            'grupo'       => 'analisis_flota',
        ],
        'que_es_servicio' => [
            'label'       => '¿Qué es cada servicio?',
            'descripcion' => 'Descripción de cada servicio y sus zonas activas.',
            'grupo'       => 'zonas_servicios',
        ],
        'recoleccion_servicio' => [
            'label'       => '¿Cuánto recolecta cada servicio?',
            'descripcion' => 'Ranking de servicios por kilos del período.',
            'grupo'       => 'zonas_servicios',
        ],
        'zonas_servicio' => [
            'label'       => 'Recolección por zona',
            'descripcion' => 'Desglose por zona del servicio principal y del resto.',
            'grupo'       => 'zonas_servicios',
        ],
    ];

    /**
     * Hojas configurables del Excel v2, en el orden del workbook.
     *
     * @var array<string, array{label: string, descripcion: string}>
     */
    private const EXCEL = [
        'resumen' => [
            'label'       => 'Resumen',
            'descripcion' => 'KPIs generales, cruces por servicio y resumen por día.',
        ],
        'por_interno' => [
            'label'       => 'Por vehículo · N° interno',
            'descripcion' => 'Desglose diario por tipo y matriz de viajes por N° interno.',
        ],
        'por_servicio' => [
            'label'       => 'Hojas por servicio',
            'descripcion' => 'Una hoja por servicio con su desglose por zona y por día.',
        ],
        'base_datos' => [
            'label'       => 'Base de datos',
            'descripcion' => 'Detalle crudo de todos los pesajes del período.',
        ],
    ];

    /** @return array<string, array{label: string, descripcion: string, grupo: ?string}> */
    public static function pdf(): array
    {
        return self::PDF;
    }

    /** @return array<string, array{label: string, descripcion: string}> */
    public static function excel(): array
    {
        return self::EXCEL;
    }

    /** @return list<string> */
    public static function pdfKeys(): array
    {
        return array_keys(self::PDF);
    }

    /** @return list<string> */
    public static function excelKeys(): array
    {
        return array_keys(self::EXCEL);
    }

    /**
     * Lista de páginas PDF habilitadas: null → todas; claves desconocidas fuera,
     * preservando el orden canónico del documento. Una lista vacía es válida:
     * el PDF conserva portada y cierre fijos.
     *
     * @param  list<string>|null  $keys
     * @return list<string>
     */
    public static function sanitizarPdf(?array $keys): array
    {
        if ($keys === null) {
            return self::pdfKeys();
        }

        return array_values(array_intersect(self::pdfKeys(), $keys));
    }

    /**
     * Lista de hojas Excel habilitadas: null → todas; claves desconocidas fuera,
     * orden canónico. Un workbook no puede quedar sin hojas: si la selección
     * queda vacía cae a la hoja Resumen.
     *
     * @param  list<string>|null  $keys
     * @return list<string>
     */
    public static function sanitizarExcel(?array $keys): array
    {
        if ($keys === null) {
            return self::excelKeys();
        }

        return array_values(array_intersect(self::excelKeys(), $keys)) ?: ['resumen'];
    }

    /**
     * Shape completo `['pdf' => [...], 'excel' => [...]]` saneado a partir de un
     * valor crudo (columna json, opciones del programado o snapshot).
     *
     * @param  array{pdf?: list<string>|null, excel?: list<string>|null}|null  $secciones
     * @return array{pdf: list<string>, excel: list<string>}
     */
    public static function sanitizar(?array $secciones): array
    {
        return [
            'pdf'   => self::sanitizarPdf($secciones['pdf'] ?? null),
            'excel' => self::sanitizarExcel($secciones['excel'] ?? null),
        ];
    }

    /**
     * True si la selección equivale a "todas las secciones" (en cuyo caso
     * conviene persistir null y que los defaults sigan la evolución del catálogo).
     *
     * @param  array{pdf?: list<string>|null, excel?: list<string>|null}|null  $secciones
     */
    public static function esTodo(?array $secciones): bool
    {
        $s = self::sanitizar($secciones);

        return $s['pdf'] === self::pdfKeys() && $s['excel'] === self::excelKeys();
    }
}
