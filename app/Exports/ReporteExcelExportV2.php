<?php

namespace App\Exports;

use App\Support\ReporteSecciones;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export v2 del reporte de pesajes — replica el formato del archivo de referencia del
 * cliente (docs/reportes/reportes-03-07-2026/Reporte mayo por tipo de servicio.xlsx):
 *
 *  - Hoja "Resumen":  RESUMEN GENERAL (KPIs) · RESUMEN POR DÍA · RESUMEN POR SERVICIO Y
 *                     TIPO DE VEHÍCULO (kg) · RESUMEN DE VIAJES POR SERVICIOS.
 *  - Hoja "Por vehículo- N° Interno":  RESUMEN POR TIPO DE VEHÍCULO · DESGLOSE DIARIO ·
 *                     CANTIDAD DE VIAJES POR N° INTERNO (matriz interno × día).
 *  - Una hoja por servicio + hoja "Base de datos"  → se agregan en la fase 2b.
 *
 * $reporte recibe, además de la salida de ReporteService::generar():
 *  - 'config'          ReporteConfiguracion|object|null (nombre del municipio)
 *  - 'kg_netos_total'  int
 *  - 'datosV2'         salida de ReporteService::datosExcelV2()
 *  - 'detalle'         detalle aplanado (para la hoja Base de datos, fase 2b)
 *
 * Paleta, formatos, helpers de estilo y el bloque diario viven en ReporteExcelBase.
 */
