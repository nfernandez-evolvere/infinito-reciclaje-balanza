<?php

namespace App\Exports;

use Carbon\Carbon;
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
 * Exporta el reporte de pesajes a un Excel que imita el formato del reporte
 * municipal de referencia (docs/reportes/REPORTE MARZO_ INFINITO RECICLAJE.xlsx):
 *
 *  - Hoja "Resumen":  banner + RESUMEN GENERAL (KPIs), DESGLOSE POR TIPO DE
 *                     VEHÍCULO, DESGLOSE DIARIO DE INGRESOS (con stats) y
 *                     KG TOTAL — ZONA Y TIPO DE VEHÍCULO.
 *  - Hoja "Zona × Día": matriz de kg netos por zona y por cada día del período.
 *  - Hoja "Detalle":  pesajes crudos del período.
 *
 * El array $reporte recibe, además de la salida de ReporteService::generar(),
 * las claves 'config' (ReporteConfiguracion|null) y 'pivots' (salida de
 * ReporteService::pivotsParaExcel()).
 */
class ReporteExcelExport
{
    // Paleta tomada del reporte de referencia.
    private const C_TITLE = 'FF1F4E79';        // azul oscuro — banner de título

    private const C_SECTION = 'FF2E75B6';      // azul medio — barras de sección

    private const C_COLHEAD = 'FFD6E4F0';      // azul claro — encabezados de tabla

    private const C_TOTAL_LIGHT = 'FFE2EFDA';  // verde claro — fila TOTAL (vehículos)

    private const C_KGBAND = 'FFE2F0D9';       // verde claro — banda en columnas KG

    private const C_TOTAL_DARK = 'FF385724';   // verde oscuro — fila TOTALES (pivots)

    private const C_STAT_PROM = 'FFFFF2CC';    // ámbar — fila PROMEDIO

    private const C_STAT_MAX = 'FFFCE4EC';     // rosa — fila MÁXIMO

    private const C_STAT_MIN = 'FFE8F5E9';     // verde — fila MÍNIMO

    private const C_ZEBRA = 'FFF5F5F5';        // gris — zebra striping

    private const C_WHITE = 'FFFFFFFF';

    private const C_INK = 'FF000000';

    private const C_GRID = 'FFD9D9D9';

    private const FMT_INT = '#,##0';

    private const FMT_DEC1 = '#,##0.0';

    private const FMT_PCT = '0.0%';

    private const FMT_DATE = 'dd/mm/yyyy';

    /** Primera columna de contenido (B): A queda como margen. */
    private const C0 = 2;

    public function __construct(protected array $reporte) {}

