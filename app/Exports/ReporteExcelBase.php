<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Base compartida de los exports de reporte a Excel: paleta verde (misma identidad
 * que los PDF, escala green-* de design-tokens.css), formatos numéricos y los helpers
 * de estilo (barras, encabezados, celdas, bordes). Las subclases solo implementan
 * build() armando las hojas con estos helpers.
 *
 * El array $reporte recibe la salida de ReporteService más las claves que cada export
 * necesite (config, pivots, detalle aplanado, kg_netos_total, …).
 */
abstract class ReporteExcelBase
{
    protected const C_TITLE = 'FF1E461F';        // verde oscuro (green-900) — banner de título

    protected const C_SECTION = 'FF2E7D32';      // verde primario (green-700) — barras de sección

    protected const C_COLHEAD = 'FFDBF7DA';      // verde claro (green-100) — encabezados de tabla

    protected const C_TOTAL_LIGHT = 'FFE2EFDA';  // verde claro — fila TOTAL (vehículos)

    protected const C_KGBAND = 'FFE2F0D9';       // verde claro — banda en columnas KG

    protected const C_TOTAL_DARK = 'FF385724';   // verde oscuro — fila TOTALES (pivots)

    protected const C_STAT_PROM = 'FFFFF2CC';    // ámbar — fila PROMEDIO

    protected const C_STAT_MAX = 'FFFCE4EC';     // rosa — fila MÁXIMO

    protected const C_STAT_MIN = 'FFE8F5E9';     // verde — fila MÍNIMO

    protected const C_ZEBRA = 'FFF5F5F5';        // gris — zebra striping

    protected const C_WHITE = 'FFFFFFFF';

    protected const C_INK = 'FF000000';

    protected const C_GRID = 'FFD9D9D9';

    protected const FMT_INT = '#,##0';

    protected const FMT_DEC1 = '#,##0.0';

    protected const FMT_PCT = '0.0%';

    protected const FMT_DATE = 'dd/mm/yyyy';

    /** Primera columna de contenido (B): A queda como margen. */
    protected const C0 = 2;

    public function __construct(protected array $reporte) {}

    /** Arma el spreadsheet completo. Cada export define sus hojas. */
    abstract protected function build(): Spreadsheet;