class ReporteExcelExportV2 extends ReporteExcelBase
{
    protected function build(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getProperties()
            ->setTitle('Reporte de Pesajes por Tipo de Servicio')
            ->setCreator('Infinito Reciclaje');

        // Hojas habilitadas por la configuración de secciones (congelada en el
        // snapshot al generar). Sin la clave → todas; sanitizar garantiza al
        // menos una hoja (un workbook no puede quedar vacío).
        $hojas = ReporteSecciones::sanitizarExcel($this->reporte['secciones']['excel'] ?? null);
        // La primera hoja reutiliza la activa del workbook; las demás se crean.
        $primera = true;
        $sheet = function () use ($spreadsheet, &$primera): Worksheet {
            if ($primera) {
                $primera = false;

                return $spreadsheet->getActiveSheet();
            }

            return $spreadsheet->createSheet();
        };

        if (in_array('resumen', $hojas, true)) {
            $this->buildResumen($sheet());
        }

        if (in_array('por_interno', $hojas, true)) {
            $this->buildPorInterno($sheet());
        }

        // Una hoja por servicio (ordenados por kg desc en datosExcelV2).
        if (in_array('por_servicio', $hojas, true)) {
            foreach ($this->reporte['datosV2']['servicios'] as $servicio) {
                $this->buildServicio($sheet(), $servicio);
            }
        }

        if (in_array('base_datos', $hojas, true)) {
            $this->buildDetalleSheet($sheet(), 'Base de datos');
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /** Título de hoja saneado (Excel: máx 31 caracteres, sin []:*?/\). */
    private function tituloHoja(string $nombre): string
    {
        $limpio = preg_replace('/[\/\\\\?*\[\]:]/', ' ', $nombre);

        return mb_substr(trim((string) $limpio), 0, 31);
    }

    /** @return Collection<int, array{id: int, nombre: string}> */
    private function tipos(): Collection
    {
        return $this->reporte['datosV2']['tipos'];
    }

    /** Banner de título (2 filas): título + subtítulo con municipio y período. */
    private function titulo(Worksheet $sheet, int $lastCol, string $titulo): int
    {
        $desde = $this->reporte['desde']->format('d/m/Y');
        $hasta = $this->reporte['hasta']->format('d/m/Y');
        $muni = $this->reporte['config']?->municipalidad_nombre;

        $row = 2;
        $this->bar($sheet, $row, $lastCol, $titulo, self::C_TITLE, 16);
        $row++;

        $subtitulo = trim(($muni ? $muni.'  ·  ' : '').'Período: '.$desde.' al '.$hasta);
        $this->bar($sheet, $row, $lastCol, $subtitulo, self::C_SECTION, 11);

        return $row + 2;
    }

    // ── Hoja Resumen ─────────────────────────────────────────────────────────

    private function buildResumen(Worksheet $sheet): void
    {
        $sheet->setTitle('Resumen');
        $sheet->setShowGridlines(false);
        $sheet->getColumnDimension('A')->setWidth(2.5);

        $n = $this->tipos()->count();
        // Bloque más ancho: el cruce servicio × tipo (Servicio + n tipos + Total).
        $lastCol = max(self::C0 + 1 + $n, self::C0 + 3);

        $row = $this->titulo($sheet, $lastCol, 'RESUMEN DE PESAJES — DISPOSICIÓN FINAL');
        $row = $this->buildResumenGeneral($sheet, $row);
        $row = $this->buildServicioCruce($sheet, $row, 'RESUMEN POR SERVICIO Y TIPO DE VEHÍCULO', 'kg');
        $row = $this->buildServicioCruce($sheet, $row, 'RESUMEN DE VIAJES POR SERVICIOS', 'viajes');
        $this->buildResumenPorDia($sheet, $row);

        $sheet->getColumnDimension('B')->setWidth(28);
        foreach (range(self::C0 + 1, $lastCol) as $c) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setWidth(15);
        }
    }

    private function buildResumenGeneral(Worksheet $sheet, int $row): int
    {
        $kpis = $this->reporte['kpis'];
        $kgNetos = (int) ($this->reporte['kg_netos_total'] ?? 0);
        $promedioKgDia = $kpis['dias_op'] > 0 ? (int) round($kgNetos / $kpis['dias_op']) : 0;

        $this->bar($sheet, $row, self::C0 + 3, 'RESUMEN GENERAL');
        $row++;

        $filas = [
            ['Total de registros con peso', $kpis['total']],
            ['Total kilogramos netos', $kgNetos],
            ['Días con operación', $kpis['dias_op']],
            ['Promedio diario (kg)', $promedioKgDia],
            ['Promedio por viaje (kg)', $kpis['promedio_kg_viaje']],
        ];

        $headRow = $row;
        foreach ($filas as [$label, $valor]) {
            $this->text($sheet, self::C0, $row, $label, ['bold' => true]);
            $this->num($sheet, self::C0 + 1, $row, $valor, self::FMT_INT, ['bold' => true, 'align' => Alignment::HORIZONTAL_CENTER]);
            $row++;
        }
        $this->border($sheet, $this->range(self::C0, $headRow, self::C0 + 1, $row - 1));

        return $row + 1;
    }

    /**
     * Cruce servicio × tipo de vehículo. Con $metric = 'kg' escribe kilos por tipo y
     * Total kg; con 'viajes' escribe viajes por tipo y Total viajes. Filas ordenadas
     * por kg desc (según servicioTipoVehiculo()), más la fila TOTAL.
     */
    private function buildServicioCruce(Worksheet $sheet, int $row, string $titulo, string $metric): int
    {
        $tipos = $this->tipos();
        $cruce = $this->reporte['datosV2']['servicioTipoVehiculo'];
        $lastCol = self::C0 + 1 + $tipos->count();   // Servicio + n tipos + Total

        $this->bar($sheet, $row, $lastCol, $titulo);
        $row++;

        $headers = ['Servicio'];
        foreach ($tipos as $t) {
            $headers[] = $t['nombre'];
        }
        $headers[] = $metric === 'kg' ? 'Total kg' : 'Total viajes';
        $headRow = $row;
        $this->colHeaders($sheet, $row, self::C0, $headers);
        $row++;

        $z = 0;
        foreach ($cruce['filas'] as $fila) {
            $this->escribirCruceFila($sheet, $row, $fila, $metric, $z % 2 === 0 ? self::C_ZEBRA : self::C_WHITE, false);
            $row++;
            $z++;
        }
        $this->escribirCruceFila($sheet, $row, $cruce['totales'], $metric, self::C_TOTAL_DARK, true);
        $row++;

        $this->border($sheet, $this->range(self::C0, $headRow, $lastCol, $row - 1));

        return $row + 1;
    }

    private function escribirCruceFila(Worksheet $sheet, int $row, array $fila, string $metric, string $fill, bool $esTotal): void
    {
        $fontColor = $esTotal ? self::C_WHITE : self::C_INK;

        $this->text($sheet, self::C0, $row, $fila['nombre'], ['bold' => $esTotal, 'color' => $fontColor]);

        $col = self::C0 + 1;
        foreach ($this->tipos() as $t) {
            $this->num($sheet, $col, $row, $fila['tipos'][$t['id']][$metric], self::FMT_INT, ['bold' => $esTotal, 'color' => $fontColor, 'align' => Alignment::HORIZONTAL_CENTER]);
            $col++;
        }

        $total = $metric === 'kg' ? $fila['total_kg'] : $fila['total_viajes'];
        $this->num($sheet, $col, $row, $total, self::FMT_INT, ['bold' => true, 'color' => $fontColor, 'align' => Alignment::HORIZONTAL_CENTER]);

        $this->fillRow($sheet, $row, self::C0, $col, $fill);
    }

    private function buildResumenPorDia(Worksheet $sheet, int $row): int
    {
        $porDia = $this->reporte['datosV2']['resumenPorDia'];
        $lastCol = self::C0 + 3;   // Fecha · Día · Kg · Viajes

        $this->bar($sheet, $row, $lastCol, 'RESUMEN POR DÍA');
        $row++;

        $headRow = $row;
        $this->colHeaders($sheet, $row, self::C0, ['Fecha', 'Día', 'Kg ingresados', 'Viajes']);
        $row++;

        $z = 0;
        foreach ($porDia['filas'] as $fila) {
            $fill = $z % 2 === 0 ? self::C_ZEBRA : self::C_WHITE;
            $this->date($sheet, self::C0, $row, $fila['fecha'], self::FMT_DATE, ['fill' => $fill]);
            $this->text($sheet, self::C0 + 1, $row, ucfirst($fila['dia']), ['fill' => $fill]);
            $this->num($sheet, self::C0 + 2, $row, $fila['kg'], self::FMT_INT, ['fill' => $fill, 'align' => Alignment::HORIZONTAL_CENTER]);
            $this->num($sheet, self::C0 + 3, $row, $fila['viajes'], self::FMT_INT, ['fill' => $fill, 'align' => Alignment::HORIZONTAL_CENTER]);
            $row++;
            $z++;
        }

        $this->text($sheet, self::C0, $row, 'TOTAL', ['bold' => true, 'color' => self::C_WHITE]);
        $this->num($sheet, self::C0 + 2, $row, $porDia['total_kg'], self::FMT_INT, ['bold' => true, 'color' => self::C_WHITE, 'align' => Alignment::HORIZONTAL_CENTER]);
        $this->num($sheet, self::C0 + 3, $row, $porDia['total_viajes'], self::FMT_INT, ['bold' => true, 'color' => self::C_WHITE, 'align' => Alignment::HORIZONTAL_CENTER]);
        $this->fillRow($sheet, $row, self::C0, $lastCol, self::C_TOTAL_DARK);

        $this->border($sheet, $this->range(self::C0, $headRow, $lastCol, $row));

        return $row + 1;
    }

    // ── Hoja Por vehículo- N° Interno ────────────────────────────────────────

    private function buildPorInterno(Worksheet $sheet): void
    {
        $sheet->setTitle('Por vehículo- N° Interno');
        $sheet->setShowGridlines(false);
        $sheet->getColumnDimension('A')->setWidth(2.5);

        $fechas = $this->reporte['datosV2']['fechas'];
        // Bloque más ancho: la matriz interno × día (Interno + Tipo + una col por día).
        $lastCol = self::C0 + 1 + count($fechas);

        $row = $this->titulo($sheet, $lastCol, 'POR TIPO DE VEHÍCULO — INGRESOS AL PREDIO');
        $row = $this->buildResumenPorTipo($sheet, $row);
        $row = $this->buildDiario($sheet, $row, $this->tipos(), $this->reporte['datosV2']['diario']);
        $this->buildViajesPorInterno($sheet, $row);

        $sheet->getColumnDimension('B')->setWidth(16);
        $sheet->getColumnDimension('C')->setWidth(16);
        foreach (range(self::C0 + 2, $lastCol) as $c) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setWidth(6);
        }
    }

