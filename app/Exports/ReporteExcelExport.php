<?php

namespace App\Exports;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReporteExcelExport
{
    public function __construct(protected array $reporte) {}

    public function download(string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setTitle('Reporte de Pesajes')
            ->setCreator('Infinito Reciclaje');

        $this->buildResumen($spreadsheet->getActiveSheet());

        $detalle = $spreadsheet->createSheet();
        $detalle->setTitle('Detalle');
        $this->buildDetalle($detalle);

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function buildResumen(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): void
    {
        $sheet->setTitle('Resumen');

        $desde   = $this->reporte['desde']->format('d/m/Y');
        $hasta   = $this->reporte['hasta']->format('d/m/Y');
        $kpis    = $this->reporte['kpis'];
        $zonas   = $this->reporte['zonas'];
        $vehs    = $this->reporte['vehiculos'];
        $evol    = $this->reporte['evolucion'];

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF18181B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $subHeaderStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF4F4F5']],
        ];
        $totalStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF4F4F5']],
        ];

        $row = 1;

        // Título
        $sheet->setCellValue('A' . $row, 'Reporte de Pesajes — ' . $desde . ' al ' . $hasta);
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray($headerStyle);
        $sheet->getRowDimension($row)->setRowHeight(22);
        $row += 2;

        // KPIs
        $sheet->setCellValue('A' . $row, 'Resumen del período');
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray($subHeaderStyle);
        $row++;

        $kpiData = [
            ['Total de viajes', number_format($kpis['total'])],
            ['Toneladas netas', number_format($kpis['toneladas'], 2)],
            ['Días operativos', $kpis['dias_op'] . ' de ' . $kpis['dias_rango']],
            ['Promedio ton/día', number_format($kpis['promedio_ton_dia'], 2)],
            ['Promedio kg/viaje', number_format($kpis['promedio_kg_viaje'])],
        ];
        foreach ($kpiData as [$label, $valor]) {
            $sheet->setCellValue('A' . $row, $label);
            $sheet->setCellValue('B' . $row, $valor);
            $row++;
        }

        $row++;