    public function download(string $filename): StreamedResponse
    {
        $writer = new Xlsx($this->build());

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /** Devuelve el .xlsx como bytes en memoria, para adjuntarlo a un email. */
    public function contents(): string
    {
        $writer = new Xlsx($this->build());

        ob_start();
        $writer->save('php://output');

        return (string) ob_get_clean();
    }

    // ── Bloques reutilizables ────────────────────────────────────────────────

    /**
     * Desglose diario de ingresos: Fecha · Total Viajes · Total KG · {tipo} Viajes ·
     * {tipo} KG por cada tipo presente, más las filas de estadística TOTALES /
     * PROMEDIO / MÁXIMO / MÍNIMO. Lo consumen tanto el export v1 (hoja Resumen) como
     * el v2 (hoja Por N° interno); el bloque `$diario` viene de calcularDiarioPorTipo().
     *
     * @param  Collection<int, array{id: int, nombre: string}>  $tipos
     * @param  array{filas: list<array>, totales: array, promedio: array, maximo: array, minimo: array}  $diario
     */
    protected function buildDiario(Worksheet $sheet, int $row, Collection $tipos, array $diario): int
    {
        $tipoIds = $tipos->pluck('id')->all();
        $lastCol = self::C0 + 2 + 2 * count($tipoIds);

        $this->bar($sheet, $row, $lastCol, 'DESGLOSE DIARIO DE INGRESOS');
        $row++;

        // Encabezados: Fecha · Total Viajes · Total KG · {tipo} Viajes · {tipo} KG …
        $headers = ['Fecha', 'Total Viajes', 'Total KG'];
        foreach ($tipos as $t) {
            $headers[] = $t['nombre']."\nViajes";
            $headers[] = $t['nombre']."\nKG";
        }
        $headRow = $row;
        $this->colHeaders($sheet, $row, self::C0, $headers);
        $row++;

        foreach ($diario['filas'] as $fila) {
            $this->date($sheet, self::C0, $row, $fila['fecha']);
            $this->num($sheet, self::C0 + 1, $row, $fila['total_viajes'], self::FMT_INT, ['align' => Alignment::HORIZONTAL_CENTER]);
            $this->num($sheet, self::C0 + 2, $row, $fila['total_kg'], self::FMT_INT, ['align' => Alignment::HORIZONTAL_CENTER, 'fill' => self::C_KGBAND]);
            $this->escribirTiposDiario($sheet, $row, $fila['tipos'], $tipoIds);
            $row++;
        }

        $row = $this->escribirStatsDiario($sheet, $row, 'TOTALES', $diario['totales'], $tipoIds, self::C_TOTAL_DARK, self::C_WHITE);
        $row = $this->escribirStatsDiario($sheet, $row, 'PROMEDIO', $diario['promedio'], $tipoIds, self::C_STAT_PROM);
        $row = $this->escribirStatsDiario($sheet, $row, 'MÁXIMO', $diario['maximo'], $tipoIds, self::C_STAT_MAX);
        $row = $this->escribirStatsDiario($sheet, $row, 'MÍNIMO', $diario['minimo'], $tipoIds, self::C_STAT_MIN);

        $this->border($sheet, $this->range(self::C0, $headRow, $lastCol, $row - 1));

        return $row + 1;
    }

    /** Columnas Viajes/KG por tipo en una fila del desglose diario (Viajes blanco, KG banda verde). */
    protected function escribirTiposDiario(Worksheet $sheet, int $row, array $porTipo, array $tipoIds): void
    {
        $col = self::C0 + 3;
        foreach ($tipoIds as $id) {
            $this->num($sheet, $col, $row, $porTipo[$id]['viajes'], self::FMT_INT, ['align' => Alignment::HORIZONTAL_CENTER]);
            $this->num($sheet, $col + 1, $row, $porTipo[$id]['kg'], self::FMT_INT, ['align' => Alignment::HORIZONTAL_CENTER, 'fill' => self::C_KGBAND]);
            $col += 2;
        }
    }

    /** Fila de estadística (TOTALES/PROMEDIO/MÁXIMO/MÍNIMO) con fondo uniforme. */
    protected function escribirStatsDiario(Worksheet $sheet, int $row, string $label, array $struct, array $tipoIds, string $fill, string $fontColor = self::C_INK): int
    {
        $lastCol = self::C0 + 2 + 2 * count($tipoIds);

        $this->text($sheet, self::C0, $row, $label, ['bold' => true, 'color' => $fontColor]);
        $this->num($sheet, self::C0 + 1, $row, $struct['total_viajes'], self::FMT_INT, ['bold' => true, 'color' => $fontColor, 'align' => Alignment::HORIZONTAL_CENTER]);
        $this->num($sheet, self::C0 + 2, $row, $struct['total_kg'], self::FMT_INT, ['bold' => true, 'color' => $fontColor, 'align' => Alignment::HORIZONTAL_CENTER]);

        $col = self::C0 + 3;
        foreach ($tipoIds as $id) {
            $this->num($sheet, $col, $row, $struct['tipos'][$id]['viajes'], self::FMT_INT, ['bold' => true, 'color' => $fontColor, 'align' => Alignment::HORIZONTAL_CENTER]);
            $this->num($sheet, $col + 1, $row, $struct['tipos'][$id]['kg'], self::FMT_INT, ['bold' => true, 'color' => $fontColor, 'align' => Alignment::HORIZONTAL_CENTER]);
            $col += 2;
        }

        $this->fillRow($sheet, $row, self::C0, $lastCol, $fill);

        return $row + 1;
    }

    /**
     * Hoja de detalle crudo de pesajes (v1: "Detalle"; v2: "Base de datos"). Vuelca el
     * detalle ya aplanado por ReporteService::detalleParaExcel() con encabezado fijo.
     */
    protected function buildDetalleSheet(Worksheet $sheet, string $titulo): void
    {
        $sheet->setTitle($titulo);

        $headers = [
            'Fecha', 'Hora', 'Patente', 'Tipo Vehículo', 'Tipo Servicio',
            'Zona', 'Turno', 'Operador', 'Peso Bruto (kg)', 'Tara (kg)',
            'Peso Neto (kg)', 'Estado', 'Editado', 'Alerta Peso',
        ];

        foreach ($headers as $i => $h) {
            $sheet->setCellValue([$i + 1, 1], $h);
        }
        $headRange = 'A1:'.Coordinate::stringFromColumnIndex(count($headers)).'1';
        $sheet->getStyle($headRange)->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => self::C_WHITE]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::C_TITLE]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(20);
        $sheet->freezePane('A2');

        $row = 2;
        foreach ($this->reporte['detalle'] as $p) {
            $sheet->setCellValue([1, $row], $p['fecha']);
            $sheet->setCellValue([2, $row], $p['hora']);
            $sheet->setCellValue([3, $row], $p['patente']);
            $sheet->setCellValue([4, $row], $p['tipo_vehiculo']);
            $sheet->setCellValue([5, $row], $p['tipo_servicio']);
            $sheet->setCellValue([6, $row], $p['zona']);
            $sheet->setCellValue([7, $row], $p['turno']);
            $sheet->setCellValue([8, $row], $p['operador']);
            $this->num($sheet, 9, $row, $p['peso_bruto_kg'], self::FMT_INT);
            $this->num($sheet, 10, $row, $p['peso_tara_kg'], self::FMT_INT);
            $this->num($sheet, 11, $row, $p['peso_neto_kg'], self::FMT_INT);
            $sheet->setCellValue([12, $row], $p['estado']);
            $sheet->setCellValue([13, $row], $p['editado'] ? 'Sí' : 'No');
            $sheet->setCellValue([14, $row], $p['alerta_peso'] ? 'Sí' : 'No');
            $row++;
        }

