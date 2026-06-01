<?php

namespace App\Services;

class SvgChartService
{
    /**
     * Gráfico de barras verticales para evolución diaria.
     * $datos: [['fecha' => 'd/m', 'toneladas' => float, 'viajes' => int], ...]
     */
    public function barVertical(array $datos, int $w = 720, int $h = 240): string
    {
        if (empty($datos)) {
            return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $h . '"></svg>';
        }

        $padTop = 28; $padBottom = 36; $padLeft = 48; $padRight = 16;
        $chartW = $w - $padLeft - $padRight;
        $chartH = $h - $padTop - $padBottom;

        $values  = array_column($datos, 'toneladas');
        $maxVal  = max($values) ?: 1;
        $avg     = array_sum($values) / max(count($values), 1);
        $count   = count($datos);
        $barW    = max(4, ($chartW / $count) * 0.72);
        $gap     = ($chartW / $count) * 0.28;
        $color   = '#0ea5e9';
        $colorLow = '#ef4444';

        // Y-axis grid lines (4 lines)
        $gridLines = 4;
        $gridStep  = $chartH / $gridLines;
        $valStep   = $maxVal / $gridLines;

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $h . '" font-family="DejaVu Sans, Arial, sans-serif">';

        // Background
        $svg .= '<rect width="' . $w . '" height="' . $h . '" fill="#ffffff"/>';

        // Grid lines
        for ($i = 0; $i <= $gridLines; $i++) {
            $y = $padTop + $chartH - $i * $gridStep;
            $label = number_format(round($valStep * $i, 1));
            $svg .= '<line x1="' . $padLeft . '" y1="' . $y . '" x2="' . ($w - $padRight) . '" y2="' . $y . '" stroke="#e5e7eb" stroke-width="1"/>';
            $svg .= '<text x="' . ($padLeft - 6) . '" y="' . ($y + 4) . '" text-anchor="end" font-size="9" fill="#9ca3af">' . $label . '</text>';
        }

        // Bars + labels
        foreach ($datos as $i => $d) {
            $barH  = $chartH * ($d['toneladas'] / $maxVal);
            $x     = $padLeft + $i * ($chartW / $count) + $gap / 2;
            $y     = $padTop + $chartH - $barH;
            $fill  = $d['toneladas'] < ($avg * 0.3) ? $colorLow : $color;

            $svg .= '<rect x="' . round($x, 1) . '" y="' . round($y, 1) . '" width="' . round($barW, 1) . '" height="' . round($barH, 1) . '" fill="' . $fill . '" rx="2"/>';

            // Value on top (only if bar is tall enough)
            if ($barH > 16) {
                $svg .= '<text x="' . round($x + $barW / 2, 1) . '" y="' . round($y - 3, 1) . '" text-anchor="middle" font-size="8" fill="#374151">' . number_format($d['toneladas'], 1) . '</text>';
            }

            // X-axis label (every N bars to avoid crowding)
            $showEvery = max(1, (int) ceil($count / 20));
            if ($i % $showEvery === 0) {
                $svg .= '<text x="' . round($x + $barW / 2, 1) . '" y="' . ($padTop + $chartH + 14) . '" text-anchor="middle" font-size="8" fill="#6b7280">' . htmlspecialchars($d['fecha']) . '</text>';
            }
        }

        // Average line
        $avgY = $padTop + $chartH - ($chartH * ($avg / $maxVal));
        $svg .= '<line x1="' . $padLeft . '" y1="' . round($avgY, 1) . '" x2="' . ($w - $padRight) . '" y2="' . round($avgY, 1) . '" stroke="#6b7280" stroke-width="1.5" stroke-dasharray="5,4"/>';
        $svg .= '<text x="' . ($w - $padRight + 2) . '" y="' . round($avgY + 4, 1) . '" font-size="8" fill="#6b7280">Prom</text>';

        // Axes
        $svg .= '<line x1="' . $padLeft . '" y1="' . $padTop . '" x2="' . $padLeft . '" y2="' . ($padTop + $chartH) . '" stroke="#d1d5db" stroke-width="1"/>';
        $svg .= '<line x1="' . $padLeft . '" y1="' . ($padTop + $chartH) . '" x2="' . ($w - $padRight) . '" y2="' . ($padTop + $chartH) . '" stroke="#d1d5db" stroke-width="1"/>';

        $svg .= '</svg>';

        return $svg;
    }