        // Tabla zonas
        if ($zonas->isNotEmpty()) {
            $sheet->setCellValue('A' . $row, 'Por zona y turno');
            $sheet->mergeCells('A' . $row . ':G' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray($subHeaderStyle);
            $row++;

            $headers = ['Zona', 'Turno', 'Viajes', 'Toneladas', 'kg/viaje', '% Total', 'kg/ha'];
            foreach ($headers as $i => $h) {
                $sheet->setCellValue(chr(65 + $i) . $row, $h);
            }
            $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray($headerStyle);
            $row++;

            $totViajes = 0; $totTons = 0;
            foreach ($zonas as $z) {
                $sheet->setCellValue('A' . $row, $z['nombre']);
                $sheet->setCellValue('B' . $row, $z['turno'] ?? '—');
                $sheet->setCellValue('C' . $row, $z['viajes']);
                $sheet->setCellValue('D' . $row, $z['toneladas']);
                $sheet->setCellValue('E' . $row, number_format($z['kg_viaje']));
                $sheet->setCellValue('F' . $row, $z['porcentaje'] . '%');
                $sheet->setCellValue('G' . $row, $z['kg_ha'] !== null ? number_format($z['kg_ha'], 1) : 'S/D');
                $totViajes += $z['viajes'];
                $totTons   += $z['toneladas'];
                $row++;
            }
            $sheet->setCellValue('A' . $row, 'TOTAL');
            $sheet->setCellValue('C' . $row, $totViajes);
            $sheet->setCellValue('D' . $row, round($totTons, 2));
            $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray($totalStyle);
            $row += 2;
        }

        // Tabla vehículos
        if ($vehs->isNotEmpty()) {
            $sheet->setCellValue('A' . $row, 'Por tipo de vehículo');
            $sheet->mergeCells('A' . $row . ':E' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray($subHeaderStyle);
            $row++;

            $headers = ['Tipo', 'Viajes', 'Toneladas', 'kg/viaje', '% Total'];
            foreach ($headers as $i => $h) {
                $sheet->setCellValue(chr(65 + $i) . $row, $h);
            }
            $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($headerStyle);
            $row++;

            $totViajes = 0; $totTons = 0;
            foreach ($vehs as $v) {
                $sheet->setCellValue('A' . $row, $v['nombre']);
                $sheet->setCellValue('B' . $row, $v['viajes']);
                $sheet->setCellValue('C' . $row, $v['toneladas']);
                $sheet->setCellValue('D' . $row, number_format($v['kg_viaje']));
                $sheet->setCellValue('E' . $row, $v['porcentaje'] . '%');
                $totViajes += $v['viajes'];
                $totTons   += $v['toneladas'];
                $row++;
            }
            $sheet->setCellValue('A' . $row, 'TOTAL');
            $sheet->setCellValue('B' . $row, $totViajes);
            $sheet->setCellValue('C' . $row, round($totTons, 2));
            $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($totalStyle);
            $row += 2;
        }

        // Evolución diaria
        if (!empty($evol['datos'])) {
            $sheet->setCellValue('A' . $row, 'Evolución diaria');
            $sheet->mergeCells('A' . $row . ':C' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray($subHeaderStyle);
            $row++;

            $headers = ['Fecha', 'Viajes', 'Toneladas'];
            foreach ($headers as $i => $h) {
                $sheet->setCellValue(chr(65 + $i) . $row, $h);
            }
            $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray($headerStyle);
            $row++;

            foreach ($evol['datos'] as $d) {
                $sheet->setCellValue('A' . $row, $d['fecha']);
                $sheet->setCellValue('B' . $row, $d['viajes']);
                $sheet->setCellValue('C' . $row, $d['toneladas']);
                $row++;
            }
        }

        // Ancho de columnas
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    private function buildDetalle(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): void
    {
        $headers = [
            'Fecha', 'Hora', 'Patente', 'Tipo Vehículo', 'Tipo Servicio',
            'Zona', 'Turno', 'Operador', 'Peso Bruto (kg)', 'Tara (kg)',
            'Peso Neto (kg)', 'Estado', 'Editado', 'Alerta Peso',
        ];

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF18181B']],
        ];

        foreach ($headers as $i => $h) {
            $sheet->setCellValue(chr(65 + $i) . '1', $h);
        }
        $sheet->getStyle('A1:' . chr(65 + count($headers) - 1) . '1')->applyFromArray($headerStyle);

        $row = 2;
        foreach ($this->reporte['detalle'] as $p) {
            $sheet->setCellValue('A' . $row, $p->created_at->format('d/m/Y'));
            $sheet->setCellValue('B' . $row, $p->created_at->format('H:i'));
            $sheet->setCellValue('C' . $row, $p->vehiculo?->patente ?? '—');
            $sheet->setCellValue('D' . $row, $p->vehiculo?->tipoVehiculo?->nombre ?? '—');
            $sheet->setCellValue('E' . $row, $p->tipoServicio?->nombre ?? '—');
            $sheet->setCellValue('F' . $row, $p->zona?->nombre ?? '—');
            $sheet->setCellValue('G' . $row, $p->turno ?? '—');
            $sheet->setCellValue('H' . $row, $p->operador?->name ?? '—');
            $sheet->setCellValue('I' . $row, $p->peso_bruto_kg);
            $sheet->setCellValue('J' . $row, $p->peso_tara_kg);
            $sheet->setCellValue('K' . $row, $p->peso_neto_kg);
            $sheet->setCellValue('L' . $row, $p->estado);
            $sheet->setCellValue('M' . $row, $p->editado ? 'Sí' : 'No');
            $sheet->setCellValue('N' . $row, $p->alerta_peso ? 'Sí' : 'No');
            $row++;
        }

        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}