    /** RESUMEN POR TIPO DE VEHÍCULO: Tipo · Viajes · Kilos Netos · Promedio kg/viaje. */
    private function buildResumenPorTipo(Worksheet $sheet, int $row): int
    {
        $vehiculos = $this->reporte['vehiculos'];

        $this->bar($sheet, $row, self::C0 + 3, 'RESUMEN POR TIPO DE VEHÍCULO');
        $row++;

        $headRow = $row;
        $this->colHeaders($sheet, $row, self::C0, ['Tipo de Vehículo', 'Viajes', 'Kilos Netos', 'Promedio kg/viaje']);
        $row++;

        $totViajes = 0;
        $totKg = 0;
        foreach ($vehiculos as $v) {
            $kg = (int) round($v['toneladas'] * 1000);
            $this->text($sheet, self::C0, $row, $v['nombre'], ['bold' => true]);
            $this->num($sheet, self::C0 + 1, $row, $v['viajes'], self::FMT_INT, ['align' => Alignment::HORIZONTAL_CENTER]);
            $this->num($sheet, self::C0 + 2, $row, $kg, self::FMT_INT, ['align' => Alignment::HORIZONTAL_CENTER]);
            $this->num($sheet, self::C0 + 3, $row, $v['kg_viaje'], self::FMT_INT, ['align' => Alignment::HORIZONTAL_CENTER]);
            $totViajes += $v['viajes'];
            $totKg += $kg;
            $row++;
        }

        $this->text($sheet, self::C0, $row, 'TOTAL', ['bold' => true]);
        $this->num($sheet, self::C0 + 1, $row, $totViajes, self::FMT_INT, ['bold' => true, 'align' => Alignment::HORIZONTAL_CENTER]);
        $this->num($sheet, self::C0 + 2, $row, $totKg, self::FMT_INT, ['bold' => true, 'align' => Alignment::HORIZONTAL_CENTER]);
        $this->num($sheet, self::C0 + 3, $row, $totViajes > 0 ? (int) round($totKg / $totViajes) : 0, self::FMT_INT, ['bold' => true, 'align' => Alignment::HORIZONTAL_CENTER]);
        $this->fillRow($sheet, $row, self::C0, self::C0 + 3, self::C_TOTAL_LIGHT);

        $this->border($sheet, $this->range(self::C0, $headRow, self::C0 + 3, $row));

        return $row + 2;
    }

