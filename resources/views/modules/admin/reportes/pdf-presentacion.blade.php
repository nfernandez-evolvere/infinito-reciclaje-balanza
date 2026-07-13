<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Reporte de Pesajes</title>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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
        font-size: 12px;
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
        font-size: 9.5px;
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
        font-size: 9px;
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
        font-size: 18px;
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
        font-size: 14.5px;
        font-weight: 600;
        color: var(--p-100);
        margin-bottom: 1mm;
    }

    .cover-sub {
        font-size: 9.5px;
        color: var(--p-400);
        letter-spacing: 0.02em;
    }

    .cover-badge {
        background: oklch(from var(--p-700) l c h / 30%);
        border: 1px solid var(--p-700);
        border-radius: 5px;
        padding: 2.5mm 4.5mm;
        font-size: 9px;
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
        font-size: 8px;
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
        font-size: 9.5px;
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

    /* Bajada explicativa bajo el título de cada sección */
    .slide-desc {
        font-size: 13px;
        color: var(--n-600);
        line-height: 1.6;
        max-width: 220mm;
        margin-bottom: 5mm;
    }

    /* ════════════════════════════════════════
       QUIÉNES SOMOS
    ════════════════════════════════════════ */
    .intro-text {
        font-size: 12.5px;
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
        font-size: 13px;
        font-weight: 700;
        color: var(--p-900);
        margin-bottom: 2.5mm;
    }

    .svc-desc {
        font-size: 11px;
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
        --kc-bg:       var(--p-600);
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
        font-size: 9.5px;
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
        font-size: 32px;
        font-weight: 800;
        line-height: 1;
        color: var(--kc-value);
        letter-spacing: -0.03em;
    }

    .kpi-unit {
        font-size: 12px;
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
        font-size: 8px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: var(--p-700);
        margin-bottom: 2mm;
    }

    .insight.amber .insight-label { color: oklch(0.560 0.168 95); }
    .insight.red   .insight-label { color: var(--red-600); }

    .insight-text {
        font-size: 11.5px;
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
        font-size: 8.5px;
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
        font-size: 10.5px;
        font-weight: 700;
        color: var(--n-600);
        margin-bottom: 1px;
        white-space: nowrap;
    }

    .bar-bar {
        position: relative;
        width: 100%;
        border-radius: 2px 2px 0 0;
        min-height: 2px;
        background: var(--p-600);
    }

    .bar-bar.low { background: var(--red-600); }

    /* Valor dentro del tope de la barra (barras altas) */
    .bar-val-in {
        position: absolute;
        top: 1.5px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 10.5px;
        font-weight: 700;
        color: #fff;
        white-space: nowrap;
        text-shadow: 0 0 2px rgba(0,0,0,0.55), 0 1px 1px rgba(0,0,0,0.45);
    }

    .bar-label {
        font-size: 8px;
        color: var(--n-500);
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
        font-size: 9.5px;
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
        font-size: 10.5px;
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
        display: flex;
        align-items: center;
        height: 6mm;
        background: var(--n-100);
        border-radius: 3px;
        overflow: hidden;
    }

    .hbar-fill {
        height: 100%;
        border-radius: 3px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding: 0 2mm;
        background: var(--p-700);
        flex-shrink: 0;
    }

    .hbar-fill-val {
        font-size: 10px;
        font-weight: 700;
        color: #fff;
        white-space: nowrap;
    }

    /* Valor afuera de la barra cuando es muy corta para contenerlo */
    .hbar-out-val {
        font-size: 10px;
        font-weight: 700;
        color: var(--p-950);
        white-space: nowrap;
        padding-left: 2mm;
    }

    .hbar-after {
        font-size: 10px;
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
        font-size: 10px;
    }

    table.data thead th {
        background: var(--p-950);
        color: var(--p-200);
        padding: 3.5mm 4mm;
        text-align: left;
        font-weight: 600;
        font-size: 8.5px;
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
        font-size: 8.5px;
        font-weight: 600;
    }

    /* ════════════════════════════════════════
       LEYENDA
    ════════════════════════════════════════ */
    .legend {
        display: flex;
        align-items: center;
        gap: 3mm 6mm;
        margin-bottom: 5mm;
        flex-wrap: wrap;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 1.5mm;
        font-size: 9px;
        color: var(--n-500);
        white-space: nowrap;
    }

    /* Escala de toneladas (leyenda de la tabla por zona) — agrandada y rotulada */
    .legend-scale-label {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--n-600);
    }

    .legend .legend-item {
        font-size: 11px;
        font-weight: 500;
        color: var(--n-700);
        gap: 2mm;
    }

    .legend .dot {
        width: 13px;
        height: 13px;
        border-radius: 3px;
    }

    /* Guía de continuación cuando una sección sigue en la página siguiente */
    .slide-continued {
        font-size: 9.5px;
        font-style: italic;
        color: var(--n-400);
        margin-top: 1.5mm;
    }

    /* ════════════════════════════════════════
       MAPA DE CALOR (Leaflet + ranking)
    ════════════════════════════════════════ */
    .map-grid {
        display: grid;
        grid-template-columns: 62fr 38fr;
        gap: 6mm;
        align-items: start;
    }

    .map-frame {
        border: 1px solid var(--n-200);
        border-radius: 10px;
        overflow: hidden;
        background: var(--n-100);
    }

    .pdf-map {
        width: 100%;
        height: 118mm;
    }

    .map-legend {
        display: flex;
        align-items: center;
        gap: 3mm 4mm;
        flex-wrap: wrap;
        margin-top: 3.5mm;
    }

    .map-legend .legend-scale {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--n-500);
    }

    .map-legend .swatch {
        display: inline-block;
        width: 11px; height: 11px;
        border-radius: 2px;
        margin-right: 1.5mm;
        vertical-align: middle;
        flex-shrink: 0;
    }

    .map-legend .legend-item { font-size: 10px; }

    /* Ranking lateral de zonas (réplica de la lista de la web) */
    .map-rank-title { font-size: 12.5px; font-weight: 700; color: var(--p-950); }
    .map-rank-sub   { font-size: 10px; color: var(--n-400); margin-bottom: 2.5mm; }

    .rank-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 2.5mm;
        border: 1px solid var(--n-200);
        border-radius: 6px;
        padding: 1.3mm 2.5mm;
        margin-bottom: 1.2mm;
    }

    .rank-left { display: flex; align-items: center; gap: 2mm; min-width: 0; }

    .rank-dot {
        width: 11px; height: 11px;
        border-radius: 2px;
        flex-shrink: 0;
    }

    .rank-name { font-size: 11px; font-weight: 600; color: var(--p-950); display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .rank-sub  { font-size: 9.5px; color: var(--n-400); }
    .rank-val  { font-size: 11px; font-weight: 700; color: var(--p-950); white-space: nowrap; font-variant-numeric: tabular-nums; }

    .rank-pill {
        font-size: 8px;
        font-weight: 600;
        color: var(--n-500);
        background: var(--n-100);
        border-radius: 10px;
        padding: 0.3mm 1.5mm;
        margin-left: 1.5mm;
    }

    .rank-more { font-size: 10px; color: var(--n-400); margin-top: 1.5mm; }

    /* Etiqueta permanente con el nombre de la zona dentro del polígono */
    .zona-label.leaflet-tooltip {
        background: transparent;
        border: none;
        box-shadow: none;
        padding: 0;
        color: #ffffff;
        font-weight: 600;
        font-size: 13px;
        line-height: 1.1;
        white-space: nowrap;
        text-shadow:
            -1px -1px 0 rgba(0,0,0,0.55), 1px -1px 0 rgba(0,0,0,0.55),
            -1px  1px 0 rgba(0,0,0,0.55), 1px  1px 0 rgba(0,0,0,0.55),
             0 0 3px rgba(0,0,0,0.4);
    }
    .zona-label.leaflet-tooltip::before { display: none; }

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

    .foot-left { font-size: 8.5px; color: var(--n-400); }
    .foot-brand { font-weight: 600; color: var(--p-700); }
    .foot-right { font-size: 8.5px; color: var(--n-400); text-align: right; }

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
        font-size: 12px;
        font-weight: 700;
        color: var(--p-100);
        margin-bottom: 2mm;
    }

    .closing-card-desc {
        font-size: 10px;
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
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: var(--p-950);
    }

    .alerta-tipo-count {
        margin-left: auto;
        font-size: 9px;
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
        font-size: 9px;
        color: var(--n-500);
        white-space: nowrap;
        flex-shrink: 0;
        padding-top: 0.5mm;
        width: 16mm;
    }

    .alerta-body { flex: 1; min-width: 0; }

    .alerta-titulo {
        font-size: 10px;
        font-weight: 600;
        color: var(--p-950);
        margin-bottom: 1mm;
    }

    .alerta-desc {
        font-size: 9px;
        color: var(--n-600);
        line-height: 1.5;
    }

    .alerta-zona {
        font-size: 8.5px;
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
        font-size: 9px;
        color: var(--n-600);
        line-height: 1.4;
    }

    /* ════════════════════════════════════════
       PÁGINA ANÁLISIS ESTRATÉGICO (AI)
    ════════════════════════════════════════ */
    .ai-page {
        background: var(--p-950);
        min-height: 210mm;
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
    }

    .ai-page::before {
        content: '';
        position: absolute;
        top: -30mm; right: -15mm;
        width: 90mm; height: 90mm;
        border-radius: 50%;
        background: var(--p-800);
        opacity: 0.4;
    }

    .ai-page::after {
        content: '';
        position: absolute;
        bottom: 10mm; left: -20mm;
        width: 70mm; height: 70mm;
        border-radius: 50%;
        background: var(--p-900);
        opacity: 0.5;
    }

    .ai-stripe {
        position: absolute;
        top: 0; right: 42mm;
        width: 5mm; height: 100%;
        background: var(--p-600);
        opacity: 0.4;
    }

    .ai-body {
        padding: 14mm 24mm 0;
        position: relative;
        z-index: 1;
        flex: 1;
    }

    .ai-header {
        margin-bottom: 9mm;
    }

    .ai-eyebrow {
        font-size: 8.5px;
        font-weight: 700;
        letter-spacing: 0.24em;
        text-transform: uppercase;
        color: var(--p-400);
        margin-bottom: 2mm;
    }

    .ai-title {
        font-size: 26px;
        font-weight: 800;
        color: #fff;
        letter-spacing: -0.025em;
        line-height: 1.05;
        margin-bottom: 3mm;
    }

    .ai-title span { color: var(--p-400); }

    .ai-rule {
        width: 20mm; height: 3px;
        background: linear-gradient(90deg, var(--p-500), var(--p-300));
        border-radius: 2px;
    }

    .ai-sections {
        display: flex;
        flex-direction: column;
        gap: 5mm;
    }

    .ai-section {
        display: flex;
        gap: 5mm;
        align-items: flex-start;
    }

    .ai-section-num {
        flex-shrink: 0;
        width: 10mm;
        font-size: 22px;
        font-weight: 800;
        color: var(--p-700);
        line-height: 1;
        letter-spacing: -0.03em;
        padding-top: 0.5mm;
    }

    .ai-section-content {
        flex: 1;
        border-left: 2px solid var(--p-800);
        padding-left: 4mm;
    }

    .ai-section-label {
        font-size: 8.5px;
        font-weight: 700;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: var(--p-500);
        margin-bottom: 2mm;
    }

    .ai-section-text {
        font-size: 11.5px;
        color: var(--p-100);
        line-height: 1.75;
    }

    .ai-badge {
        display: inline-flex;
        align-items: center;
        gap: 1.5mm;
        background: oklch(from var(--p-700) l c h / 20%);
        border: 1px solid var(--p-800);
        border-radius: 4px;
        padding: 1mm 2.5mm;
        font-size: 8px;
        font-weight: 600;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--p-400);
        margin-top: 3mm;
    }

    .ai-badge-dot {
        width: 4px; height: 4px;
        border-radius: 50%;
        background: var(--p-500);
    }

    /* ════════════════════════════════════════
       PÁGINA CIERRE — GRACIAS (claro)
    ════════════════════════════════════════ */
    .thank-page {
        background: #fff;
        min-height: 210mm;
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
    }

    /* Banda superior verde */
    .thank-stripe-top {
        height: 5mm;
        background: linear-gradient(90deg, var(--p-800), var(--p-500), var(--p-300));
        flex-shrink: 0;
        position: relative;
        z-index: 2;
    }

    /* Círculo decorativo grande — fondo derecha */
    .thank-deco-a {
        position: absolute;
        top: -18mm; right: -22mm;
        width: 110mm; height: 110mm;
        border-radius: 50%;
        background: var(--p-50);
    }

    /* Círculo decorativo mediano — izquierda centro */
    .thank-deco-b {
        position: absolute;
        top: 38mm; left: -28mm;
        width: 70mm; height: 70mm;
        border-radius: 50%;
        background: var(--p-50);
    }

    /* Anillo decorativo — derecha centro (solo borde) */
    .thank-deco-c {
        position: absolute;
        top: 50mm; right: 18mm;
        width: 55mm; height: 55mm;
        border-radius: 50%;
        border: 6mm solid var(--p-100);
        background: transparent;
    }

    /* Círculo pequeño sólido — bottom right */
    .thank-deco-d {
        position: absolute;
        bottom: 18mm; right: 14mm;
        width: 22mm; height: 22mm;
        border-radius: 50%;
        background: var(--p-100);
    }

    /* Línea vertical decorativa izquierda */
    .thank-deco-line {
        position: absolute;
        top: 0; left: 42mm;
        width: 1px; height: 100%;
        background: var(--p-100);
    }

    .thank-body {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 0 30mm 0 28mm;
        position: relative;
        z-index: 1;
    }

    .thank-eyebrow {
        font-size: 8.5px;
        font-weight: 700;
        letter-spacing: 0.26em;
        text-transform: uppercase;
        color: var(--p-600);
        margin-bottom: 4mm;
    }

    .thank-title {
        font-size: 82px;
        font-weight: 800;
        color: var(--p-900);
        letter-spacing: -0.045em;
        line-height: 0.88;
        margin-bottom: 7mm;
    }

    .thank-rule {
        width: 26mm;
        height: 3px;
        background: linear-gradient(90deg, var(--p-700), var(--p-300));
        border-radius: 2px;
        margin-bottom: 7mm;
    }

    .thank-org {
        font-size: 17px;
        font-weight: 300;
        color: var(--n-500);
        letter-spacing: 0.01em;
        margin-bottom: 5mm;
    }

    .thank-caption {
        font-size: 11.5px;
        color: var(--n-400);
        line-height: 1.75;
        max-width: 140mm;
    }

    .thank-footer {
        background: var(--p-950);
        padding: 5mm 28mm;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
        position: relative;
        z-index: 2;
    }

    .thank-footer-org {
        font-size: 12px;
        font-weight: 600;
        color: var(--p-100);
        margin-bottom: 1mm;
    }

    .thank-footer-sub {
        font-size: 9px;
        color: var(--p-500);
        letter-spacing: 0.02em;
    }

    .thank-footer-badge {
        background: oklch(from var(--p-700) l c h / 20%);
        border: 1px solid var(--p-700);
        border-radius: 5px;
        padding: 2.5mm 4.5mm;
        font-size: 8.5px;
        font-weight: 600;
        letter-spacing: 0.1em;
        color: var(--p-300);
        text-transform: uppercase;
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

    $tipoDesc = [
        'peso_fuera_rango'        => 'Pesajes por fuera del rango habitual configurado para el tipo de vehículo.',
        'volumen_diario_atipico'  => 'Días cuyo volumen total se aparta del patrón del período.',
        'gap_registro'            => 'Lapsos sin pesajes durante un turno activo.',
        'frecuencia_zona_atipica' => 'Zonas con una frecuencia de recolección distinta a su patrón habitual.',
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

@php
    // Browsershot renderiza HTML sin base URL: las rutas relativas no resuelven.
    // Embebemos el logo como data URI para que aparezca siempre en el PDF.
    $logoPath = public_path('favicon.png');
    $logoUri = is_file($logoPath)
        ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
        : null;
@endphp

{{-- ═══════════ PORTADA ═══════════ --}}
<div class="page cover">
    <div class="cover-stripe"></div>
    <div class="cover-body">
        <div class="cover-brand">
            @if ($logoUri)
                <img src="{{ $logoUri }}" alt="" style="width: 12mm; height: 12mm; object-fit: contain; flex-shrink: 0;">
            @else
                <div class="cover-brand-dot"></div>
            @endif
            Infinito Reciclaje
        </div>
        <div class="cover-label">{{ $esAlerta ? 'Reporte de alertas de peso' : 'Reporte mensual de gestión' }}</div>
        <div class="cover-title">
            @if ($esAlerta)
                Reporte<br>de <span>Alertas</span><br>{{ $periodo }}
            @else
                Reporte<br>de <span>Pesajes</span><br>{{ $periodo }}
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
            <p class="slide-desc">Eventos detectados automáticamente durante el período. Cada tarjeta agrupa un tipo de alerta; el detalle de cada una está en las páginas siguientes.</p>

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
@php
    // Las filas de alerta son altas (título + descripción + zona): entran ~10 por
    // hoja A4 apaisada. Reparto balanceado para que ninguna página desborde (y la
    // continuación respete su margen superior) sin dejar una alerta huérfana.
    $alertasTotalChunks = max(1, (int) ceil($grupo->count() / 10));
    $alertasChunks      = $grupo->chunk(max(1, (int) ceil($grupo->count() / $alertasTotalChunks)));
@endphp
@foreach($alertasChunks as $chunkIdx => $chunk)
<div class="page">
    <div class="slide-wrap">
        <div class="slide-head">
            <div class="slide-header-row">
                <div>
                    <div class="slide-eyebrow">Alertas — {{ $tipoNombre }}</div>
                    <div class="slide-title">
                        {{ $tipoNombre }}
                        @if($alertasChunks->count() > 1)
                            <span style="font-size:14.5px;color:oklch(0.708 0 0);font-weight:400;">({{ $chunkIdx + 1 }}/{{ $alertasChunks->count() }})</span>
                        @endif
                    </div>
                </div>
                <div class="slide-meta">{{ $grupo->count() }} {{ $grupo->count() === 1 ? 'alerta' : 'alertas' }} · {{ $periodo }}</div>
            </div>
            <div class="slide-rule"></div>
        </div>

        <div class="slide-content">
            <p class="slide-desc">{{ $tipoDesc[$tipoKey] ?? '' }}</p>
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
                        <span style="font-size:8.5px;color:oklch(0.592 0.153 144);font-weight:600;">Leída</span>
                    @else
                        <span style="font-size:8.5px;color:var(--amber-500);font-weight:600;">Sin leer</span>
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
            <p class="slide-desc">Indicadores principales del período: total recolectado, viajes registrados y promedios por jornada operativa.</p>
            <div class="kpi-grid">
                {{-- Toneladas netas — default (verde profundo) — icon: package --}}
                <div class="kpi-card v-mid">
                    <div class="kpi-body">
                        <div class="kpi-label">Toneladas netas</div>
                        <div class="kpi-value">{{ number_format($kpis['toneladas'], 0, ',', '.') }}</div>
                        <div class="kpi-unit">toneladas recolectadas</div>
                    </div>
                </div>

                {{-- Total viajes — v-mid (verde medio) — icon: scale --}}
                <div class="kpi-card v-mid">
                    <div class="kpi-body">
                        <div class="kpi-label">Total viajes</div>
                        <div class="kpi-value">{{ number_format($kpis['total'], 0, ',', '.') }}</div>
                        <div class="kpi-unit">pesajes registrados</div>
                    </div>
                </div>

                {{-- Días operativos — v-mid (verde medio) — icon: calendar-days --}}
                <div class="kpi-card v-mid">
                    <div class="kpi-body">
                        <div class="kpi-label">Días operativos</div>
                        <div class="kpi-value">{{ $kpis['dias_op'] }}</div>
                        <div class="kpi-unit">de {{ $kpis['dias_rango'] }} días del período</div>
                    </div>
                </div>

                {{-- Promedio diario — v-mid (verde medio) — icon: trending-up --}}
                <div class="kpi-card v-mid">
                    <div class="kpi-body">
                        <div class="kpi-label">Promedio diario</div>
                        <div class="kpi-value">{{ number_format($kpis['promedio_ton_dia'], 1, ',', '.') }}</div>
                        <div class="kpi-unit">toneladas / día operativo</div>
                    </div>
                </div>

                {{-- Promedio por viaje — v-mid (verde medio) — icon: truck --}}
                <div class="kpi-card v-mid">
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
                    @php $diasSin = max($kpis['dias_rango'] - $kpis['dias_op'], 0); @endphp
                    En {{ $periodo }} se registraron <strong>{{ number_format($kpis['total'], 0, ',', '.') }} viajes</strong>
                    por <strong>{{ number_format($kpis['toneladas'], 1, ',', '.') }} t netas</strong>, con actividad en
                    <strong>{{ $kpis['dias_op'] }} de {{ $kpis['dias_rango'] }} días</strong>
                    @if($diasSin > 0)(<strong>{{ $diasSin }}</strong> {{ $diasSin === 1 ? 'jornada sin registros' : 'jornadas sin registros' }}) @endif
                    y un promedio de {{ number_format($kpis['promedio_ton_dia'], 1, ',', '.') }} t por jornada operativa.
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
@php
    // Paginar el gráfico en chunks de <=15 barras para que no se compriman.
    // Reparto balanceado: 31 días → 3 páginas de 11/11/9, no 15/15/1.
    $evTotalChunks = max(1, (int) ceil(count($evDatos) / 15));
    $evChunkSize   = max(1, (int) ceil(count($evDatos) / $evTotalChunks));
    $evChunks      = array_chunk($evDatos, $evChunkSize);
    $evTotalChunks = count($evChunks);
@endphp
@foreach($evChunks as $chunkIdx => $evChunk)
<div class="page">
    <div class="slide-wrap">
        <div class="slide-head">
            <div class="slide-header-row">
                <div>
                    <div class="slide-eyebrow">Tendencia</div>
                    <div class="slide-title">
                        Evolución Diaria de Toneladas
                        @if($evTotalChunks > 1)
                            <span style="font-size: 14.5px; color: oklch(0.708 0 0); font-weight: 400;">({{ $chunkIdx + 1 }}/{{ $evTotalChunks }})</span>
                        @endif
                    </div>
                </div>
                <div class="slide-meta">{{ $periodo }}</div>
            </div>
            <div class="slide-rule"></div>
        </div>

        <div class="slide-content">
            @if($chunkIdx === 0)
            <p class="slide-desc">Toneladas netas recolectadas por día. La línea de promedio marca la media de las jornadas con actividad; en rojo, los días por debajo del 30% de ese promedio.</p>
            {{-- KPIs del período: solo en la primera página del gráfico --}}
            <div class="kpi-grid-4">
                {{-- Promedio — v-mid (verde medio) — icon: bar-chart-2 --}}
                <div class="kpi-card v-mid">
                    <div class="kpi-body">
                        <div class="kpi-label">Promedio</div>
                        <div class="kpi-value">{{ number_format($evolucion['promedio'], 1, ',', '.') }}</div>
                        <div class="kpi-unit">toneladas / día</div>
                    </div>
                </div>

                {{-- Máximo — default (verde profundo) — icon: arrow-up --}}
                <div class="kpi-card v-mid">
                    <div class="kpi-body">
                        <div class="kpi-label">Máximo</div>
                        <div class="kpi-value">{{ number_format($evolucion['maximo'], 1, ',', '.') }}</div>
                        <div class="kpi-unit">toneladas en un día</div>
                    </div>
                </div>

                {{-- Mínimo — v-slate (neutral) — icon: arrow-down --}}
                <div class="kpi-card v-mid">
                    <div class="kpi-body">
                        <div class="kpi-label">Mínimo</div>
                        <div class="kpi-value">{{ number_format($evolucion['minimo'], 1, ',', '.') }}</div>
                        <div class="kpi-unit">toneladas en un día</div>
                    </div>
                </div>

                {{-- Días con datos — v-blue (temporal) — icon: calendar-days --}}
                <div class="kpi-card v-mid">
                    <div class="kpi-body">
                        <div class="kpi-label">Días con datos</div>
                        <div class="kpi-value">{{ $diasConDatos }}</div>
                        <div class="kpi-unit">días con actividad</div>
                    </div>
                </div>
            </div>
            @endif

            <div class="chart-wrap">
                <div class="chart-label">Toneladas por día @if($evTotalChunks > 1)· {{ $evChunk[0]['fecha'] }} a {{ $evChunk[count($evChunk) - 1]['fecha'] }}@endif</div>
                <div class="bar-chart-vertical">
                    @php $showEvery = max(1, (int) ceil(count($evChunk) / 20)); @endphp
                    @foreach($evChunk as $i => $d)
                    @php
                        $pct   = $evMax > 0 ? ($d['toneladas'] / $evMax) * 100 : 0;
                        $isLow = $d['toneladas'] > 0 && $d['toneladas'] < ($evAvg * 0.3);
                        $showL = ($i % $showEvery === 0);
                    @endphp
                    <div class="bar-col">
                        @if($d['toneladas'] > 0 && $pct < 16)
                            <div class="bar-val">{{ number_format($d['toneladas'], 1) }}</div>
                        @endif
                        <div class="bar-bar {{ $isLow ? 'low' : '' }}"
                             style="height: {{ max($pct, $d['toneladas'] > 0 ? 1.5 : 0) }}%;">
                            @if($d['toneladas'] > 0 && $pct >= 16)
                                <div class="bar-val-in">{{ number_format($d['toneladas'], 1) }}</div>
                            @endif
                        </div>
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
@endforeach

{{-- ═══════════ POR TIPO DE VEHÍCULO ═══════════ --}}
@php
    // Mismo criterio que la tabla por zona: paginamos para que cada .page entre
    // en una hoja A4 apaisada. La tabla (columna derecha) es la más alta y manda.
    //  · 1ª página: bajada + gráfico + tabla.
    //  · continuación: la tabla a ancho completo.
    //  · el "Total" (tfoot) va en la última y necesita ~2 filas libres.
    // Las flotas reales tienen pocos tipos → con ≤11 entra todo en una página
    // (salida sin cambios); el mecanismo solo pagina si hubiera muchos tipos.
    $vehArray  = $vehiculos->values()->all();
    $vehN      = count($vehArray);
    $vCapPrim  = 13;
    $vCapResto = 14;
    $vCapTotal = 2;

    $vehChunks = [];
    $i = 0;
    do {
        $cap = empty($vehChunks) ? $vCapPrim : $vCapResto;
        if ($vehN - $i <= $cap) {              // última página → reservar lugar para el total
            $cap = max(1, $cap - $vCapTotal);
        }
        $vehChunks[] = ['offset' => $i, 'rows' => array_slice($vehArray, $i, $cap)];
        $i += $cap;
    } while ($i < $vehN);
    $vehTotalSlides = count($vehChunks);
@endphp

@foreach($vehChunks as $vehIdx => $vehChunk)
@php $vehOffset = $vehChunk['offset']; $vehRows = $vehChunk['rows']; @endphp
<div class="page">
    <div class="slide-wrap">
        <div class="slide-head">
            <div class="slide-header-row">
                <div>
                    <div class="slide-eyebrow">Composición de flota</div>
                    <div class="slide-title">
                        Por Tipo de Vehículo
                        @if($vehTotalSlides > 1)
                            <span style="font-size: 14.5px; color: oklch(0.708 0 0); font-weight: 400;">({{ $vehIdx + 1 }}/{{ $vehTotalSlides }})</span>
                        @endif
                    </div>
                    @if($vehIdx > 0)
                        <div class="slide-continued">Continuación de la página anterior</div>
                    @endif
                </div>
                <div class="slide-meta">{{ $periodo }}</div>
            </div>
            <div class="slide-rule"></div>
        </div>

        <div class="slide-content">
            @if($vehIdx === 0)
            <p class="slide-desc">Reparto de viajes y toneladas según el tipo de vehículo. Muestra qué parte de la flota concentra la recolección.</p>
            @endif
            <div class="{{ $vehIdx === 0 ? 'two-col two-col-4-6' : '' }}">
                @if($vehIdx === 0)
                <div class="chart-wrap" style="padding: 4.5mm;">
                    <div class="chart-label">Viajes por tipo</div>
                    <div class="hbar-chart">
                        @foreach($vehRows as $li => $v)
                        @php
                            $vi    = $vehOffset + $li;
                            $pct   = $vMax > 0 ? round(($v['viajes'] / $vMax) * 100) : 0;
                            $color = $vColors[$vi % count($vColors)];
                        @endphp
                        <div class="hbar-row">
                            <div class="hbar-label">{{ $v['nombre'] }}</div>
                            <div class="hbar-track">
                                <div class="hbar-fill" style="width: {{ $pct }}%; background: {{ $color }};">
                                    @if($pct >= 18)
                                    <span class="hbar-fill-val">{{ number_format($v['viajes'], 0, ',', '.') }}</span>
                                    @endif
                                </div>
                                @if($pct < 18)
                                <span class="hbar-out-val">{{ number_format($v['viajes'], 0, ',', '.') }}</span>
                                @endif
                            </div>
                            <div class="hbar-after">{{ number_format($v['porcentaje'], 1) }}%</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div>
                    <table class="data">
                        <thead>
                            <tr>
                                <th>Tipo de vehículo</th>
                                <th class="r">Viajes</th>
                                <th class="r">Toneladas</th>
                                <th class="r">% del total</th>
                                <th class="r">kg/viaje</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vehRows as $li => $v)
                            @php $vi = $vehOffset + $li; @endphp
                            <tr>
                                <td class="strong">
                                    <span class="dot" style="background: {{ $vColors[$vi % count($vColors)] }};"></span>
                                    {{ $v['nombre'] }}
                                </td>
                                <td class="num">{{ number_format($v['viajes'], 0, ',', '.') }}</td>
                                <td class="num">{{ number_format($v['toneladas'], 1, ',', '.') }} t</td>
                                <td class="muted">{{ number_format($v['porcentaje'], 1, ',', '.') }}%</td>
                                <td class="num">{{ number_format($v['kg_viaje'], 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        @if($vehIdx === $vehTotalSlides - 1)
                        <tfoot>
                            <tr>
                                <td>Total</td>
                                <td class="r">{{ number_format($vehiculos->sum('viajes'), 0, ',', '.') }}</td>
                                <td class="r">{{ number_format($vehiculos->sum('toneladas'), 1, ',', '.') }} t</td>
                                <td class="r">100%</td>
                                <td class="r">—</td>
                            </tr>
                        </tfoot>
                        @endif
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
@endforeach

{{-- ═══════════ POR ZONA ═══════════ --}}
@php
    // Repartimos las zonas en chunks para que cada .page entre en una sola hoja
    // física A4 apaisada (210mm) y no desborde. Si desbordara, Chromium parte la
    // .page y la continuación arranca pegada al borde superior, sin margen.
    //  · 1ª página: lleva además la bajada + la leyenda de escala → entra menos.
    //  · páginas de continuación: solo el encabezado de la tabla.
    //  · el "Total general" (tfoot) va en la última y necesita ~2 filas libres.
    $zonasArray = $zonas->values()->all();
    $n          = count($zonasArray);
    $capPrimera = 10;
    $capResto   = 13;
    $capTotal   = 2;

    $zonasChunks = [];
    $i = 0;
    do {
        $cap = empty($zonasChunks) ? $capPrimera : $capResto;
        if ($n - $i <= $cap) {              // última página → reservar lugar para el total
            $cap = max(1, $cap - $capTotal);
        }
        $zonasChunks[] = array_slice($zonasArray, $i, $cap);
        $i += $cap;
    } while ($i < $n);

    $totalSlides = count($zonasChunks);
@endphp

@foreach($zonasChunks as $chunkIdx => $chunk)
<div class="page">
    <div class="slide-wrap">
        <div class="slide-head">
            <div class="slide-header-row">
                <div>
                    <div class="slide-eyebrow">Distribución territorial</div>
                    <div class="slide-title">
                        Toneladas Netas por Zona y Turno
                        @if($totalSlides > 1)
                            <span style="font-size: 14.5px; color: oklch(0.708 0 0); font-weight: 400;">({{ $chunkIdx + 1 }}/{{ $totalSlides }})</span>
                        @endif
                    </div>
                    @if($chunkIdx > 0)
                        <div class="slide-continued">Continuación de la página anterior</div>
                    @endif
                </div>
                <div class="slide-meta">{{ $periodo }}</div>
            </div>
            <div class="slide-rule"></div>
        </div>

        <div class="slide-content">
            @if($chunkIdx === 0)
            <p class="slide-desc">Volumen recolectado por zona y turno: una zona puede ocupar varias filas, una por turno. Incluye kg por viaje, por hectárea y por habitante. El color indica el rango de toneladas de cada fila.</p>
            <div class="legend">
                <span class="legend-scale-label">Escala · toneladas netas</span>
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
                        <th class="r">kg/viaje</th>
                        <th class="r">% del total</th>
                        <th class="r">kg/ha</th>
                        <th class="r">kg/hab</th>
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
                        <td class="num" style="color: {{ $dotColor }};">{{ number_format($zona['toneladas'], 1, ',', '.') }} t</td>
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
                        <td class="r">{{ number_format($zonas->sum('toneladas'), 1, ',', '.') }} t</td>
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

{{-- ═══════════ MAPAS DE CALOR (choropleth por métrica) ═══════════ --}}
@php
    $mapaZonas  = $reporte['mapaZonas'] ?? collect();
    $mapaConGeo = $mapaZonas->filter(fn ($z) => $z['tiene_geometria'] ?? false);

    $mapasMetricas = [
        ['metric' => 'toneladas',  'eyebrow' => 'Concentración territorial', 'desc' => 'Toneladas netas recolectadas por zona, sumando todos sus turnos. Las áreas más oscuras concentran mayor volumen de recolección.'],
        ['metric' => 'pesajes',    'eyebrow' => 'Frecuencia de recolección', 'desc' => 'Cantidad de viajes registrados por zona en el período.'],
        ['metric' => 'per_capita', 'eyebrow' => 'Generación por habitante',  'desc' => 'Kilos recolectados por habitante (kg/hab). Solo se colorean las zonas con población cargada.'],
        ['metric' => 'densidad',   'eyebrow' => 'Intensidad por superficie', 'desc' => 'Kilos recolectados por hectárea (kg/ha). Solo se colorean las zonas con superficie cargada.'],
    ];
@endphp

@if($mapaConGeo->isNotEmpty())
@inject('choropleth', 'App\Services\ChoroplethMapService')
@foreach($mapasMetricas as $mm)
@php
    $mapa = $choropleth->mapData($mapaZonas, $mm['metric']);
    $mapId = 'mapa-'.$mm['metric'];
    $rankTop = array_slice($mapa['filas'], 0, 11);
    $rankRest = count($mapa['filas']) - count($rankTop);
@endphp
@if($mapa['hayMapa'])
<div class="page">
    <div class="slide-wrap">
        <div class="slide-head">
            <div class="slide-header-row">
                <div>
                    <div class="slide-eyebrow">{{ $mm['eyebrow'] }}</div>
                    <div class="slide-title">Mapa de Calor · {{ $mapa['metrica']['label'] }}</div>
                </div>
                <div class="slide-meta">{{ $periodo }}</div>
            </div>
            <div class="slide-rule"></div>
        </div>

        <div class="slide-content">
            <p class="slide-desc">{{ $mm['desc'] }}</p>

            <div class="map-grid">
                {{-- Mapa Leaflet (calles OSM + zonas coloreadas) + leyenda --}}
                <div>
                    <div class="map-frame">
                        <div class="pdf-map" id="{{ $mapId }}" data-choropleth="{{ $mapId }}-data"></div>
                    </div>
                    <script type="application/json" id="{{ $mapId }}-data">@json($mapa['mapa'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)</script>

                    <div class="map-legend">
                        <span class="legend-scale">Escala · {{ $mapa['metrica']['label'] }}@if($mapa['metrica']['unidad'] !== 'viajes') ({{ $mapa['metrica']['unidad'] }})@endif</span>
                        @foreach($mapa['buckets'] as $b)
                        <span class="legend-item"><span class="swatch" style="background: {{ $b['color'] }};"></span>{{ $b['label'] }}</span>
                        @endforeach
                        <span class="legend-item"><span class="swatch" style="background: #cbd5e1;"></span>Sin actividad</span>
                    </div>
                </div>

                {{-- Ranking de zonas por la métrica (réplica de la lista de la web) --}}
                <div>
                    <div class="map-rank-title">Ranking de zonas</div>
                    <div class="map-rank-sub">Total por zona, ordenado por {{ mb_strtolower($mapa['metrica']['label']) }}.</div>
                    @foreach($rankTop as $f)
                    <div class="rank-row">
                        <div class="rank-left">
                            <span class="rank-dot" style="background: {{ $f['color'] }};"></span>
                            <div style="min-width:0;">
                                <div class="rank-name">{{ $f['nombre'] }}@unless($f['tiene_geometria'])<span class="rank-pill">sin área</span>@endunless</div>
                                <div class="rank-sub">{{ $f['sub'] }}</div>
                            </div>
                        </div>
                        <span class="rank-val">{{ $f['valor'] }}</span>
                    </div>
                    @endforeach
                    @if($rankRest > 0)
                    <div class="rank-more">y {{ $rankRest }} {{ $rankRest === 1 ? 'zona más' : 'zonas más' }}…</div>
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
@endif
@endforeach
@endif

{{-- ═══════════ DENSIDAD kg/ha ═══════════ --}}
@if($zonasConHa->isNotEmpty())
@php
    // Mismo criterio que la tabla por zona: paginamos las barras para que cada
    // .page entre en una sola hoja A4 apaisada y, si desbordara, la continuación
    // respete el margen superior en vez de arrancar pegada al borde.
    //  · 1ª página: lleva la bajada + los insights (columna derecha) → entra menos.
    //  · páginas de continuación: solo las barras restantes, a ancho completo.
    // Con el tope actual ($zonasConHa->take(15)) entra todo en una página: la
    // salida no cambia, pero el mecanismo queda listo si se sube ese tope.
    $haArray   = $zonasConHa->all();
    $haN       = count($haArray);
    $haPrimera = 15;
    $haResto   = 20;

    $haChunks = [];
    $i = 0;
    do {
        $cap = empty($haChunks) ? $haPrimera : $haResto;
        $haChunks[] = array_slice($haArray, $i, $cap);
        $i += $cap;
    } while ($i < $haN);
    $haTotalSlides = count($haChunks);

    $topZonas = $zonasConHa->take(3)->pluck('nombre')->join(', ');
    $bottom   = $zonasConHa->last();
    $topVal   = $zonasConHa->first()['kg_ha'] ?? 0;
@endphp

@foreach($haChunks as $haIdx => $haChunk)
<div class="page">
    <div class="slide-wrap">
        <div class="slide-head">
            <div class="slide-header-row">
                <div>
                    <div class="slide-eyebrow">Intensidad de generación</div>
                    <div class="slide-title">
                        Densidad por Hectárea
                        @if($haTotalSlides > 1)
                            <span style="font-size: 14.5px; color: oklch(0.708 0 0); font-weight: 400;">({{ $haIdx + 1 }}/{{ $haTotalSlides }})</span>
                        @endif
                    </div>
                    @if($haIdx > 0)
                        <div class="slide-continued">Continuación de la página anterior</div>
                    @endif
                </div>
                <div class="slide-meta">Top {{ $zonasConHa->count() }} zonas · {{ $periodo }}</div>
            </div>
            <div class="slide-rule"></div>
        </div>

        <div class="slide-content">
            @if($haIdx === 0)
            <p class="slide-desc">Kilos recolectados por hectárea en cada zona. Mide la intensidad de generación según la superficie, sin depender del tamaño de la zona.</p>
            @endif
            <div class="{{ $haIdx === 0 ? 'two-col two-col-5-5' : '' }}">
                <div class="chart-wrap" style="padding: 4.5mm;">
                    <div class="chart-label">kg por hectárea</div>
                    <div class="hbar-chart">
                        @foreach($haChunk as $z)
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
                                    @if($pct >= 18)
                                    <span class="hbar-fill-val">{{ number_format($z['kg_ha'], 0) }}</span>
                                    @endif
                                </div>
                                @if($pct < 18)
                                <span class="hbar-out-val">{{ number_format($z['kg_ha'], 0) }}</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                @if($haIdx === 0)
                <div style="display: flex; flex-direction: column; gap: 4mm;">
                    <div class="insight">
                        <div class="insight-label">Zonas de mayor densidad</div>
                        <div class="insight-text">
                            {{ $topZonas }} concentran la mayor generación por hectárea del período. Una densidad alta
                            suele reflejar más frecuencia de recolección o mayor actividad sobre esa superficie.
                        </div>
                    </div>
                    @if($bottom && $bottom['kg_ha'] < $topVal * 0.2)
                    <div class="insight amber">
                        <div class="insight-label">Zona de baja densidad</div>
                        <div class="insight-text">
                            {{ $bottom['nombre'] }} registra <strong>{{ number_format($bottom['kg_ha'], 1, ',', '.') }} kg/ha</strong>,
                            muy por debajo del resto. Conviene verificar si la superficie y la frecuencia de recolección
                            cargadas para la zona reflejan su realidad antes de sacar conclusiones.
                        </div>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>

        <div class="foot">
            <div class="foot-left"><span class="foot-brand">Infinito Reciclaje</span> · Gestión Integral de Residuos</div>
            <div class="foot-right">{{ $organizacion }} · {{ $periodo }}</div>
        </div>
    </div>
</div>
@endforeach
@endif {{-- zonasConHa --}}

@endif {{-- !esAlerta --}}

{{-- ═══════════ ANÁLISIS ESTRATÉGICO (AI) ═══════════ --}}
@if(!$esAlerta && !empty($ai['analisis']))
@php
    $parrafos = array_values(array_filter(
        explode("\n\n", trim($ai['analisis'])),
        fn($p) => trim($p) !== ''
    ));
    $labels = ['Diagnóstico', 'Posibilidades de mejora', 'Recomendaciones'];

    // Empaquetamos los párrafos por alto estimado para que cada .ai-page entre en
    // una hoja A4 apaisada y, si desbordara, la continuación arranque en una página
    // nueva en vez de pegada al borde. El texto de IA está acotado (3 párrafos,
    // máx 1024 tokens), así que el caso normal entra en una sola página: solo
    // pagina si los párrafos fueran inusualmente largos.
    $aiCharsLinea = 115;   // ~caracteres por línea a 11.5px en el ancho de la sección
    $aiMmLinea    = 5.3;   // alto de línea (line-height 1.75)
    $aiOverheadMm = 11;    // label + gap por sección
    $aiBudgetMm   = 150;   // alto útil para secciones en una hoja

    $aiPaginas  = [];
    $actual     = [];
    $altoActual = 0;
    foreach ($parrafos as $idx => $p) {
        $lineas  = max(1, (int) ceil(mb_strlen($p) / $aiCharsLinea));
        $altoSec = $aiOverheadMm + $lineas * $aiMmLinea;
        if (! empty($actual) && $altoActual + $altoSec > $aiBudgetMm) {
            $aiPaginas[] = $actual;
            $actual = [];
            $altoActual = 0;
        }
        $actual[]    = ['idx' => $idx, 'texto' => $p];
        $altoActual += $altoSec;
    }
    if (! empty($actual)) {
        $aiPaginas[] = $actual;
    }
    $aiTotal = count($aiPaginas);
@endphp

@foreach($aiPaginas as $aiPagIdx => $aiPagina)
<div class="page ai-page">
    <div class="ai-stripe"></div>

    <div class="ai-body">
        <div class="ai-header">
            <div class="ai-eyebrow">Reporte · {{ $periodo }}</div>
            <div class="ai-title">
                Análisis <span>Estratégico</span>
                @if($aiTotal > 1)
                    <span style="font-size: 15px; color: var(--p-400); font-weight: 400; letter-spacing: 0;">({{ $aiPagIdx + 1 }}/{{ $aiTotal }})</span>
                @endif
            </div>
            <div class="ai-rule"></div>
        </div>

        <div class="ai-sections">
            @foreach($aiPagina as $sec)
            <div class="ai-section">
                <div class="ai-section-num">0{{ $sec['idx'] + 1 }}</div>
                <div class="ai-section-content">
                    <div class="ai-section-label">{{ $labels[$sec['idx']] ?? 'Análisis' }}</div>
                    <div class="ai-section-text">{{ $sec['texto'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    @if($aiPagIdx === $aiTotal - 1)
    <div class="cover-footer">
        <div class="ai-badge">
            <div class="ai-badge-dot"></div>
            Generado con IA · {{ $ai['modelo'] ?? 'Gemini' }}
        </div>
        <div>
            <div class="cover-muni">{{ $organizacion }}</div>
            <div class="cover-sub">Sistema de Balanza Digital · {{ $periodo }}</div>
        </div>
    </div>
    @endif
</div>
@endforeach
@endif

{{-- ═══════════ CIERRE ═══════════ --}}
<div class="page thank-page">

    {{-- Adornos geométricos --}}
    <div class="thank-deco-a"></div>
    <div class="thank-deco-b"></div>
    <div class="thank-deco-c"></div>
    <div class="thank-deco-d"></div>
    <div class="thank-deco-line"></div>

    {{-- Banda superior --}}
    <div class="thank-stripe-top"></div>

    {{-- Contenido central --}}
    <div class="thank-body">
        <div class="thank-eyebrow">Infinito Reciclaje · {{ $periodo }}</div>
        <div class="thank-title">Gracias.</div>
        <div class="thank-rule"></div>
        <div class="thank-org">{{ $organizacion }}</div>
        <p class="thank-caption">
            Reporte correspondiente a {{ $periodo }}, generado a partir de los registros del sistema de balanza digital.<br>
            Cada pesaje queda trazado con fecha, vehículo, zona y peso neto: eso permite auditar la operación
            y comparar la evolución entre períodos.
        </p>
    </div>

    {{-- Pie oscuro --}}
    <div class="thank-footer">
        <div>
            <div class="thank-footer-org">{{ $organizacion }}</div>
            <div class="thank-footer-sub">Sistema de Balanza Digital · Generado el {{ $generado }}</div>
        </div>
        <div class="thank-footer-badge">Gestión Integral de Residuos</div>
    </div>
</div>


{{-- Inicializa los mapas Leaflet del PDF. Browsershot (Chromium real) ejecuta
     este script y espera waitUntilNetworkIdle(), dando tiempo a que carguen los
     tiles de OpenStreetMap antes de imprimir. Cada .pdf-map lee su dataset del
     <script type="application/json"> contiguo (zonas con geojson + color). --}}
<script>
(function () {
    function initMaps() {
        if (typeof L === 'undefined') return;
        document.querySelectorAll('.pdf-map[data-choropleth]').forEach(function (el) {
            var src = document.getElementById(el.getAttribute('data-choropleth'));
            if (!src) return;
            var zonas;
            try { zonas = JSON.parse(src.textContent); } catch (e) { zonas = []; }

            var map = L.map(el, {
                zoomControl: false, attributionControl: false, dragging: false,
                scrollWheelZoom: false, doubleClickZoom: false, boxZoom: false,
                keyboard: false, touchZoom: false,
            });
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

            var group = L.featureGroup().addTo(map);
            zonas.forEach(function (z) {
                if (!z.geojson) return;
                var layer = L.geoJSON(z.geojson, {
                    style: { color: '#475569', weight: 2, fillColor: z.color, fillOpacity: 0.7 },
                });
                layer.addTo(group);
                L.tooltip({ permanent: true, direction: 'center', className: 'zona-label', interactive: false })
                    .setLatLng(layer.getBounds().getCenter()).setContent(z.nombre).addTo(group);
            });

            map.invalidateSize();
            if (group.getLayers().length) {
                map.fitBounds(group.getBounds(), { padding: [16, 16], maxZoom: 15 });
            } else {
                map.setView([-27.4698, -58.8306], 12);
            }
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMaps);
    } else {
        initMaps();
    }
})();
</script>

</body>
</html>
