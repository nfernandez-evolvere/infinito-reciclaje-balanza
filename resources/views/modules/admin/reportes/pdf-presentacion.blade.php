<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Informe de Pesajes</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        /* Paleta verde primaria (igual que design-tokens.css) */
        --p-950: oklch(0.247 0.052 144);
        --p-900: oklch(0.355 0.079 144);
        --p-800: oklch(0.441 0.109 144);
        --p-700: oklch(0.523 0.135 144); /* primary */
        --p-600: oklch(0.592 0.153 144);
        --p-500: oklch(0.665 0.163 144);
        --p-400: oklch(0.754 0.165 144);
        --p-300: oklch(0.840 0.135 144);
        --p-200: oklch(0.905 0.088 144);
        --p-100: oklch(0.948 0.048 144);
        --p-50:  oklch(0.976 0.022 144);

        /* Neutral */
        --n-950: oklch(0.145 0 0);
        --n-900: oklch(0.205 0 0);
        --n-800: oklch(0.269 0 0);
        --n-700: oklch(0.371 0 0);
        --n-600: oklch(0.439 0 0);
        --n-500: oklch(0.556 0 0);
        --n-400: oklch(0.708 0 0);
        --n-300: oklch(0.869 0 0);
        --n-200: oklch(0.922 0 0);
        --n-100: oklch(0.961 0 0);
        --n-50:  oklch(0.985 0 0);

        /* Estados */
        --red-600:    oklch(0.568 0.268 27);
        --red-50:     oklch(0.971 0.018 27);
        --amber-500:  oklch(0.745 0.210 95);
        --amber-50:   oklch(0.980 0.040 95);
    }

    body {
        font-family: 'Inter', -apple-system, Arial, sans-serif;
        font-size: 10px;
        color: var(--n-700);
        background: #fff;
        line-height: 1.5;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    /* ── PÁGINAS ── */
    .page {
        width: 297mm;
        min-height: 210mm;
        page-break-after: always;
        position: relative;
        overflow: hidden;
    }
    .page:last-child { page-break-after: avoid; }

    /* ════════════════════════════════════════
       PORTADA
    ════════════════════════════════════════ */
    .cover {
        background: var(--p-950);
        min-height: 210mm;
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
    }

    /* Detalle geométrico decorativo */
    .cover::before {
        content: '';
        position: absolute;
        top: -40mm;
        right: -20mm;
        width: 110mm;
        height: 110mm;
        border-radius: 50%;
        background: var(--p-800);
        opacity: 0.5;
    }
    .cover::after {
        content: '';
        position: absolute;
        bottom: 20mm;
        right: 10mm;
        width: 60mm;
        height: 60mm;
        border-radius: 50%;
        background: var(--p-900);
        opacity: 0.6;
    }

    .cover-stripe {
        position: absolute;
        top: 0; right: 42mm;
        width: 5mm;
        height: 100%;
        background: var(--p-600);
        opacity: 0.4;
    }

    .cover-body {
        padding: 28mm 28mm 0;
        position: relative;
        z-index: 1;
        flex: 1;
    }

    .cover-brand {
        display: inline-flex;
        align-items: center;
        gap: 2.5mm;
        font-size: 8px;
        font-weight: 700;
        letter-spacing: 0.28em;
        text-transform: uppercase;
        color: var(--p-300);
        margin-bottom: 24mm;
    }

    .cover-brand-dot {
        width: 5px; height: 5px;
        border-radius: 50%;
        background: var(--p-400);
        flex-shrink: 0;
    }

    .cover-label {
        display: inline-block;
        background: var(--p-700);
        color: var(--p-100);
        font-size: 7.5px;
        font-weight: 600;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        padding: 1.5mm 3mm;
        border-radius: 3px;
        margin-bottom: 6mm;
    }

    .cover-title {
        font-size: 52px;
        font-weight: 800;
        line-height: 0.95;
        color: #fff;
        letter-spacing: -0.03em;
        margin-bottom: 10mm;
    }

    .cover-title span {
        color: var(--p-400);
    }

    .cover-period {
        font-size: 15px;
        font-weight: 300;
        color: var(--p-200);
        letter-spacing: 0.02em;
    }

    .cover-footer {
        position: relative;
        z-index: 1;
        padding: 6mm 28mm;
        border-top: 1px solid var(--p-800);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .cover-muni {
        font-size: 12px;
        font-weight: 600;
        color: var(--p-100);
        margin-bottom: 1mm;
    }

    .cover-sub {
        font-size: 8px;
        color: var(--p-400);
        letter-spacing: 0.02em;
    }

    .cover-badge {
        background: oklch(from var(--p-700) l c h / 30%);
        border: 1px solid var(--p-700);
        border-radius: 5px;
        padding: 2.5mm 4.5mm;
        font-size: 7.5px;
        font-weight: 600;
        letter-spacing: 0.1em;
        color: var(--p-300);
        text-transform: uppercase;
    }

    /* ════════════════════════════════════════
       ENCABEZADO DE SLIDE
    ════════════════════════════════════════ */
    .slide-wrap {
        padding: 12mm 20mm 16mm;
        min-height: 210mm;
        display: flex;
        flex-direction: column;
    }

    .slide-head { margin-bottom: 7mm; }

    .slide-header-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: 3.5mm;
    }

    .slide-eyebrow {
        font-size: 6.5px;
        font-weight: 700;
        letter-spacing: 0.22em;
        text-transform: uppercase;
        color: var(--p-600);
        margin-bottom: 1.5mm;
    }

    .slide-title {
        font-size: 19px;
        font-weight: 700;
        color: var(--p-950);
        letter-spacing: -0.02em;
        line-height: 1.1;
    }

    .slide-meta {
        font-size: 8px;
        color: var(--n-400);
        text-align: right;
        white-space: nowrap;
    }

    .slide-rule {
        height: 1px;
        background: var(--n-200);
        position: relative;
    }

    .slide-rule::before {
        content: '';
        position: absolute;
        left: 0; top: 0;
        width: 28mm; height: 2px;
        background: var(--p-700);
        top: -0.5px;
    }

    .slide-content { flex: 1; }

    /* ════════════════════════════════════════
       QUIÉNES SOMOS
    ════════════════════════════════════════ */
    .intro-text {
        font-size: 10.5px;
        color: var(--n-600);
        line-height: 1.8;
        max-width: 200mm;
        margin-bottom: 9mm;
        padding-left: 4mm;
        border-left: 3px solid var(--p-300);
    }

    .services-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 5mm;
    }

    .svc-card {
        background: var(--p-50);
        border: 1px solid var(--p-100);
        border-radius: 8px;
        padding: 6mm 5.5mm;
        position: relative;
        overflow: hidden;
    }

    .svc-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--p-700), var(--p-400));
    }

    .svc-num {
        font-size: 28px;
        font-weight: 800;
        color: var(--p-200);
        line-height: 1;
        margin-bottom: 2mm;
        letter-spacing: -0.03em;
    }

    .svc-title {
        font-size: 11px;
        font-weight: 700;
        color: var(--p-900);
        margin-bottom: 2.5mm;
    }

    .svc-desc {
        font-size: 9px;
        color: var(--n-600);
        line-height: 1.65;
    }

    /* ════════════════════════════════════════
       KPIs — sistema de variantes
    ════════════════════════════════════════ */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 4mm;
        margin-bottom: 7mm;
    }

    .kpi-grid-4 {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 4mm;
        margin-bottom: 6mm;
    }

    /* Base card — custom properties sobreescritas por variante */
    .kpi-card {
        --kc-bg:        var(--p-950);
        --kc-icon-bg:   var(--p-800);
        --kc-stroke:    var(--p-300);
        --kc-circle:    var(--p-800);
        --kc-label:     var(--p-400);
        --kc-value:     #fff;
        --kc-unit:      var(--p-300);

        background: var(--kc-bg);
        border-radius: 10px;
        padding: 5mm 5mm 5mm;
        display: flex;
        align-items: center;
        gap: 3.5mm;
        position: relative;
        overflow: hidden;
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        bottom: -7mm; right: -7mm;
        width: 26mm; height: 26mm;
        border-radius: 50%;
        background: var(--kc-circle);
        opacity: 0.45;
    }

    /* Variante: verde medio */
    .kpi-card.v-mid {
        --kc-bg:       var(--p-800);
        --kc-icon-bg:  var(--p-700);
        --kc-stroke:   var(--p-100);
        --kc-circle:   var(--p-700);
        --kc-label:    var(--p-200);
        --kc-unit:     var(--p-200);
    }

    /* Variante: pizarra oscura (neutral) */
    .kpi-card.v-slate {
        --kc-bg:       var(--n-900);
        --kc-icon-bg:  var(--n-800);
        --kc-stroke:   var(--n-300);
        --kc-circle:   var(--n-800);
        --kc-label:    var(--n-400);
        --kc-unit:     var(--n-400);
    }

    /* Variante: azul oscuro (tiempo / calendario) */
    .kpi-card.v-blue {
        --kc-bg:       oklch(0.231 0.077 250);
        --kc-icon-bg:  oklch(0.401 0.154 250);
        --kc-stroke:   oklch(0.810 0.101 250);
        --kc-circle:   oklch(0.401 0.154 250);
        --kc-label:    oklch(0.707 0.143 250);
        --kc-unit:     oklch(0.707 0.143 250);
    }

    .kpi-icon {
        flex-shrink: 0;
        width: 10mm; height: 10mm;
        border-radius: 6px;
        background: var(--kc-icon-bg);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        z-index: 1;
    }

    .kpi-icon svg {
        width: 15px; height: 15px;
        stroke: var(--kc-stroke);
        fill: none;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .kpi-body {
        flex: 1;
        min-width: 0;
        position: relative;
        z-index: 1;
    }

    .kpi-label {
        font-size: 6.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.14em;
        color: var(--kc-label);
        margin-bottom: 2.5mm;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .kpi-value {
        font-size: 26px;
        font-weight: 800;
        line-height: 1;
        color: var(--kc-value);
        letter-spacing: -0.03em;
    }

    .kpi-unit {
        font-size: 7px;
        color: var(--kc-unit);
        margin-top: 2mm;
    }

    /* ════════════════════════════════════════
       INSIGHT BOXES
    ════════════════════════════════════════ */
    .insight {
        border-left: 3px solid var(--p-700);
        background: var(--p-50);
        border-radius: 0 7px 7px 0;
        padding: 4mm 5mm;
        margin-bottom: 4mm;
    }

    .insight.amber { border-left-color: var(--amber-500); background: var(--amber-50); }
    .insight.red   { border-left-color: var(--red-600);   background: var(--red-50);   }

    .insight-label {
        font-size: 6.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: var(--p-700);
        margin-bottom: 2mm;
    }

    .insight.amber .insight-label { color: oklch(0.560 0.168 95); }
    .insight.red   .insight-label { color: var(--red-600); }

    .insight-text {
        font-size: 9.5px;
        color: var(--n-600);
        line-height: 1.65;
    }


    /* ════════════════════════════════════════
       GRÁFICO BARRAS VERTICALES — EVOLUCIÓN
    ════════════════════════════════════════ */
    .chart-wrap {
        border: 1px solid var(--n-200);
        border-radius: 10px;
        padding: 5mm 5mm 4mm;
        background: var(--n-50);
    }

    .chart-label {
        font-size: 7px;
        font-weight: 600;
        color: var(--n-500);
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin-bottom: 3mm;
    }

    .bar-chart-vertical {
        display: flex;
        align-items: flex-end;
        gap: 1px;
        height: 52mm;
    }

    .bar-col {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end;
        height: 100%;
    }

    .bar-val {
        font-size: 5px;
        color: var(--n-500);
        margin-bottom: 1px;
        white-space: nowrap;
    }

    .bar-bar {
        width: 100%;
        border-radius: 2px 2px 0 0;
        min-height: 2px;
        background: var(--p-600);
    }

    .bar-bar.low { background: var(--red-600); }

    .bar-label {
        font-size: 5px;
        color: var(--n-400);
        margin-top: 1.5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        text-align: center;
    }

    .avg-note {
        display: flex;
        align-items: center;
        gap: 2mm;
        margin-top: 3mm;
    }

    .avg-dash {
        width: 12px;
        border-top: 1.5px dashed var(--n-400);
    }

    .avg-text {
        font-size: 7px;
        color: var(--n-500);
    }

    /* ════════════════════════════════════════
       GRÁFICO BARRAS HORIZONTALES
    ════════════════════════════════════════ */
    .hbar-chart { display: flex; flex-direction: column; gap: 2mm; }

    .hbar-row {
        display: flex;
        align-items: center;
        gap: 2.5mm;
    }

    .hbar-label {
        font-size: 8px;
        color: var(--n-700);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        text-align: right;
        flex-shrink: 0;
        width: 30mm;
    }

    .hbar-track {
        flex: 1;
        height: 5.5mm;
        background: var(--n-100);
        border-radius: 3px;
        overflow: hidden;
    }

    .hbar-fill {
        height: 100%;
        border-radius: 3px;
        display: flex;
        align-items: center;
        padding-left: 2mm;
        background: var(--p-700);
    }

    .hbar-fill-val {
        font-size: 7px;
        font-weight: 600;
        color: #fff;
        white-space: nowrap;
    }

    .hbar-after {
        font-size: 7.5px;
        color: var(--n-500);
        white-space: nowrap;
        width: 11mm;
        text-align: right;
        font-weight: 500;
    }

    /* ════════════════════════════════════════
       LAYOUT DOS COLUMNAS
    ════════════════════════════════════════ */
    .two-col { display: grid; gap: 7mm; }
    .two-col-4-6 { grid-template-columns: 4fr 6fr; }
    .two-col-5-5 { grid-template-columns: 1fr 1fr; }

    /* ════════════════════════════════════════
       TABLA DE DATOS
    ════════════════════════════════════════ */
    table.data {
        width: 100%;
        border-collapse: collapse;
        font-size: 8.5px;
    }

    table.data thead th {
        background: var(--p-950);
        color: var(--p-200);
        padding: 3.5mm 4mm;
        text-align: left;
        font-weight: 600;
        font-size: 7px;
        text-transform: uppercase;
        letter-spacing: 0.09em;
    }

    table.data thead th:first-child { border-radius: 6px 0 0 0; }
    table.data thead th:last-child  { border-radius: 0 6px 0 0; }
    table.data thead th.r { text-align: right; }

    table.data tbody td {
        padding: 2.8mm 4mm;
        border-bottom: 1px solid var(--n-100);
        color: var(--n-700);
        vertical-align: middle;
    }

    table.data tbody tr:nth-child(even) td { background: var(--p-50); }
    table.data tbody td.r      { text-align: right; }
    table.data tbody td.strong { font-weight: 600; color: var(--p-950); }
    table.data tbody td.num    { text-align: right; font-weight: 600; color: var(--p-950); font-variant-numeric: tabular-nums; }
    table.data tbody td.muted  { color: var(--n-400); text-align: right; }

    table.data tfoot td {
        padding: 3.5mm 4mm;
        font-weight: 700;
        color: var(--p-950);
        border-top: 2px solid var(--p-700);
        background: var(--p-50);
    }

    table.data tfoot td.r { text-align: right; }
    table.data tfoot td:first-child { border-radius: 0 0 0 6px; }
    table.data tfoot td:last-child  { border-radius: 0 0 6px 0; }

    /* ════════════════════════════════════════
       BADGES / DOTS / PILLS
    ════════════════════════════════════════ */
    .dot {
        display: inline-block;
        width: 7px; height: 7px;
        border-radius: 50%;
        margin-right: 4px;
        vertical-align: middle;
        flex-shrink: 0;
    }

    .pill {
        display: inline-block;
        padding: 0.8mm 2.5mm;
        border-radius: 20px;
        background: var(--p-100);
        color: var(--p-800);
        font-size: 7px;
        font-weight: 600;
    }

    /* ════════════════════════════════════════
       LEYENDA
    ════════════════════════════════════════ */
    .legend {
        display: flex;
        gap: 5mm;
        margin-bottom: 4mm;
        flex-wrap: wrap;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 1.5mm;
        font-size: 7.5px;
        color: var(--n-500);
        white-space: nowrap;
    }

    /* ════════════════════════════════════════
       PIE DE PÁGINA
    ════════════════════════════════════════ */
    .foot {
        position: absolute;
        left: 20mm; right: 20mm;
        bottom: 7mm;
        border-top: 1px solid var(--n-200);
        padding-top: 2.5mm;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .foot-left { font-size: 7px; color: var(--n-400); }
    .foot-brand { font-weight: 600; color: var(--p-700); }
    .foot-right { font-size: 7px; color: var(--n-400); text-align: right; }

    /* ════════════════════════════════════════
       CIERRE — cards oscuras (estilo portada)
    ════════════════════════════════════════ */
    .closing-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 4mm;
    }

    .closing-card {
        background: var(--p-900);
        border: 1px solid var(--p-800);
        border-top: 3px solid var(--p-600);
        border-radius: 8px;
        padding: 5mm 5mm;
    }

    .closing-num {
        font-size: 22px;
        font-weight: 800;
        color: var(--p-700);
        line-height: 1;
        margin-bottom: 2.5mm;
        letter-spacing: -0.03em;
    }

    .closing-card-title {
        font-size: 10px;
        font-weight: 700;
        color: var(--p-100);
        margin-bottom: 2mm;
    }

    .closing-card-desc {
        font-size: 8.5px;
        color: var(--p-400);
        line-height: 1.6;
    }

    /* ════════════════════════════════════════
       SLIDE ALERTAS
    ════════════════════════════════════════ */
    .alerta-tipo-header {
        display: flex;
        align-items: center;
        gap: 3mm;
        margin: 6mm 0 3mm;
        padding-bottom: 2mm;
        border-bottom: 2px solid var(--p-700);
    }
    .alerta-tipo-header:first-child { margin-top: 0; }

    .alerta-tipo-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .alerta-tipo-label {
        font-size: 8.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: var(--p-950);
    }

    .alerta-tipo-count {
        margin-left: auto;
        font-size: 7.5px;
        font-weight: 600;
        color: var(--n-400);
    }

    .alerta-row {
        display: flex;
        gap: 4mm;
        padding: 2.5mm 3mm;
        border-bottom: 1px solid var(--n-100);
        align-items: flex-start;
    }
    .alerta-row:last-child { border-bottom: none; }
    .alerta-row:nth-child(even) { background: var(--p-50); }

    .alerta-fecha {
        font-size: 7.5px;
        color: var(--n-500);
        white-space: nowrap;
        flex-shrink: 0;
        padding-top: 0.5mm;
        width: 16mm;
    }

    .alerta-body { flex: 1; min-width: 0; }

    .alerta-titulo {
        font-size: 8.5px;
        font-weight: 600;
        color: var(--p-950);
        margin-bottom: 1mm;
    }

    .alerta-desc {
        font-size: 7.5px;
        color: var(--n-600);
        line-height: 1.5;
    }

    .alerta-zona {
        font-size: 7px;
        color: var(--p-700);
        font-weight: 500;
        margin-top: 0.8mm;
    }

    .alerta-tipo-colors {
        'peso_fuera_rango':        '#f59e0b',
        'volumen_diario_atipico':  '#dc2626',
        'gap_registro':            '#6b7280',
        'frecuencia_zona_atipica': '#f59e0b',
    }

    .resumen-alertas {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 4mm;
        margin-bottom: 7mm;
    }

    .resumen-alerta-card {
        background: var(--n-50);
        border: 1px solid var(--n-200);
        border-top: 3px solid var(--p-700);
        border-radius: 7px;
        padding: 4mm 4mm;
    }

    .resumen-alerta-card.amber { border-top-color: var(--amber-500); }
    .resumen-alerta-card.red   { border-top-color: var(--red-600); }
    .resumen-alerta-card.gray  { border-top-color: var(--n-400); }

    .resumen-alerta-num {
        font-size: 24px;
        font-weight: 800;
        color: var(--p-950);
        line-height: 1;
        margin-bottom: 1.5mm;
    }

    .resumen-alerta-label {
        font-size: 7.5px;
        color: var(--n-600);
        line-height: 1.4;
    }

</style>
</head>
<body>

@php
    $kpis      = $reporte['kpis'];
    $evolucion = $reporte['evolucion'];
    $zonas     = $reporte['zonas'];
    $vehiculos = $reporte['vehiculos'];
    $desde     = $reporte['desde'];
    $hasta     = $reporte['hasta'];
    $config    = $reporte['config'] ?? null;
    $ai        = $reporte['conclusiones'] ?? [];

    $esAlerta  = ($tipo ?? 'informe_mensual') === 'alertas';
    $alertas   = $reporte['alertas'] ?? collect();

    $tipoLabels = [
        'peso_fuera_rango'        => 'Peso fuera de rango',
        'volumen_diario_atipico'  => 'Volumen atípico',
        'gap_registro'            => 'Sin actividad',
        'frecuencia_zona_atipica' => 'Frecuencia atípica',
    ];
    $alertasAgrupadas = $alertas->groupBy('tipo');

    $periodo      = ucfirst($desde->translatedFormat('F Y'));
    $periodoLargo = $desde->translatedFormat('d \d\e F') . ' al ' . $hasta->translatedFormat('d \d\e F \d\e Y');
    $organizacion = $config?->municipalidad_nombre ?? 'la organización';
    $generado      = now()->format('d/m/Y H:i');
    $diasConDatos  = count(array_filter(array_column($evolucion['datos'], 'toneladas')));

    $servicios = $config?->servicios ?? [
        ['titulo' => 'Recolección y Reciclaje',  'descripcion' => 'Gestión de cartón, papel, plásticos y transformación en materia prima.'],
        ['titulo' => 'Datos y Trazabilidad',      'descripcion' => 'Reportes mensuales, certificados y plataforma digital de seguimiento.'],
        ['titulo' => 'Capacitación',              'descripcion' => 'Asesoría en gestión ambiental y operación de puntos verdes.'],
    ];

    // Escala de colores por kg para zonas
    $colorZona = fn (float $kg) => match (true) {
        $kg >= 500000 => '#dc2626',
        $kg >= 150000 => '#ea580c',
        $kg >= 80000  => '#f59e0b',
        $kg >= 30000  => '#eab308',
        default       => 'oklch(0.592 0.153 144)',
    };

    // Evolución
    $evDatos   = $evolucion['datos'];
    $evMax     = max(array_column($evDatos, 'toneladas') ?: [1]);
    $evAvg     = $evolucion['promedio'];
    $showEvery = max(1, (int) ceil(count($evDatos) / 20));

    // Vehículos
    $vMax    = $vehiculos->max('viajes') ?: 1;
    $vColors = [
        'oklch(0.523 0.135 144)',
        'oklch(0.441 0.109 144)',
        'oklch(0.665 0.163 144)',
        'oklch(0.355 0.079 144)',
        'oklch(0.754 0.165 144)',
        'oklch(0.247 0.052 144)',
    ];

    // Densidad
    $zonasConHa = $zonas->filter(fn ($z) => $z['kg_ha'] !== null)->sortByDesc('kg_ha')->take(15)->values();
    $haMax      = $zonasConHa->max('kg_ha') ?: 1;
@endphp

{{-- ═══════════ PORTADA ═══════════ --}}
<div class="page cover">
    <div class="cover-stripe"></div>
    <div class="cover-body">
        <div class="cover-brand">
            <div class="cover-brand-dot"></div>
            Infinito Reciclaje
        </div>
        <div class="cover-label">{{ $esAlerta ? 'Reporte de alertas de peso' : 'Informe mensual de gestión' }}</div>
        <div class="cover-title">
            @if ($esAlerta)
                Reporte<br>de <span>Alertas</span><br>{{ $periodo }}
            @else
                Informe<br>de <span>Pesajes</span><br>{{ $periodo }}
            @endif
        </div>
        <div class="cover-period">{{ $periodoLargo }}</div>
    </div>
    <div class="cover-footer">
        <div>
            <div class="cover-muni">{{ $organizacion }}</div>
            <div class="cover-sub">Sistema de Balanza Digital · Gestión de Residuos</div>
        </div>
        <div class="cover-badge">Gestión Integral de Residuos</div>
    </div>
</div>

{{-- ═══════════ QUIÉNES SOMOS ═══════════ --}}
<div class="page">
    <div class="slide-wrap">
        <div class="slide-head">
            <div class="slide-header-row">
                <div>
                    <div class="slide-eyebrow">Presentación</div>
                    <div class="slide-title">Quiénes Somos</div>
                </div>
            </div>
            <div class="slide-rule"></div>
        </div>

        <div class="slide-content">
            <p class="intro-text">
                @if($config?->intro_empresa)
                    {{ $config->intro_empresa }}
                @else
                    Somos una empresa especializada en la gestión integral de residuos urbanos, brindando servicios de
                    recolección, reciclaje y trazabilidad de datos para organizaciones. Nuestro sistema de balanza digital
                    garantiza transparencia y precisión en cada registro, convirtiendo cada pesaje en información estratégica.
                @endif
            </p>

            <div class="services-grid">
                @foreach(array_slice($servicios, 0, 3) as $i => $servicio)
                <div class="svc-card">
                    <div class="svc-num">0{{ $i + 1 }}</div>
                    <div class="svc-title">{{ $servicio['titulo'] ?? '' }}</div>
                    <div class="svc-desc">{{ $servicio['descripcion'] ?? '' }}</div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="foot">
            <div class="foot-left"><span class="foot-brand">Infinito Reciclaje</span> · Gestión Integral de Residuos</div>
            <div class="foot-right">{{ $organizacion }} · {{ $periodo }}</div>
        </div>
    </div>
</div>

{{-- ═══════════ SLIDE ALERTAS (solo tipo alertas) ═══════════ --}}
@if($esAlerta)

{{-- Resumen --}}
<div class="page">
    <div class="slide-wrap">
        <div class="slide-head">
            <div class="slide-header-row">
                <div>
                    <div class="slide-eyebrow">Detección automática</div>
                    <div class="slide-title">Alertas del Período</div>
                </div>
                <div class="slide-meta">{{ $periodoLargo }}</div>
            </div>
            <div class="slide-rule"></div>
        </div>

        <div class="slide-content">

            @php
                $colores = [
                    'peso_fuera_rango'        => 'amber',
                    'volumen_diario_atipico'  => 'red',
                    'gap_registro'            => 'gray',
                    'frecuencia_zona_atipica' => 'amber',
                ];
            @endphp

            {{-- Resumen por tipo --}}
            <div class="resumen-alertas">
                @foreach($tipoLabels as $tipoKey => $tipoNombre)
                @php $cnt = ($alertasAgrupadas[$tipoKey] ?? collect())->count(); @endphp
                <div class="resumen-alerta-card {{ $colores[$tipoKey] ?? '' }}">
                    <div class="resumen-alerta-num">{{ $cnt }}</div>
                    <div class="resumen-alerta-label">{{ $tipoNombre }}</div>
                </div>
                @endforeach
            </div>

            @if($alertas->isEmpty())
                <div class="insight">
                    <div class="insight-label">Sin alertas en el período</div>
                    <div class="insight-text">No se detectaron alertas entre {{ $periodoLargo }}.</div>
                </div>
            @else
                <div class="insight amber">
                    <div class="insight-label">Total de alertas detectadas</div>
                    <div class="insight-text">
                        Se detectaron <strong>{{ $alertas->count() }} alertas</strong> entre {{ $periodoLargo }}.
                        @php $sinLeer = $alertas->where('leida', false)->count(); @endphp
                        @if($sinLeer > 0)
                            <strong>{{ $sinLeer }}</strong> {{ $sinLeer === 1 ? 'permanece' : 'permanecen' }} sin leer.
                        @endif
                    </div>
                </div>
            @endif

        </div>

        <div class="foot">
            <div class="foot-left"><span class="foot-brand">Infinito Reciclaje</span> · Gestión Integral de Residuos</div>
            <div class="foot-right">{{ $organizacion }} · {{ $periodo }}</div>
        </div>
    </div>
</div>

{{-- Detalle por tipo (una página por tipo con alertas) --}}
@foreach($tipoLabels as $tipoKey => $tipoNombre)
@php $grupo = $alertasAgrupadas[$tipoKey] ?? collect(); @endphp
@if($grupo->isNotEmpty())
@foreach($grupo->chunk(20) as $chunkIdx => $chunk)
<div class="page">
    <div class="slide-wrap">
        <div class="slide-head">
            <div class="slide-header-row">
                <div>
                    <div class="slide-eyebrow">Alertas — {{ $tipoNombre }}</div>
                    <div class="slide-title">
                        {{ $tipoNombre }}
                        @if($grupo->count() > 20)
                            <span style="font-size:12px;color:oklch(0.708 0 0);font-weight:400;">({{ $chunkIdx + 1 }}/{{ $grupo->chunk(20)->count() }})</span>
                        @endif
                    </div>
                </div>
                <div class="slide-meta">{{ $grupo->count() }} {{ $grupo->count() === 1 ? 'alerta' : 'alertas' }} · {{ $periodo }}</div>
            </div>
            <div class="slide-rule"></div>
        </div>

        <div class="slide-content">
            @foreach($chunk as $alerta)
            <div class="alerta-row">
                <div class="alerta-fecha">{{ $alerta->fecha_deteccion->format('d/m/Y') }}</div>
                <div class="alerta-body">
                    <div class="alerta-titulo">{{ $alerta->titulo }}</div>
                    @if($alerta->descripcion)
                        <div class="alerta-desc">{{ $alerta->descripcion }}</div>
                    @endif
                    @if($alerta->zona)
                        <div class="alerta-zona">Zona: {{ $alerta->zona->nombre }}</div>
                    @endif
                </div>
                <div style="flex-shrink:0;margin-left:3mm;">
                    @if($alerta->leida)
                        <span style="font-size:7px;color:oklch(0.592 0.153 144);font-weight:600;">Leída</span>
                    @else
                        <span style="font-size:7px;color:var(--amber-500);font-weight:600;">Sin leer</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <div class="foot">
            <div class="foot-left"><span class="foot-brand">Infinito Reciclaje</span> · Gestión Integral de Residuos</div>
            <div class="foot-right">{{ $organizacion }} · {{ $periodo }}</div>
        </div>
    </div>
</div>
@endforeach
@endif
@endforeach

@endif
{{-- ═══════════ FIN SLIDE ALERTAS ═══════════ --}}

@if(!$esAlerta)
{{-- ═══════════ RESUMEN GENERAL ═══════════ --}}
<div class="page">
    <div class="slide-wrap">
        <div class="slide-head">
            <div class="slide-header-row">
                <div>
                    <div class="slide-eyebrow">Indicadores clave</div>
                    <div class="slide-title">Resumen General</div>
                </div>
                <div class="slide-meta">{{ $periodoLargo }}</div>
            </div>
            <div class="slide-rule"></div>
        </div>

        <div class="slide-content">
            <div class="kpi-grid">
                {{-- Toneladas netas — default (verde profundo) — icon: package --}}
                <div class="kpi-card">
                    <div class="kpi-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"/>
                            <path d="M12 22V12"/>
                            <path d="m3.3 7 7.703 4.734a2 2 0 0 0 1.994 0L20.7 7"/>
                            <path d="m7.5 4.27 9 5.15"/>
                        </svg>
                    </div>
                    <div class="kpi-body">
                        <div class="kpi-label">Toneladas netas</div>
                        <div class="kpi-value">{{ number_format($kpis['toneladas'], 0, ',', '.') }}</div>
                        <div class="kpi-unit">toneladas recolectadas</div>
                    </div>
                </div>

                {{-- Total viajes — v-slate (neutral) — icon: scale --}}
                <div class="kpi-card v-slate">
                    <div class="kpi-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/>
                            <path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/>
                            <path d="M7 21h10"/>
                            <path d="M12 3v18"/>
                            <path d="M3 7h2c2 0 4-1 7-1s5 1 7 1h2"/>
                        </svg>
                    </div>
                    <div class="kpi-body">
                        <div class="kpi-label">Total viajes</div>
                        <div class="kpi-value">{{ number_format($kpis['total'], 0, ',', '.') }}</div>
                        <div class="kpi-unit">pesajes registrados</div>
                    </div>
                </div>

                {{-- Días operativos — v-blue (temporal) — icon: calendar-days --}}
                <div class="kpi-card v-blue">
                    <div class="kpi-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 2v4"/><path d="M16 2v4"/>
                            <rect width="18" height="18" x="3" y="4" rx="2"/>
                            <path d="M3 10h18"/>
                            <path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/>
                            <path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/>
                        </svg>
                    </div>
                    <div class="kpi-body">
                        <div class="kpi-label">Días operativos</div>
                        <div class="kpi-value">{{ $kpis['dias_op'] }}</div>
                        <div class="kpi-unit">de {{ $kpis['dias_rango'] }} días del período</div>
                    </div>
                </div>

                {{-- Promedio diario — v-mid (verde medio) — icon: trending-up --}}
                <div class="kpi-card v-mid">
                    <div class="kpi-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="m22 7-8.5 8.5-5-5L2 17"/>
                            <path d="M16 7h6v6"/>
                        </svg>
                    </div>
                    <div class="kpi-body">
                        <div class="kpi-label">Promedio diario</div>
                        <div class="kpi-value">{{ number_format($kpis['promedio_ton_dia'], 1, ',', '.') }}</div>
                        <div class="kpi-unit">toneladas / día operativo</div>
                    </div>
                </div>

                {{-- Promedio por viaje — v-slate (neutral) — icon: truck --}}
                <div class="kpi-card v-slate">
                    <div class="kpi-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/>
                            <path d="M15 18H9"/>
                            <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/>
                            <circle cx="17" cy="18" r="2"/>
                            <circle cx="7" cy="18" r="2"/>
                        </svg>
                    </div>
                    <div class="kpi-body">
                        <div class="kpi-label">Promedio por viaje</div>
                        <div class="kpi-value">{{ number_format($kpis['promedio_kg_viaje'], 0, ',', '.') }}</div>
                        <div class="kpi-unit">kg por viaje</div>
                    </div>
                </div>
            </div>

            <div class="insight">
                <div class="insight-label">Lectura del período</div>
                <div class="insight-text">
                    Durante {{ $periodo }} se registraron <strong>{{ number_format($kpis['total'], 0, ',', '.') }} viajes</strong>
                    que totalizaron <strong>{{ number_format($kpis['toneladas'], 1, ',', '.') }} toneladas netas</strong>,
                    con actividad en <strong>{{ $kpis['dias_op'] }} de {{ $kpis['dias_rango'] }} días</strong> y un promedio
                    de {{ number_format($kpis['promedio_ton_dia'], 1, ',', '.') }} toneladas por jornada operativa.
                </div>
            </div>
        </div>

        <div class="foot">
            <div class="foot-left"><span class="foot-brand">Infinito Reciclaje</span> · Gestión Integral de Residuos</div>
            <div class="foot-right">{{ $organizacion }} · {{ $periodo }}</div>
        </div>
    </div>
</div>

{{-- ═══════════ EVOLUCIÓN DIARIA ═══════════ --}}
<div class="page">
    <div class="slide-wrap">
        <div class="slide-head">
            <div class="slide-header-row">
                <div>
                    <div class="slide-eyebrow">Tendencia</div>
                    <div class="slide-title">Evolución Diaria de Toneladas</div>
                </div>
                <div class="slide-meta">{{ $periodo }}</div>
            </div>
            <div class="slide-rule"></div>
        </div>

        <div class="slide-content">
            <div class="kpi-grid-4">
                {{-- Promedio — v-mid (verde medio) — icon: bar-chart-2 --}}
                <div class="kpi-card v-mid">
                    <div class="kpi-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <line x1="18" x2="18" y1="20" y2="10"/>
                            <line x1="12" x2="12" y1="20" y2="4"/>
                            <line x1="6" x2="6" y1="20" y2="14"/>
                        </svg>
                    </div>
                    <div class="kpi-body">
                        <div class="kpi-label">Promedio</div>
                        <div class="kpi-value">{{ number_format($evolucion['promedio'], 1, ',', '.') }}</div>
                        <div class="kpi-unit">toneladas / día</div>
                    </div>
                </div>

                {{-- Máximo — default (verde profundo) — icon: arrow-up --}}
                <div class="kpi-card">
                    <div class="kpi-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="m5 12 7-7 7 7"/>
                            <path d="M12 19V5"/>
                        </svg>
                    </div>
                    <div class="kpi-body">
                        <div class="kpi-label">Máximo</div>
                        <div class="kpi-value">{{ number_format($evolucion['maximo'], 1, ',', '.') }}</div>
                        <div class="kpi-unit">toneladas en un día</div>
                    </div>
                </div>

                {{-- Mínimo — v-slate (neutral) — icon: arrow-down --}}
                <div class="kpi-card v-slate">
                    <div class="kpi-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 5v14"/>
                            <path d="m19 12-7 7-7-7"/>
                        </svg>
                    </div>
                    <div class="kpi-body">
                        <div class="kpi-label">Mínimo</div>
                        <div class="kpi-value">{{ number_format($evolucion['minimo'], 1, ',', '.') }}</div>
                        <div class="kpi-unit">toneladas en un día</div>
                    </div>
                </div>

                {{-- Días con datos — v-blue (temporal) — icon: calendar-days --}}
                <div class="kpi-card v-blue">
                    <div class="kpi-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 2v4"/><path d="M16 2v4"/>
                            <rect width="18" height="18" x="3" y="4" rx="2"/>
                            <path d="M3 10h18"/>
                            <path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/>
                            <path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/>
                        </svg>
                    </div>
                    <div class="kpi-body">
                        <div class="kpi-label">Días con datos</div>
                        <div class="kpi-value">{{ $diasConDatos }}</div>
                        <div class="kpi-unit">días con actividad</div>
                    </div>
                </div>
            </div>

            <div class="chart-wrap">
                <div class="chart-label">Toneladas por día</div>
                <div class="bar-chart-vertical">
                    @foreach($evDatos as $i => $d)
                    @php
                        $pct   = $evMax > 0 ? ($d['toneladas'] / $evMax) * 100 : 0;
                        $isLow = $d['toneladas'] > 0 && $d['toneladas'] < ($evAvg * 0.3);
                        $showL = ($i % $showEvery === 0);
                    @endphp
                    <div class="bar-col">
                        @if($d['toneladas'] > 0 && $pct > 5)
                            <div class="bar-val">{{ number_format($d['toneladas'], 1) }}</div>
                        @endif
                        <div class="bar-bar {{ $isLow ? 'low' : '' }}"
                             style="height: {{ max($pct, $d['toneladas'] > 0 ? 1.5 : 0) }}%;"></div>
                        @if($showL)
                            <div class="bar-label">{{ $d['fecha'] }}</div>
                        @else
                            <div class="bar-label">&nbsp;</div>
                        @endif
                    </div>
                    @endforeach
                </div>
                <div class="avg-note">
                    <div class="avg-dash"></div>
                    <span class="avg-text">Promedio: {{ number_format($evAvg, 1, ',', '.') }} t/día</span>
                </div>
            </div>
        </div>

        <div class="foot">
            <div class="foot-left"><span class="foot-brand">Infinito Reciclaje</span> · Gestión Integral de Residuos</div>
            <div class="foot-right">{{ $organizacion }} · {{ $periodo }}</div>
        </div>
    </div>
</div>

{{-- ═══════════ POR TIPO DE VEHÍCULO ═══════════ --}}
<div class="page">
    <div class="slide-wrap">
        <div class="slide-head">
            <div class="slide-header-row">
                <div>
                    <div class="slide-eyebrow">Composición de flota</div>
                    <div class="slide-title">Por Tipo de Vehículo</div>
                </div>
                <div class="slide-meta">{{ $periodo }}</div>
            </div>
            <div class="slide-rule"></div>
        </div>

        <div class="slide-content">
            <div class="two-col two-col-4-6">
                <div class="chart-wrap" style="padding: 4.5mm;">
                    <div class="chart-label">Viajes por tipo</div>
                    <div class="hbar-chart">
                        @foreach($vehiculos as $vi => $v)
                        @php
                            $pct   = $vMax > 0 ? round(($v['viajes'] / $vMax) * 100) : 0;
                            $color = $vColors[$vi % count($vColors)];
                        @endphp
                        <div class="hbar-row">
                            <div class="hbar-label">{{ $v['nombre'] }}</div>
                            <div class="hbar-track">
                                <div class="hbar-fill" style="width: {{ $pct }}%; background: {{ $color }};">
                                    @if($pct > 22)
                                    <span class="hbar-fill-val">{{ number_format($v['viajes'], 0, ',', '.') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="hbar-after">{{ number_format($v['porcentaje'], 1) }}%</div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <table class="data">
                        <thead>
                            <tr>
                                <th>Tipo de vehículo</th>
                                <th class="r">Viajes</th>
                                <th class="r">Toneladas</th>
                                <th class="r">% Total</th>
                                <th class="r">kg / viaje</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vehiculos as $vi => $v)
                            <tr>
                                <td class="strong">
                                    <span class="dot" style="background: {{ $vColors[$vi % count($vColors)] }};"></span>
                                    {{ $v['nombre'] }}
                                </td>
                                <td class="num">{{ number_format($v['viajes'], 0, ',', '.') }}</td>
                                <td class="num">{{ number_format($v['toneladas'], 1, ',', '.') }}</td>
                                <td class="muted">{{ number_format($v['porcentaje'], 1, ',', '.') }}%</td>
                                <td class="num">{{ number_format($v['kg_viaje'], 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>Total</td>
                                <td class="r">{{ number_format($vehiculos->sum('viajes'), 0, ',', '.') }}</td>
                                <td class="r">{{ number_format($vehiculos->sum('toneladas'), 1, ',', '.') }}</td>
                                <td class="r">100%</td>
                                <td class="r">—</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="foot">
            <div class="foot-left"><span class="foot-brand">Infinito Reciclaje</span> · Gestión Integral de Residuos</div>
            <div class="foot-right">{{ $organizacion }} · {{ $periodo }}</div>
        </div>
    </div>
</div>

{{-- ═══════════ POR ZONA ═══════════ --}}
@php
    $zonasChunks = $zonas->chunk(16);
    $totalSlides = $zonasChunks->count();
@endphp

@foreach($zonasChunks as $chunkIdx => $chunk)
<div class="page">
    <div class="slide-wrap">
        <div class="slide-head">
            <div class="slide-header-row">
                <div>
                    <div class="slide-eyebrow">Distribución territorial</div>
                    <div class="slide-title">
                        Toneladas Netas por Zona
                        @if($totalSlides > 1)
                            <span style="font-size: 12px; color: oklch(0.708 0 0); font-weight: 400;">({{ $chunkIdx + 1 }}/{{ $totalSlides }})</span>
                        @endif
                    </div>
                </div>
                <div class="slide-meta">{{ $periodo }}</div>
            </div>
            <div class="slide-rule"></div>
        </div>

        <div class="slide-content">
            @if($chunkIdx === 0)
            <div class="legend">
                <div class="legend-item"><span class="dot" style="background:#dc2626;"></span> Más de 500 t</div>
                <div class="legend-item"><span class="dot" style="background:#ea580c;"></span> 150 – 500 t</div>
                <div class="legend-item"><span class="dot" style="background:#f59e0b;"></span> 80 – 150 t</div>
                <div class="legend-item"><span class="dot" style="background:#eab308;"></span> 30 – 80 t</div>
                <div class="legend-item"><span class="dot" style="background:oklch(0.592 0.153 144);"></span> Menos de 30 t</div>
            </div>
            @endif

            <table class="data">
                <thead>
                    <tr>
                        <th>Zona</th>
                        <th>Turno</th>
                        <th class="r">Viajes</th>
                        <th class="r">Toneladas</th>
                        <th class="r">kg / viaje</th>
                        <th class="r">% Total</th>
                        <th class="r">kg / ha</th>
                        <th class="r">kg / hab</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($chunk as $zona)
                    @php $kg = $zona['toneladas'] * 1000; $dotColor = $colorZona($kg); @endphp
                    <tr>
                        <td class="strong">
                            <span class="dot" style="background: {{ $dotColor }};"></span>
                            {{ $zona['nombre'] }}
                        </td>
                        <td>
                            @if($zona['turno'])
                                <span class="pill">{{ $zona['turno'] }}</span>
                            @else
                                <span style="color: oklch(0.708 0 0);">—</span>
                            @endif
                        </td>
                        <td class="num">{{ number_format($zona['viajes'], 0, ',', '.') }}</td>
                        <td class="num" style="color: {{ $dotColor }};">{{ number_format($zona['toneladas'], 1, ',', '.') }}</td>
                        <td class="muted">{{ number_format($zona['kg_viaje'], 0, ',', '.') }}</td>
                        <td class="muted">{{ number_format($zona['porcentaje'], 1, ',', '.') }}%</td>
                        <td class="num">{{ $zona['kg_ha'] !== null ? number_format($zona['kg_ha'], 1, ',', '.') : '—' }}</td>
                        <td class="num">{{ isset($zona['kg_hab']) && $zona['kg_hab'] !== null ? number_format($zona['kg_hab'], 2, ',', '.') : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                @if($chunkIdx === $totalSlides - 1)
                <tfoot>
                    <tr>
                        <td colspan="2">Total general</td>
                        <td class="r">{{ number_format($zonas->sum('viajes'), 0, ',', '.') }}</td>
                        <td class="r">{{ number_format($zonas->sum('toneladas'), 1, ',', '.') }}</td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        <div class="foot">
            <div class="foot-left"><span class="foot-brand">Infinito Reciclaje</span> · Gestión Integral de Residuos</div>
            <div class="foot-right">{{ $organizacion }} · {{ $periodo }}</div>
        </div>
    </div>
</div>
@endforeach

{{-- ═══════════ DENSIDAD kg/ha ═══════════ --}}
@if($zonasConHa->isNotEmpty())
<div class="page">
    <div class="slide-wrap">
        <div class="slide-head">
            <div class="slide-header-row">
                <div>
                    <div class="slide-eyebrow">Intensidad de generación</div>
                    <div class="slide-title">Densidad por Hectárea</div>
                </div>
                <div class="slide-meta">Top {{ $zonasConHa->count() }} zonas · {{ $periodo }}</div>
            </div>
            <div class="slide-rule"></div>
        </div>

        <div class="slide-content">
            <div class="two-col two-col-5-5">
                <div class="chart-wrap" style="padding: 4.5mm;">
                    <div class="chart-label">kg por hectárea</div>
                    <div class="hbar-chart">
                        @foreach($zonasConHa as $zi => $z)
                        @php
                            $pct   = $haMax > 0 ? round(($z['kg_ha'] / $haMax) * 100) : 0;
                            $ratio = $haMax > 0 ? $z['kg_ha'] / $haMax : 0;
                            $color = match(true) {
                                $ratio >= 0.75 => '#dc2626',
                                $ratio >= 0.50 => '#ea580c',
                                $ratio >= 0.25 => 'oklch(0.523 0.135 144)',
                                default        => 'oklch(0.665 0.163 144)',
                            };
                            $label = $z['nombre'] . ($z['turno'] ? ' ' . substr($z['turno'], 0, 1) : '');
                        @endphp
                        <div class="hbar-row">
                            <div class="hbar-label">{{ mb_strimwidth($label, 0, 18, '…') }}</div>
                            <div class="hbar-track">
                                <div class="hbar-fill" style="width: {{ $pct }}%; background: {{ $color }};">
                                    @if($pct > 22)
                                    <span class="hbar-fill-val">{{ number_format($z['kg_ha'], 0) }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="hbar-after">{{ number_format($z['kg_ha'], 0) }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 4mm;">
                    @php
                        $topZonas = $zonasConHa->take(3)->pluck('nombre')->join(', ');
                        $bottom   = $zonasConHa->last();
                        $topVal   = $zonasConHa->first()['kg_ha'] ?? 0;
                    @endphp
                    <div class="insight">
                        <div class="insight-label">Zonas de mayor densidad</div>
                        <div class="insight-text">
                            {{ $topZonas }} presentan los valores más altos de generación por hectárea,
                            lo que sugiere mayor frecuencia de recolección o mayor concentración de actividad.
                        </div>
                    </div>
                    @if($bottom && $bottom['kg_ha'] < $topVal * 0.2)
                    <div class="insight amber">
                        <div class="insight-label">Zona de baja densidad</div>
                        <div class="insight-text">
                            {{ $bottom['nombre'] }} registra una densidad relativa baja
                            ({{ number_format($bottom['kg_ha'], 1, ',', '.') }} kg/ha). Conviene evaluar
                            la optimización de su ruta de recolección.
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="foot">
            <div class="foot-left"><span class="foot-brand">Infinito Reciclaje</span> · Gestión Integral de Residuos</div>
            <div class="foot-right">{{ $organizacion }} · {{ $periodo }}</div>
        </div>
    </div>
</div>
@endif {{-- zonasConHa --}}

@endif {{-- !esAlerta --}}

{{-- ═══════════ CIERRE ═══════════ --}}
<div class="page cover">
    <div class="cover-stripe"></div>

    <div class="cover-body" style="padding-top: 22mm;">
        <div class="cover-brand" style="margin-bottom: 12mm;">
            <div class="cover-brand-dot"></div>
            Infinito Reciclaje
        </div>

        <div class="cover-eyebrow">Agradecimiento</div>
        <div class="cover-title" style="font-size: 42px; margin-bottom: 6mm;">Gracias</div>
        <div class="cover-accent"></div>

        <p style="font-size: 10px; color: var(--p-200); line-height: 1.75; max-width: 200mm; margin-bottom: 9mm;">
            @if(!empty($ai['analisis']))
                {{ $ai['analisis'] }}
            @else
                Agradecemos la confianza depositada en Infinito Reciclaje durante {{ $periodo }}.
                Este informe refleja el trabajo comprometido de todo el equipo operativo.
                Seguimos trabajando para mejorar la eficiencia, la trazabilidad y el impacto ambiental
                de cada jornada de recolección.
            @endif
        </p>

        <div class="closing-grid">
            <div class="closing-card">
                <div class="closing-num">01</div>
                <div class="closing-card-title">Seguimiento continuo</div>
                <div class="closing-card-desc">Cada pesaje queda registrado en la plataforma digital con trazabilidad completa. Los datos de este informe son auditables en tiempo real.</div>
            </div>
            <div class="closing-card">
                <div class="closing-num">02</div>
                <div class="closing-card-title">Próximo informe</div>
                <div class="closing-card-desc">El informe correspondiente al mes siguiente será generado y enviado automáticamente a los destinatarios configurados en el sistema.</div>
            </div>
            <div class="closing-card">
                <div class="closing-num">03</div>
                <div class="closing-card-title">Consultas y soporte</div>
                <div class="closing-card-desc">Para consultas sobre este informe o sobre el servicio, el equipo de Infinito Reciclaje está disponible a través de los canales habituales de contacto.</div>
            </div>
        </div>
    </div>

    <div class="cover-footer">
        <div>
            <div class="cover-muni">{{ $organizacion }}</div>
            <div class="cover-sub">Sistema de Balanza Digital · Generado el {{ $generado }}</div>
        </div>
        <div class="cover-badge">Gestión Integral de Residuos</div>
    </div>
</div>

</body>
</html>