    /** CANTIDAD DE VIAJES POR N° INTERNO: una fila por vehículo, una columna por día. */
    private function buildViajesPorInterno(Worksheet $sheet, int $row): int
    {
        $fechas = $this->reporte['datosV2']['fechas'];
        $filas = $this->reporte['datosV2']['porNumeroInterno']['filas'];
        $lastCol = self::C0 + 1 + count($fechas);

        $this->bar($sheet, $row, $lastCol, 'CANTIDAD DE VIAJES POR N° INTERNO', self::C_TITLE);
        $row++;

        // Encabezados: Interno · Tipo · una columna por día (número de día del mes).
        $headRow = $row;
        $this->text($sheet, self::C0, $row, 'Interno', ['bold' => true, 'align' => Alignment::HORIZONTAL_CENTER, 'fill' => self::C_COLHEAD]);
        $this->text($sheet, self::C0 + 1, $row, 'Tipo', ['bold' => true, 'align' => Alignment::HORIZONTAL_CENTER, 'fill' => self::C_COLHEAD]);
        $col = self::C0 + 2;
        foreach ($fechas as $f) {
            $this->num($sheet, $col, $row, (int) $f->format('j'), self::FMT_INT, ['bold' => true, 'align' => Alignment::HORIZONTAL_CENTER, 'fill' => self::C_COLHEAD]);
            $col++;
        }
        $this->border($sheet, $this->range(self::C0, $row, $lastCol, $row));
        $sheet->getRowDimension($row)->setRowHeight(20);
        $row++;

        $claves = array_map(fn ($f) => $f->toDateString(), $fechas);
        $z = 0;
        foreach ($filas as $fila) {
            $fill = $z % 2 === 0 ? self::C_ZEBRA : self::C_WHITE;
            $this->text($sheet, self::C0, $row, $fila['interno'], ['fill' => $fill]);
            $this->text($sheet, self::C0 + 1, $row, $fila['tipo'], ['fill' => $fill]);
            $col = self::C0 + 2;
            foreach ($claves as $k) {
                $v = $fila['dias'][$k] ?? 0;
                // Celda vacía cuando no hubo viajes: la matriz queda más legible.
                if ($v > 0) {
                    $this->num($sheet, $col, $row, $v, self::FMT_INT, ['fill' => $fill, 'align' => Alignment::HORIZONTAL_CENTER]);
                } else {
                    $this->text($sheet, $col, $row, '', ['fill' => $fill]);
                }
                $col++;
            }
            $row++;
            $z++;
        }

        $this->border($sheet, $this->range(self::C0, $headRow, $lastCol, $row - 1));

        return $row + 1;
    }

