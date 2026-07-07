<?php

namespace App\Exports;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * @deprecated Generador de reporte v1. Se mantiene funcionando pero congelado; las
 * mejoras van en ReporteExcelExportV2 (formato del cliente: hojas por servicio y por
 * N° interno). Ver docs/reportes/plan-generador-v2.md.
 *
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
 * las claves: 'config' (ReporteConfiguracion|object|null), 'pivots' (salida de
 * ReporteService::pivotsParaExcel()), 'detalle' aplanado (salida de
 * ReporteService::detalleParaExcel(): filas escalares, no modelos Eloquent) y
 * 'kg_netos_total' (int). Esa forma serializable es la que se congela en el
 * snapshot del historial y la que se reusa al re-descargar.
 *
 * La paleta, los formatos y los helpers de estilo viven en ReporteExcelBase.
 */
class ReporteExcelExport extends ReporteExcelBase
{
    /** Arma el spreadsheet completo (Resumen · Zona × Día · Detalle). */
    protected function build(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getProperties()
            ->setTitle('Reporte de Pesajes')
            ->setCreator('Infinito Reciclaje');

        $this->buildResumen($spreadsheet->getActiveSheet());

        $this->buildZonaDia($spreadsheet->createSheet());

        $this->buildDetalleSheet($spreadsheet->createSheet(), 'Detalle');

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
        $row = $this->buildDiario($sheet, $row, $tipos, $this->reporte['pivots']['diario']);
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
        $kgNetos = (int) ($this->reporte['kg_netos_total'] ?? 0);
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
        $granTotal = (int) ($this->reporte['kg_netos_total'] ?? 0);

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

    private function buildZonaTipo(Worksheet $sheet, int $row, $tipos): int
    {
        $zonaTipo = $this->reporte['pivots']['zonaTipo'];
        $tipoIds = $tipos->pluck('id')->all();
        $n = count($tipoIds);
        $lastCol = self::C0 + 3 + 2 * $n;   // Zona + 2n + (Total Viajes, Total KG, %)

        $this->bar($sheet, $row, $lastCol, 'KG TOTAL — ZONA Y TIPO DE VEHÍCULO', self::C_TITLE);
        $row++;

        $headers = ['Zona y turno'];
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
        $this->text($sheet, self::C0, $row, 'Zona y turno', ['bold' => true, 'align' => Alignment::HORIZONTAL_CENTER, 'fill' => self::C_COLHEAD, 'wrap' => true]);
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
}