    /**
     * Gráfico de barras horizontales para densidad kg/ha.
     * $datos: [['nombre' => string, 'valor' => float, 'color' => string], ...]
     */
    public function barHorizontal(array $datos, int $w = 480, int $h = 320): string
    {
        if (empty($datos)) {
            return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $h . '"></svg>';
        }

        $datos   = array_slice($datos, 0, 20);
        $count   = count($datos);
        $padTop  = 16; $padBottom = 24; $padLeft = 90; $padRight = 60;
        $chartW  = $w - $padLeft - $padRight;
        $chartH  = $h - $padTop - $padBottom;
        $maxVal  = max(array_column($datos, 'valor')) ?: 1;
        $avg     = array_sum(array_column($datos, 'valor')) / max($count, 1);
        $rowH    = $chartH / $count;
        $barH    = max(8, $rowH * 0.65);
        $gap     = ($rowH - $barH) / 2;

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $h . '" font-family="DejaVu Sans, Arial, sans-serif">';
        $svg .= '<rect width="' . $w . '" height="' . $h . '" fill="#ffffff"/>';

        foreach ($datos as $i => $d) {
            $barW = $chartW * ($d['valor'] / $maxVal);
            $y    = $padTop + $i * $rowH + $gap;
            $fill = $d['color'] ?? $this->colorByValue($d['valor'], $maxVal);

            // Row background (alternating)
            if ($i % 2 === 0) {
                $svg .= '<rect x="0" y="' . round($padTop + $i * $rowH, 1) . '" width="' . $w . '" height="' . round($rowH, 1) . '" fill="#f9fafb"/>';
            }

            // Label
            $svg .= '<text x="' . ($padLeft - 6) . '" y="' . round($y + $barH / 2 + 4, 1) . '" text-anchor="end" font-size="9" fill="#374151">' . htmlspecialchars(mb_strimwidth($d['nombre'], 0, 14, '…')) . '</text>';

            // Bar
            $svg .= '<rect x="' . $padLeft . '" y="' . round($y, 1) . '" width="' . round($barW, 1) . '" height="' . round($barH, 1) . '" fill="' . $fill . '" rx="2"/>';

            // Value
            $svg .= '<text x="' . round($padLeft + $barW + 4, 1) . '" y="' . round($y + $barH / 2 + 4, 1) . '" font-size="9" fill="#374151">' . number_format($d['valor'], 0) . '</text>';
        }

        // Average line vertical
        $avgX = $padLeft + $chartW * ($avg / $maxVal);
        $svg .= '<line x1="' . round($avgX, 1) . '" y1="' . $padTop . '" x2="' . round($avgX, 1) . '" y2="' . ($padTop + $chartH) . '" stroke="#6b7280" stroke-width="1.5" stroke-dasharray="4,3"/>';
        $svg .= '<text x="' . round($avgX, 1) . '" y="' . ($padTop + $chartH + 14) . '" text-anchor="middle" font-size="8" fill="#6b7280">Prom ' . number_format($avg, 0) . '</text>';

        // X-axis
        $svg .= '<line x1="' . $padLeft . '" y1="' . ($padTop + $chartH) . '" x2="' . ($padLeft + $chartW) . '" y2="' . ($padTop + $chartH) . '" stroke="#d1d5db" stroke-width="1"/>';

        $svg .= '</svg>';

        return $svg;
    }

    private function colorByValue(float $valor, float $max): string
    {
        $ratio = $max > 0 ? $valor / $max : 0;
        if ($ratio >= 0.75) return '#dc2626';
        if ($ratio >= 0.50) return '#f97316';
        if ($ratio >= 0.25) return '#eab308';
        return '#3b82f6';
    }
}