    // ── Hoja por servicio ────────────────────────────────────────────────────

    /**
     * Una hoja por servicio: RESUMEN DEL SERVICIO · DESGLOSE POR ZONA · KG NETOS POR
     * ZONA Y POR DÍA. $servicio viene de ReporteService::datosExcelV2()['servicios'].
     */
    private function buildServicio(Worksheet $sheet, array $servicio): void
    {
        $sheet->setTitle($this->tituloHoja($servicio['nombre']));
        $sheet->setShowGridlines(false);
        $sheet->getColumnDimension('A')->setWidth(2.5);

        $fechas = $servicio['zonaDia']['fechas'];
        $lastCol = max(self::C0 + count($fechas) + 1, self::C0 + 3);

        $row = $this->titulo($sheet, $lastCol, 'SERVICIO — '.mb_strtoupper($servicio['nombre']));
        $row = $this->buildResumenServicio($sheet, $row, $servicio);
        $row = $this->buildDesgloseZona($sheet, $row, $servicio);
        $this->buildZonaDiaServicio($sheet, $row, $servicio['zonaDia']);

        $sheet->getColumnDimension('B')->setWidth(26);
        foreach (range(self::C0 + 1, $lastCol) as $c) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setWidth(12);
        }
    }

    private function buildResumenServicio(Worksheet $sheet, int $row, array $servicio): int
    {
        $this->bar($sheet, $row, self::C0 + 1, 'RESUMEN DEL SERVICIO');
        $row++;

        $headRow = $row;
        $this->text($sheet, self::C0, $row, 'Cantidad de viajes', ['bold' => true]);
        $this->num($sheet, self::C0 + 1, $row, $servicio['viajes'], self::FMT_INT, ['bold' => true, 'align' => Alignment::HORIZONTAL_CENTER]);
        $row++;
        $this->text($sheet, self::C0, $row, 'Total de kilogramos', ['bold' => true]);
        $this->num($sheet, self::C0 + 1, $row, $servicio['kg'], self::FMT_INT, ['bold' => true, 'align' => Alignment::HORIZONTAL_CENTER]);
        $this->border($sheet, $this->range(self::C0, $headRow, self::C0 + 1, $row));

        return $row + 2;
    }

    /** DESGLOSE POR ZONA: Zona · Viajes · Kilos Netos · % del servicio + TOTAL. */
    private function buildDesgloseZona(Worksheet $sheet, int $row, array $servicio): int
    {
        $lastCol = self::C0 + 3;

        $this->bar($sheet, $row, $lastCol, 'DESGLOSE POR ZONA');
        $row++;

        $headRow = $row;
        $this->colHeaders($sheet, $row, self::C0, ['Zona', 'Viajes', 'Kilos Netos', '% del servicio']);
        $row++;

        $z = 0;
        foreach ($servicio['zonas'] as $zona) {
            $fill = $z % 2 === 0 ? self::C_ZEBRA : self::C_WHITE;
            $this->text($sheet, self::C0, $row, $zona['label'], ['fill' => $fill]);
            $this->num($sheet, self::C0 + 1, $row, $zona['viajes'], self::FMT_INT, ['fill' => $fill, 'align' => Alignment::HORIZONTAL_CENTER]);
            $this->num($sheet, self::C0 + 2, $row, $zona['kg'], self::FMT_INT, ['fill' => $fill, 'align' => Alignment::HORIZONTAL_CENTER]);
            $this->num($sheet, self::C0 + 3, $row, $zona['porcentaje'], self::FMT_PCT, ['fill' => $fill, 'align' => Alignment::HORIZONTAL_CENTER]);
            $row++;
            $z++;
        }

        $this->text($sheet, self::C0, $row, 'TOTAL', ['bold' => true, 'color' => self::C_WHITE]);
        $this->num($sheet, self::C0 + 1, $row, $servicio['viajes'], self::FMT_INT, ['bold' => true, 'color' => self::C_WHITE, 'align' => Alignment::HORIZONTAL_CENTER]);
        $this->num($sheet, self::C0 + 2, $row, $servicio['kg'], self::FMT_INT, ['bold' => true, 'color' => self::C_WHITE, 'align' => Alignment::HORIZONTAL_CENTER]);
        $this->num($sheet, self::C0 + 3, $row, $servicio['kg'] > 0 ? 1.0 : 0.0, self::FMT_PCT, ['bold' => true, 'color' => self::C_WHITE, 'align' => Alignment::HORIZONTAL_CENTER]);
        $this->fillRow($sheet, $row, self::C0, $lastCol, self::C_TOTAL_DARK);

        $this->border($sheet, $this->range(self::C0, $headRow, $lastCol, $row));

        return $row + 2;
    }

    /** KG NETOS POR ZONA Y POR DÍA acotado al servicio (matriz zona × día). */
    private function buildZonaDiaServicio(Worksheet $sheet, int $row, array $zonaDia): int
    {
        $fechas = $zonaDia['fechas'];
        $claves = array_map(fn ($d) => $d->toDateString(), $fechas);
        $lastCol = self::C0 + count($fechas) + 1;   // Zona + N fechas + Total

        $this->bar($sheet, $row, $lastCol, 'KG NETOS POR ZONA Y POR DÍA', self::C_TITLE);
        $row++;

        $headRow = $row;
        $this->text($sheet, self::C0, $row, 'Zona', ['bold' => true, 'align' => Alignment::HORIZONTAL_CENTER, 'fill' => self::C_COLHEAD]);
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

        $this->text($sheet, self::C0, $row, 'TOTALES', ['bold' => true, 'color' => self::C_WHITE]);
        $col = self::C0 + 1;
        foreach ($claves as $k) {
            $this->num($sheet, $col, $row, $zonaDia['totales']['dias'][$k], self::FMT_INT, ['bold' => true, 'color' => self::C_WHITE, 'align' => Alignment::HORIZONTAL_CENTER]);
            $col++;
        }
        $this->num($sheet, $col, $row, $zonaDia['totales']['total'], self::FMT_INT, ['bold' => true, 'color' => self::C_WHITE, 'align' => Alignment::HORIZONTAL_CENTER]);
        $this->fillRow($sheet, $row, self::C0, $lastCol, self::C_TOTAL_DARK);

        $this->border($sheet, $this->range(self::C0, $headRow, $lastCol, $row));

        return $row + 1;
    }
}