    public function download(string $filename): StreamedResponse
    {
        $writer = new Xlsx($this->build());

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Devuelve el .xlsx como bytes en memoria, para adjuntarlo a un email.
     */
    public function contents(): string
    {
        $writer = new Xlsx($this->build());

        ob_start();
        $writer->save('php://output');

        return (string) ob_get_clean();
    }

    /** Arma el spreadsheet completo (Resumen · Zona × Día · Detalle). */
    private function build(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getProperties()
            ->setTitle('Reporte de Pesajes')
            ->setCreator('Infinito Reciclaje');

        $this->buildResumen($spreadsheet->getActiveSheet());

        $this->buildZonaDia($spreadsheet->createSheet());

        $this->buildDetalle($spreadsheet->createSheet());

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    // ── Hoja Resumen ─────────────────────────────────────────────────────────

    private function buildResumen(Worksheet $sheet): void
    {
        $sheet->setTitle('Resumen');
        $sheet->setShowGridlines(false);
        $sheet->getColumnDimension('A')->setWidth(2.5);

        $tipos = $this->reporte['pivots']['tipos'];
        // Ancho de referencia para banner y barras: zona × tipo es el bloque más ancho.
        $lastCol = self::C0 + 4 + 2 * $tipos->count();

        $row = $this->buildTitulo($sheet, $lastCol);
        $row = $this->buildResumenGeneral($sheet, $row);
        $row = $this->buildPorVehiculo($sheet, $row);
        $row = $this->buildDiario($sheet, $row, $tipos);
        $this->buildZonaTipo($sheet, $row, $tipos);

        $sheet->getColumnDimension('B')->setWidth(26);
        foreach (range(self::C0 + 1, $lastCol) as $c) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setWidth(13);
        }
    }

    private function buildTitulo(Worksheet $sheet, int $lastCol): int
    {
        $desde = $this->reporte['desde']->format('d/m/Y');
        $hasta = $this->reporte['hasta']->format('d/m/Y');
        $muni = $this->reporte['config']?->municipalidad_nombre;

        $row = 2;

        $this->bar($sheet, $row, $lastCol, 'REPORTE DE PESAJES — DISPOSICIÓN FINAL', self::C_TITLE, 16);
        $row++;

        $subtitulo = trim(($muni ? $muni.'  ·  ' : '').'Período: '.$desde.' al '.$hasta);
        $this->bar($sheet, $row, $lastCol, $subtitulo, self::C_SECTION, 11);

        return $row + 2;
    }

    private function buildResumenGeneral(Worksheet $sheet, int $row): int
    {
        $kpis = $this->reporte['kpis'];
        $kgNetos = (int) $this->reporte['detalle']->sum('peso_neto_kg');
        $promedioKgDia = $kpis['dias_op'] > 0 ? (int) round($kgNetos / $kpis['dias_op']) : 0;

        $this->bar($sheet, $row, self::C0 + 4, 'RESUMEN GENERAL');
        $row++;

        $filas = [
            ['Total de registros con peso', $kpis['total'], self::FMT_INT],
            ['Total kilogramos netos', $kgNetos, self::FMT_INT],
            ['Total toneladas', $kpis['toneladas'], self::FMT_DEC1],
            ['Días con operación', $kpis['dias_op'], self::FMT_INT],
            ['Días del período', $kpis['dias_rango'], self::FMT_INT],
            ['Promedio diario (kg)', $promedioKgDia, self::FMT_INT],
            ['Promedio diario (ton)', $kpis['promedio_ton_dia'], self::FMT_DEC1],
            ['Promedio por viaje (kg)', $kpis['promedio_kg_viaje'], self::FMT_INT],
        ];

        foreach ($filas as [$label, $valor, $fmt]) {
            $this->text($sheet, self::C0, $row, $label, ['bold' => true]);
            $this->num($sheet, self::C0 + 1, $row, $valor, $fmt, ['bold' => true]);
            $row++;
        }

        return $row + 1;
    }

    private function buildPorVehiculo(Worksheet $sheet, int $row): int
    {
        $vehiculos = $this->reporte['vehiculos'];
        $granTotal = (int) $this->reporte['detalle']->sum('peso_neto_kg');

        $this->bar($sheet, $row, self::C0 + 4, 'DESGLOSE POR TIPO DE VEHÍCULO');
        $row++;

        $headRow = $row;
        $this->colHeaders($sheet, $row, self::C0, [
            'Tipo de Vehículo', 'Viajes', 'Kilos Netos', '% del Total', 'Promedio kg/viaje',
        ]);
        $row++;

        $totViajes = 0;
        $totKg = 0;
        foreach ($vehiculos as $v) {
            $kg = (int) round($v['toneladas'] * 1000);
            $this->text($sheet, self::C0, $row, $v['nombre'], ['bold' => true]);
            $this->num($sheet, self::C0 + 1, $row, $v['viajes'], self::FMT_INT);
            $this->num($sheet, self::C0 + 2, $row, $kg, self::FMT_INT);
            $this->num($sheet, self::C0 + 3, $row, $v['porcentaje'] / 100, self::FMT_PCT);
            $this->num($sheet, self::C0 + 4, $row, $v['kg_viaje'], self::FMT_INT);
            $totViajes += $v['viajes'];
            $totKg += $kg;
            $row++;
        }

        $this->text($sheet, self::C0, $row, 'TOTAL', ['bold' => true]);
        $this->num($sheet, self::C0 + 1, $row, $totViajes, self::FMT_INT, ['bold' => true]);
        $this->num($sheet, self::C0 + 2, $row, $totKg, self::FMT_INT, ['bold' => true]);
        $this->num($sheet, self::C0 + 3, $row, $granTotal > 0 ? $totKg / $granTotal : 0, self::FMT_PCT, ['bold' => true]);
        $this->num($sheet, self::C0 + 4, $row, $totViajes > 0 ? (int) round($totKg / $totViajes) : 0, self::FMT_INT, ['bold' => true]);
        $this->fillRow($sheet, $row, self::C0, self::C0 + 4, self::C_TOTAL_LIGHT);

        $this->border($sheet, $this->range(self::C0, $headRow, self::C0 + 4, $row));

        return $row + 2;
    }

    private function buildDiario(Worksheet $sheet, int $row, $tipos): int
    {
        $diario = $this->reporte['pivots']['diario'];
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
    private function escribirTiposDiario(Worksheet $sheet, int $row, array $porTipo, array $tipoIds): void
    {
        $col = self::C0 + 3;
        foreach ($tipoIds as $id) {
            $this->num($sheet, $col, $row, $porTipo[$id]['viajes'], self::FMT_INT, ['align' => Alignment::HORIZONTAL_CENTER]);
            $this->num($sheet, $col + 1, $row, $porTipo[$id]['kg'], self::FMT_INT, ['align' => Alignment::HORIZONTAL_CENTER, 'fill' => self::C_KGBAND]);
            $col += 2;
        }
    }

    /** Fila de estadística (TOTALES/PROMEDIO/MÁXIMO/MÍNIMO) con fondo uniforme. */
    private function escribirStatsDiario(Worksheet $sheet, int $row, string $label, array $struct, array $tipoIds, string $fill, string $fontColor = self::C_INK): int
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

    private function buildZonaTipo(Worksheet $sheet, int $row, $tipos): int
    {
        $zonaTipo = $this->reporte['pivots']['zonaTipo'];
        $tipoIds = $tipos->pluck('id')->all();
        $n = count($tipoIds);
        $lastCol = self::C0 + 3 + 2 * $n;   // Zona + 2n + (Total Viajes, Total KG, %)

        $this->bar($sheet, $row, $lastCol, 'KG TOTAL — ZONA Y TIPO DE VEHÍCULO', self::C_TITLE);
        $row++;

        $headers = ['Zona'];
        foreach ($tipos as $t) {
            $headers[] = $t['nombre']."\nViajes";
            $headers[] = $t['nombre']."\nKG";
        }
        $headers[] = "Total\nViajes";
        $headers[] = "Total\nKG";
        $headers[] = "% del\nTotal";
        $headRow = $row;
        $this->colHeaders($sheet, $row, self::C0, $headers);
        $row++;

        $z = 0;
        foreach ($zonaTipo['filas'] as $fila) {
            $this->escribirFilaZonaTipo($sheet, $row, $fila, $tipoIds, $z % 2 === 0 ? self::C_ZEBRA : self::C_WHITE);
            $row++;
            $z++;
        }

        $this->escribirFilaZonaTipo($sheet, $row, $zonaTipo['totales'], $tipoIds, self::C_TOTAL_DARK, 'TOTALES', self::C_WHITE);
        $row++;

        $this->border($sheet, $this->range(self::C0, $headRow, $lastCol, $row - 1));

        return $row + 1;
    }

    /** Una fila de la tabla zona × tipo. Banda verde en columnas KG salvo en TOTALES. */
    private function escribirFilaZonaTipo(Worksheet $sheet, int $row, array $fila, array $tipoIds, string $fill, ?string $label = null, string $fontColor = self::C_INK): void
    {
        $esTotal = $label !== null;
        $kgFill = $esTotal ? $fill : self::C_KGBAND;

        $this->text($sheet, self::C0, $row, $label ?? $fila['label'], ['bold' => $esTotal, 'color' => $fontColor]);

        $col = self::C0 + 1;
        foreach ($tipoIds as $id) {
            $this->num($sheet, $col, $row, $fila['tipos'][$id]['viajes'], self::FMT_INT, ['bold' => $esTotal, 'color' => $fontColor]);
            $this->num($sheet, $col + 1, $row, $fila['tipos'][$id]['kg'], self::FMT_INT, ['bold' => $esTotal, 'color' => $fontColor, 'fill' => $kgFill]);
            $col += 2;
        }

        $this->num($sheet, $col, $row, $fila['total_viajes'], self::FMT_INT, ['bold' => $esTotal, 'color' => $fontColor]);
        $this->num($sheet, $col + 1, $row, $fila['total_kg'], self::FMT_INT, ['bold' => $esTotal, 'color' => $fontColor]);
        $this->num($sheet, $col + 2, $row, $fila['porcentaje'], self::FMT_PCT, ['bold' => $esTotal, 'color' => $fontColor]);

        // Pinta el resto de la fila (label + columnas Viajes + totales) sin pisar la banda KG.
        $this->fillRow($sheet, $row, self::C0, self::C0, $fill);
        $col = self::C0 + 1;
        foreach ($tipoIds as $id) {
            $this->fillRow($sheet, $row, $col, $col, $fill);          // Viajes
            if ($esTotal) {
                $this->fillRow($sheet, $row, $col + 1, $col + 1, $fill); // KG en TOTALES
            }
            $col += 2;
        }
        $this->fillRow($sheet, $row, $col, $col + 2, $fill);          // totales + %
    }

    // ── Hoja Zona × Día ──────────────────────────────────────────────────────

    private function buildZonaDia(Worksheet $sheet): void
    {
        $sheet->setTitle('Zona × Día');
        $sheet->setShowGridlines(false);
        $sheet->getColumnDimension('A')->setWidth(2.5);

        $zonaDia = $this->reporte['pivots']['zonaDia'];
        $fechas = $zonaDia['fechas'];
        $claves = array_map(fn (Carbon $d) => $d->toDateString(), $fechas);
        $lastCol = self::C0 + count($fechas) + 1;   // Zona + N fechas + Total

        $row = 2;
        $this->bar($sheet, $row, $lastCol, 'KG NETOS POR ZONA Y POR DÍA', self::C_TITLE);
        $row += 2;

        // Encabezados: Zona · una columna por fecha · Total.
        $headRow = $row;
        $this->text($sheet, self::C0, $row, 'Zona', ['bold' => true, 'align' => Alignment::HORIZONTAL_CENTER, 'fill' => self::C_COLHEAD, 'wrap' => true]);
        $col = self::C0 + 1;
        foreach ($fechas as $f) {
            $this->date($sheet, $col, $row, $f, self::FMT_DATE, ['bold' => true, 'align' => Alignment::HORIZONTAL_CENTER, 'fill' => self::C_COLHEAD]);
            $col++;
        }
        $this->text($sheet, $col, $row, 'Total', ['bold' => true, 'align' => Alignment::HORIZONTAL_CENTER, 'fill' => self::C_COLHEAD]);
        $this->border($sheet, $this->range(self::C0, $row, $lastCol, $row));
        $row++;

        $z = 0;
        foreach ($zonaDia['filas'] as $fila) {
            $fill = $z % 2 === 0 ? self::C_ZEBRA : self::C_WHITE;
            $this->text($sheet, self::C0, $row, $fila['label'], ['fill' => $fill]);
            $col = self::C0 + 1;
            foreach ($claves as $k) {
                $this->num($sheet, $col, $row, $fila['dias'][$k], self::FMT_INT, ['fill' => $fill, 'align' => Alignment::HORIZONTAL_CENTER]);
                $col++;
            }
            $this->num($sheet, $col, $row, $fila['total'], self::FMT_INT, ['bold' => true, 'fill' => $fill, 'align' => Alignment::HORIZONTAL_CENTER]);
            $row++;
            $z++;
        }

        // Fila TOTALES por día.
        $this->text($sheet, self::C0, $row, 'TOTALES', ['bold' => true, 'color' => self::C_WHITE]);
        $col = self::C0 + 1;
        foreach ($claves as $k) {
            $this->num($sheet, $col, $row, $zonaDia['totales']['dias'][$k], self::FMT_INT, ['bold' => true, 'color' => self::C_WHITE, 'align' => Alignment::HORIZONTAL_CENTER]);
            $col++;
        }
        $this->num($sheet, $col, $row, $zonaDia['totales']['total'], self::FMT_INT, ['bold' => true, 'color' => self::C_WHITE, 'align' => Alignment::HORIZONTAL_CENTER]);
        $this->fillRow($sheet, $row, self::C0, $lastCol, self::C_TOTAL_DARK);

        $this->border($sheet, $this->range(self::C0, $headRow, $lastCol, $row));

        $sheet->getColumnDimension('B')->setWidth(24);
        foreach (range(self::C0 + 1, $lastCol) as $c) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setWidth(11);
        }
    }

    // ── Hoja Detalle ─────────────────────────────────────────────────────────

    private function buildDetalle(Worksheet $sheet): void
    {
        $sheet->setTitle('Detalle');

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
            $sheet->setCellValue([1, $row], $p->created_at->format('d/m/Y'));
            $sheet->setCellValue([2, $row], $p->created_at->format('H:i'));
            $sheet->setCellValue([3, $row], $p->vehiculo?->patente ?? '—');
            $sheet->setCellValue([4, $row], $p->vehiculo?->tipoVehiculo?->nombre ?? '—');
            $sheet->setCellValue([5, $row], $p->tipoServicio?->nombre ?? '—');
            $sheet->setCellValue([6, $row], $p->zona?->nombre ?? '—');
            $sheet->setCellValue([7, $row], $p->turno ?? '—');
            $sheet->setCellValue([8, $row], $p->operador?->name ?? '—');
            $this->num($sheet, 9, $row, (int) $p->peso_bruto_kg, self::FMT_INT);
            $this->num($sheet, 10, $row, (int) $p->peso_tara_kg, self::FMT_INT);
            $this->num($sheet, 11, $row, (int) $p->peso_neto_kg, self::FMT_INT);
            $sheet->setCellValue([12, $row], $p->estado);
            $sheet->setCellValue([13, $row], $p->editado ? 'Sí' : 'No');
            $sheet->setCellValue([14, $row], $p->alerta_peso ? 'Sí' : 'No');
            $row++;
        }

        foreach (range(1, count($headers)) as $c) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
        }
    }

    // ── Helpers de estilo ────────────────────────────────────────────────────

    /** Barra de sección: celdas combinadas, fondo de color, texto blanco bold centrado. */
    private function bar(Worksheet $sheet, int $row, int $colTo, string $text, string $argb = self::C_SECTION, int $size = 13): void
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

    /** Fila de encabezados de tabla: fondo azul claro, bold, centrado, con borde. */
    private function colHeaders(Worksheet $sheet, int $row, int $startCol, array $labels): void
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
    private function text(Worksheet $sheet, int $col, int $row, string $value, array $opts = []): void
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
    private function num(Worksheet $sheet, int $col, int $row, int|float $value, string $fmt, array $opts = []): void
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
    private function date(Worksheet $sheet, int $col, int $row, Carbon $value, string $fmt = self::FMT_DATE, array $opts = []): void
    {
        $coord = $this->cell($col, $row);
        $sheet->setCellValue($coord, ExcelDate::PHPToExcel($value));
        $sheet->getStyle($coord)->getNumberFormat()->setFormatCode($fmt);
        $this->aplicar($sheet, $coord, $opts + ['align' => Alignment::HORIZONTAL_CENTER]);
    }

    /** @param  array{bold?: bool, color?: string, align?: string, fill?: string, wrap?: bool}  $opts */
    private function aplicar(Worksheet $sheet, string $coord, array $opts): void
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

    private function fillRow(Worksheet $sheet, int $row, int $colFrom, int $colTo, string $argb): void
    {
        $sheet->getStyle($this->range($colFrom, $row, $colTo, $row))
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($argb);
    }

    private function border(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()->setARGB(self::C_GRID);
    }

    private function cell(int $col, int $row): string
    {
        return Coordinate::stringFromColumnIndex($col).$row;
    }

    private function range(int $c1, int $r1, int $c2, int $r2): string
    {
        return $this->cell($c1, $r1).':'.$this->cell($c2, $r2);
    }
}