        foreach (range(1, count($headers)) as $c) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
        }
    }

    // ── Helpers de estilo ────────────────────────────────────────────────────

    /** Barra de sección: celdas combinadas, fondo de color, texto blanco bold centrado. */
    protected function bar(Worksheet $sheet, int $row, int $colTo, string $text, string $argb = self::C_SECTION, int $size = 13): void
    {
        $range = $this->range(self::C0, $row, $colTo, $row);
        $sheet->setCellValue($this->cell(self::C0, $row), $text);
        $sheet->mergeCells($range);
        $sheet->getStyle($range)->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => self::C_WHITE], 'size' => $size],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $argb]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($row)->setRowHeight($size + 8);
    }

    /** Fila de encabezados de tabla: fondo verde claro, bold, centrado, con borde. */
    protected function colHeaders(Worksheet $sheet, int $row, int $startCol, array $labels): void
    {
        foreach (array_values($labels) as $i => $label) {
            $sheet->setCellValue($this->cell($startCol + $i, $row), $label);
        }
        $range = $this->range($startCol, $row, $startCol + count($labels) - 1, $row);
        $sheet->getStyle($range)->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => self::C_INK]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::C_COLHEAD]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
        ]);
        $this->border($sheet, $range);
        $sheet->getRowDimension($row)->setRowHeight(28);
    }

    /**
     * Escribe un texto y le aplica opciones de estilo.
     *
     * @param  array{bold?: bool, color?: string, align?: string, fill?: string, wrap?: bool}  $opts
     */
    protected function text(Worksheet $sheet, int $col, int $row, string $value, array $opts = []): void
    {
        $coord = $this->cell($col, $row);
        $sheet->setCellValue($coord, $value);
        $this->aplicar($sheet, $coord, $opts);
    }

    /**
     * Escribe un valor numérico con su formato y opciones de estilo.
     *
     * @param  array{bold?: bool, color?: string, align?: string, fill?: string}  $opts
     */
    protected function num(Worksheet $sheet, int $col, int $row, int|float $value, string $fmt, array $opts = []): void
    {
        $coord = $this->cell($col, $row);
        $sheet->setCellValue($coord, $value);
        $sheet->getStyle($coord)->getNumberFormat()->setFormatCode($fmt);
        $this->aplicar($sheet, $coord, $opts);
    }

    /**
     * Escribe una fecha como valor de fecha real de Excel con formato.
     *
     * @param  array{bold?: bool, color?: string, align?: string, fill?: string}  $opts
     */
    protected function date(Worksheet $sheet, int $col, int $row, Carbon $value, string $fmt = self::FMT_DATE, array $opts = []): void
    {
        $coord = $this->cell($col, $row);
        $sheet->setCellValue($coord, ExcelDate::PHPToExcel($value));
        $sheet->getStyle($coord)->getNumberFormat()->setFormatCode($fmt);
        $this->aplicar($sheet, $coord, $opts + ['align' => Alignment::HORIZONTAL_CENTER]);
    }

    /** @param  array{bold?: bool, color?: string, align?: string, fill?: string, wrap?: bool}  $opts */
    protected function aplicar(Worksheet $sheet, string $coord, array $opts): void
    {
        $style = $sheet->getStyle($coord);

        if (! empty($opts['bold'])) {
            $style->getFont()->setBold(true);
        }
        if (! empty($opts['color'])) {
            $style->getFont()->getColor()->setARGB($opts['color']);
        }
        if (! empty($opts['align'])) {
            $style->getAlignment()->setHorizontal($opts['align']);
        }
        if (! empty($opts['wrap'])) {
            $style->getAlignment()->setWrapText(true);
        }
        if (! empty($opts['fill'])) {
            $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($opts['fill']);
        }
    }

    protected function fillRow(Worksheet $sheet, int $row, int $colFrom, int $colTo, string $argb): void
    {
        $sheet->getStyle($this->range($colFrom, $row, $colTo, $row))
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($argb);
    }

    protected function border(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()->setARGB(self::C_GRID);
    }

    protected function cell(int $col, int $row): string
    {
        return Coordinate::stringFromColumnIndex($col).$row;
    }

    protected function range(int $c1, int $r1, int $c2, int $r2): string
    {
        return $this->cell($c1, $r1).':'.$this->cell($c2, $r2);
    }
}
