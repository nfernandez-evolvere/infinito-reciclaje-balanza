<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Informe de Pesajes</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'DejaVu Sans', Arial, sans-serif;
        font-size: 10px;
        color: #1e293b;
        background: #ffffff;
        line-height: 1.45;
    }

    /* ══════════════════════════════════════════════════
       SLIDE BASE
    ══════════════════════════════════════════════════ */
    .slide {
        width: 297mm;
        min-height: 170mm;
        page-break-before: always;
        padding: 16mm 20mm 20mm;
        position: relative;
    }

    /* ── Encabezado de slide ── */
    .slide-head {
        margin-bottom: 9mm;
    }
    .slide-eyebrow {
        font-size: 7.5px;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: #0ea5e9;
        margin-bottom: 2mm;
    }
    .slide-title {
        font-size: 19px;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: -0.01em;
    }
    .slide-title-row {
        width: 100%;
    }
    .slide-title-row td { vertical-align: bottom; }
    .slide-meta {
        text-align: right;
        font-size: 9px;
        color: #94a3b8;
    }
    .rule {
        height: 2px;
        background: #e2e8f0;
        margin-top: 4mm;
        position: relative;
    }
    .rule::before {
        content: '';
        position: absolute;
        left: 0; top: 0;
        width: 28mm; height: 2px;
        background: #0ea5e9;
    }

    /* ══════════════════════════════════════════════════
       PORTADA
    ══════════════════════════════════════════════════ */
    .cover {
        width: 297mm;
        min-height: 196mm;
        background: #0f172a;
        background: linear-gradient(140deg, #0f172a 0%, #15294a 55%, #0c4a6e 100%);
        color: #ffffff;
        padding: 0;
        position: relative;
    }
    .cover-inner {
        padding: 32mm 26mm;
    }
    .cover-brand {
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 0.22em;
        text-transform: uppercase;
        color: #7dd3fc;
        margin-bottom: 24mm;
    }
    .cover-eyebrow {
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: #38bdf8;
        margin-bottom: 6mm;
    }
    .cover-title {
        font-size: 40px;
        font-weight: 700;
        line-height: 1.08;
        color: #ffffff;
        letter-spacing: -0.02em;
        margin-bottom: 7mm;
    }
    .cover-divider {
        width: 36mm;
        height: 3px;
        background: #0ea5e9;
        margin-bottom: 7mm;
    }
    .cover-period {
        font-size: 15px;
        font-weight: 400;
        color: #bae6fd;
    }
    .cover-foot {
        position: absolute;
        left: 26mm;
        right: 26mm;
        bottom: 24mm;
        border-top: 1px solid rgba(148, 197, 230, 0.25);
        padding-top: 6mm;
    }
    .cover-foot-muni {
        font-size: 12px;
        font-weight: 600;
        color: #e0f2fe;
        margin-bottom: 1mm;
    }
    .cover-foot-sub {
        font-size: 9px;
        color: #7dafd6;
    }

    /* ══════════════════════════════════════════════════
       QUIÉNES SOMOS
    ══════════════════════════════════════════════════ */
    .intro {
        font-size: 11px;
        color: #475569;
        line-height: 1.7;
        margin-bottom: 11mm;
        max-width: 210mm;
    }
    .svc-grid { width: 100%; border-collapse: collapse; }
    .svc-grid td {
        width: 33.33%;
        padding: 0 3mm;
        vertical-align: top;
    }
    .svc-grid td:first-child { padding-left: 0; }
    .svc-grid td:last-child { padding-right: 0; }
    .svc-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-top: 3px solid #0ea5e9;
        border-radius: 7px;
        padding: 7mm 6mm;
        min-height: 42mm;
    }
    .svc-num {
        font-size: 9px;
        font-weight: 700;
        color: #0ea5e9;
        margin-bottom: 4mm;
        letter-spacing: 0.05em;
    }
    .svc-title {
        font-size: 12px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 3mm;
    }
    .svc-desc {
        font-size: 9.5px;
        color: #64748b;
        line-height: 1.6;
    }

    /* ══════════════════════════════════════════════════
       KPIs
    ══════════════════════════════════════════════════ */
    .kpi-grid { width: 100%; border-collapse: collapse; margin-bottom: 8mm; }
    .kpi-grid td {
        width: 20%;
        padding: 0 2.5mm;
        vertical-align: top;
    }
    .kpi-grid td:first-child { padding-left: 0; }
    .kpi-grid td:last-child { padding-right: 0; }
    .kpi-card {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 6mm 5mm 6.5mm;
        background: #ffffff;
        min-height: 38mm;
    }
    .kpi-card-accent {
        background: linear-gradient(140deg, #0369a1 0%, #0ea5e9 100%);
        border: none;
    }
    .kpi-label {
        font-size: 7.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #94a3b8;
        margin-bottom: 6mm;
    }
    .kpi-card-accent .kpi-label { color: #bae6fd; }
    .kpi-value {
        font-size: 27px;
        font-weight: 700;
        line-height: 1;
        color: #0f172a;
        letter-spacing: -0.02em;
    }
    .kpi-card-accent .kpi-value { color: #ffffff; }
    .kpi-unit {
        font-size: 9px;
        font-weight: 400;
        color: #94a3b8;
        margin-top: 2.5mm;
    }
    .kpi-card-accent .kpi-unit { color: #e0f2fe; }

    /* ── Stat row (evolución) ── */
    .stat-grid { width: 60%; border-collapse: collapse; margin-bottom: 8mm; }
    .stat-grid td {
        padding: 0 4mm;
        vertical-align: top;
        border-left: 2px solid #e2e8f0;
    }
    .stat-grid td:first-child { padding-left: 0; border-left: none; }
    .stat-label {
        font-size: 7.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #94a3b8;
        margin-bottom: 2mm;
    }
    .stat-value {
        font-size: 17px;
        font-weight: 700;
        color: #0f172a;
    }
    .stat-value.pos { color: #16a34a; }
    .stat-value.muted { color: #64748b; }

    /* ── Chart wrapper ── */
    .chart-box {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 7mm 7mm 4mm;
        background: #fcfdfe;
    }

    /* ══════════════════════════════════════════════════
       TABLAS
    ══════════════════════════════════════════════════ */
    table.data {
        width: 100%;
        border-collapse: collapse;
        font-size: 9.5px;
    }
    table.data thead th {
        background: #0f172a;
        color: #cbd5e1;
        padding: 4mm 4mm;
        text-align: left;
        font-weight: 700;
        font-size: 8px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    table.data thead th:first-child { border-radius: 6px 0 0 0; }
    table.data thead th:last-child { border-radius: 0 6px 0 0; }
    table.data thead th.r { text-align: right; }
    table.data tbody td {
        padding: 3.4mm 4mm;
        border-bottom: 1px solid #eef2f6;
        color: #334155;
    }
    table.data tbody tr:nth-child(even) td { background: #f8fafc; }
    table.data tbody td.r { text-align: right; }
    table.data tbody td.strong { font-weight: 700; color: #0f172a; }
    table.data tbody td.num { text-align: right; font-weight: 600; color: #0f172a; }
    table.data tbody td.muted { color: #94a3b8; }
    table.data tfoot td {
        padding: 4mm 4mm;
        font-weight: 700;
        color: #0f172a;
        border-top: 2px solid #0f172a;
        background: #f1f5f9;
    }
    table.data tfoot td.r { text-align: right; }
    table.data tfoot td:first-child { border-radius: 0 0 0 6px; }
    table.data tfoot td:last-child { border-radius: 0 0 6px 0; }

    .dot {
        display: inline-block;
        width: 7px; height: 7px;
        border-radius: 50%;
        margin-right: 5px;
        vertical-align: middle;
    }
    .pill {
        display: inline-block;
        padding: 1mm 2.5mm;
        border-radius: 20px;
        background: #e2e8f0;
        color: #475569;
        font-size: 8px;
        font-weight: 600;
    }
    .sub {
        font-size: 8px;
        color: #94a3b8;
        display: block;
    }

    /* ── Layout dos columnas ── */
    table.cols { width: 100%; border-collapse: collapse; }
    table.cols > tbody > tr > td { vertical-align: top; }
    .col-gap { width: 10mm; }

    /* ── Leyenda ── */
    .legend { width: 100%; border-collapse: collapse; margin-bottom: 6mm; }
    .legend td {
        font-size: 8px;
        color: #64748b;
        padding-right: 7mm;
        white-space: nowrap;
        width: 1%;
    }
    .legend .dot { width: 8px; height: 8px; }

    /* ── Bloques de análisis ── */
    .insight {
        border-left: 3px solid #0ea5e9;
        background: #f0f9ff;
        border-radius: 0 6px 6px 0;
        padding: 5mm 6mm;
        margin-bottom: 5mm;
    }
    .insight.green { border-left-color: #22c55e; background: #f0fdf4; }
    .insight-label {
        font-size: 7.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        margin-bottom: 2mm;
    }
    .insight-text {
        font-size: 9.5px;
        color: #475569;
        line-height: 1.6;
    }

    /* ── Cierre ── */
    .closing {
        text-align: center;
        padding: 16mm 0 10mm;
    }
    .closing-title {
        font-size: 30px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 3mm;
        letter-spacing: -0.02em;
    }
    .closing-sub {
        font-size: 11px;
        color: #64748b;
        margin-bottom: 1.5mm;
    }
    .closing-muni {
        font-size: 9.5px;
        color: #94a3b8;
    }

    /* ── Pie de página ── */
    .foot {
        position: absolute;
        left: 20mm; right: 20mm;
        bottom: 9mm;
        border-top: 1px solid #e2e8f0;
        padding-top: 3mm;
    }
    .foot-tbl { width: 100%; border-collapse: collapse; }
    .foot-tbl td {
        font-size: 7.5px;
        color: #94a3b8;
    }
    .foot-tbl td.r { text-align: right; }
    .foot-brand { font-weight: 600; color: #64748b; }
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

    $periodo      = ucfirst($desde->translatedFormat('F Y'));
    $periodoLargo = $desde->translatedFormat('d \d\e F') . ' al ' . $hasta->translatedFormat('d \d\e F \d\e Y');
    $municipalidad = $config?->municipalidad_nombre ?? 'Municipalidad';
    $generado     = now()->format('d/m/Y H:i');
    $diasConDatos = count(array_filter(array_column($evolucion['datos'], 'toneladas')));

    $colorZona = function (float $kg): string {
        return match (true) {
            $kg >= 500000 => '#dc2626',
            $kg >= 150000 => '#ea580c',
            $kg >= 80000  => '#f59e0b',
            $kg >= 30000  => '#eab308',
            default       => '#0ea5e9',
        };
    };

    $servicios = $config?->servicios ?? [
        ['titulo' => 'Recolección y Reciclaje', 'descripcion' => 'Gestión de cartón, papel, plásticos y transformación en materia prima.'],
        ['titulo' => 'Datos y Trazabilidad', 'descripcion' => 'Reportes mensuales, certificados y plataforma digital de seguimiento.'],
        ['titulo' => 'Capacitación', 'descripcion' => 'Asesoría en gestión ambiental y operación de puntos verdes.'],
    ];
@endphp

@php
    $foot = '<div class="foot"><table class="foot-tbl"><tr>'
        . '<td><span class="foot-brand">Infinito Reciclaje</span> · Gestión Integral de Residuos</td>'
        . '<td class="r">' . e($municipalidad) . ' · ' . e($periodo) . '</td>'
        . '</tr></table></div>';
@endphp

{{-- ═══════════════ PORTADA ═══════════════ --}}
<div class="cover">
    <div class="cover-inner">
        <div class="cover-brand">Infinito Reciclaje</div>
        <div class="cover-eyebrow">Informe mensual de gestión</div>
        <div class="cover-title">Informe de Pesajes<br>{{ $periodo }}</div>
        <div class="cover-divider"></div>
        <div class="cover-period">{{ $periodoLargo }}</div>
    </div>
    <div class="cover-foot">
        <div class="cover-foot-muni">{{ $municipalidad }}</div>
        <div class="cover-foot-sub">Predio de Disposición Final · Sistema de Balanza Digital</div>
    </div>
</div>

{{-- ═══════════════ QUIÉNES SOMOS ═══════════════ --}}
<div class="slide">
    <div class="slide-head">
        <div class="slide-eyebrow">Presentación</div>
        <div class="slide-title">Quiénes Somos</div>
        <div class="rule"></div>
    </div>

    <p class="intro">
        @if($config?->intro_empresa)
            {{ $config->intro_empresa }}
        @else
            Somos una empresa especializada en la gestión integral de residuos urbanos, brindando servicios de
            recolección, reciclaje y trazabilidad de datos para municipios. Nuestro sistema de balanza digital
            garantiza transparencia y precisión en cada registro, convirtiendo cada pesaje en información estratégica.
        @endif
    </p>

    <table class="svc-grid">
        <tr>
            @foreach(array_slice($servicios, 0, 3) as $i => $servicio)
            <td>
                <div class="svc-card">
                    <div class="svc-num">0{{ $i + 1 }}</div>
                    <div class="svc-title">{{ $servicio['titulo'] ?? '' }}</div>
                    <div class="svc-desc">{{ $servicio['descripcion'] ?? '' }}</div>
                </div>
            </td>
            @endforeach
        </tr>
    </table>

    {!! $foot !!}
</div>

{{-- ═══════════════ RESUMEN GENERAL ═══════════════ --}}
<div class="slide">
    <div class="slide-head">
        <table class="slide-title-row"><tr>
            <td>
                <div class="slide-eyebrow">Indicadores clave</div>
                <div class="slide-title">Resumen General</div>
            </td>
            <td class="slide-meta">{{ $periodoLargo }}</td>
        </tr></table>
        <div class="rule"></div>
    </div>

    <table class="kpi-grid">
        <tr>
            <td>
                <div class="kpi-card kpi-card-accent">
                    <div class="kpi-label">Toneladas netas</div>
                    <div class="kpi-value">{{ number_format($kpis['toneladas'], 0, ',', '.') }}</div>
                    <div class="kpi-unit">toneladas recolectadas</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-label">Total viajes</div>
                    <div class="kpi-value">{{ number_format($kpis['total'], 0, ',', '.') }}</div>
                    <div class="kpi-unit">pesajes registrados</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-label">Días operativos</div>
                    <div class="kpi-value">{{ number_format($kpis['dias_op']) }}</div>
                    <div class="kpi-unit">de {{ $kpis['dias_rango'] }} días del período</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-label">Promedio diario</div>
                    <div class="kpi-value">{{ number_format($kpis['promedio_ton_dia'], 1, ',', '.') }}</div>
                    <div class="kpi-unit">toneladas / día operativo</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-label">Promedio por viaje</div>
                    <div class="kpi-value">{{ number_format($kpis['promedio_kg_viaje'], 0, ',', '.') }}</div>
                    <div class="kpi-unit">kg por viaje</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="insight">
        <div class="insight-label">Lectura del período</div>
        <div class="insight-text">
            Durante {{ $periodo }} se registraron <strong>{{ number_format($kpis['total'], 0, ',', '.') }} viajes</strong>
            que totalizaron <strong>{{ number_format($kpis['toneladas'], 1, ',', '.') }} toneladas netas</strong>,
            con actividad en <strong>{{ $kpis['dias_op'] }} de {{ $kpis['dias_rango'] }} días</strong> y un promedio
            de {{ number_format($kpis['promedio_ton_dia'], 1, ',', '.') }} toneladas por jornada operativa.
        </div>
    </div>

    {!! $foot !!}
</div>

{{-- ═══════════════ EVOLUCIÓN DIARIA ═══════════════ --}}
<div class="slide">
    <div class="slide-head">
        <table class="slide-title-row"><tr>
            <td>
                <div class="slide-eyebrow">Tendencia</div>
                <div class="slide-title">Evolución Diaria de Toneladas</div>
            </td>
            <td class="slide-meta">{{ $periodo }}</td>
        </tr></table>
        <div class="rule"></div>
    </div>

    <table class="stat-grid">
        <tr>
            <td>
                <div class="stat-label">Promedio</div>
                <div class="stat-value">{{ number_format($evolucion['promedio'], 1, ',', '.') }} t</div>
            </td>
            <td>
                <div class="stat-label">Máximo</div>
                <div class="stat-value pos">{{ number_format($evolucion['maximo'], 1, ',', '.') }} t</div>
            </td>
            <td>
                <div class="stat-label">Mínimo</div>
                <div class="stat-value muted">{{ number_format($evolucion['minimo'], 1, ',', '.') }} t</div>
            </td>
            <td>
                <div class="stat-label">Días con datos</div>
                <div class="stat-value">{{ $diasConDatos }}</div>
            </td>
        </tr>
    </table>

    <div class="chart-box">
        {!! $svgEvolucion !!}
    </div>

    {!! $foot !!}
</div>

{{-- ═══════════════ POR TIPO DE VEHÍCULO ═══════════════ --}}
<div class="slide">
    <div class="slide-head">
        <table class="slide-title-row"><tr>
            <td>
                <div class="slide-eyebrow">Composición de flota</div>
                <div class="slide-title">Por Tipo de Vehículo</div>
            </td>
            <td class="slide-meta">{{ $periodo }}</td>
        </tr></table>
        <div class="rule"></div>
    </div>

    <table class="cols">
        <tr>
            <td style="width: 42%;">
                <div class="chart-box">
                    {!! $svgVehiculos !!}
                </div>
            </td>
            <td class="col-gap"></td>
            <td>
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
                        @foreach($vehiculos as $v)
                        <tr>
                            <td class="strong">{{ $v['nombre'] }}</td>
                            <td class="num">{{ number_format($v['viajes'], 0, ',', '.') }}</td>
                            <td class="num">{{ number_format($v['toneladas'], 1, ',', '.') }}</td>
                            <td class="r muted">{{ number_format($v['porcentaje'], 1, ',', '.') }}%</td>
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
            </td>
        </tr>
    </table>

    {!! $foot !!}
</div>

{{-- ═══════════════ POR ZONA ═══════════════ --}}
@php
    $zonasChunks = $zonas->chunk(16);
    $totalSlides = $zonasChunks->count();
@endphp

@foreach($zonasChunks as $chunkIdx => $chunk)
<div class="slide">
    <div class="slide-head">
        <table class="slide-title-row"><tr>
            <td>
                <div class="slide-eyebrow">Distribución territorial</div>
                <div class="slide-title">
                    Toneladas Netas por Zona
                    @if($totalSlides > 1)<span style="font-size:12px; color:#94a3b8; font-weight:400;">({{ $chunkIdx + 1 }}/{{ $totalSlides }})</span>@endif
                </div>
            </td>
            <td class="slide-meta">{{ $periodo }}</td>
        </tr></table>
        <div class="rule"></div>
    </div>

    @if($chunkIdx === 0)
    <table class="legend">
        <tr>
            <td><span class="dot" style="background:#dc2626;"></span> Más de 500 t</td>
            <td><span class="dot" style="background:#ea580c;"></span> 150 – 500 t</td>
            <td><span class="dot" style="background:#f59e0b;"></span> 80 – 150 t</td>
            <td><span class="dot" style="background:#eab308;"></span> 30 – 80 t</td>
            <td><span class="dot" style="background:#0ea5e9;"></span> Menos de 30 t</td>
            <td style="width:auto;"></td>
        </tr>
    </table>
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
                <td class="strong"><span class="dot" style="background:{{ $dotColor }};"></span>{{ $zona['nombre'] }}</td>
                <td>@if($zona['turno'])<span class="pill">{{ $zona['turno'] }}</span>@else<span class="muted">—</span>@endif</td>
                <td class="num">{{ number_format($zona['viajes'], 0, ',', '.') }}</td>
                <td class="num" style="color:{{ $dotColor }};">{{ number_format($zona['toneladas'], 1, ',', '.') }}</td>
                <td class="r muted">{{ number_format($zona['kg_viaje'], 0, ',', '.') }}</td>
                <td class="r muted">{{ number_format($zona['porcentaje'], 1, ',', '.') }}%</td>
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

    {!! $foot !!}
</div>
@endforeach

{{-- ═══════════════ DENSIDAD kg/ha ═══════════════ --}}
@php
    $zonasConHa = $zonas->filter(fn ($z) => $z['kg_ha'] !== null)->sortByDesc('kg_ha')->take(20)->values();
@endphp
@if($zonasConHa->isNotEmpty())
<div class="slide">
    <div class="slide-head">
        <table class="slide-title-row"><tr>
            <td>
                <div class="slide-eyebrow">Intensidad de generación</div>
                <div class="slide-title">Densidad por Hectárea</div>
            </td>
            <td class="slide-meta">Top {{ $zonasConHa->count() }} zonas · {{ $periodo }}</td>
        </tr></table>
        <div class="rule"></div>
    </div>

    <table class="cols">
        <tr>
            <td style="width: 50%;">
                <div class="chart-box">
                    {!! $svgDensidad !!}
                </div>
            </td>
            <td class="col-gap"></td>
            <td>
                @php
                    $topZonas = $zonasConHa->take(3)->pluck('nombre')->join(', ');
                    $bottom   = $zonasConHa->last();
                    $topVal   = $zonasConHa->first()['kg_ha'] ?? 0;
                @endphp
                <div class="insight green">
                    <div class="insight-label">Zonas de mayor densidad</div>
                    <div class="insight-text">
                        {{ $topZonas }} presentan los valores más altos de generación por hectárea, lo que sugiere
                        mayor frecuencia de recolección o mayor concentración de actividad.
                    </div>
                </div>
                @if($bottom && $bottom['kg_ha'] < $topVal * 0.2)
                <div class="insight">
                    <div class="insight-label">Zona de baja densidad</div>
                    <div class="insight-text">
                        {{ $bottom['nombre'] }} registra una densidad relativa baja
                        ({{ number_format($bottom['kg_ha'], 1, ',', '.') }} kg/ha). Conviene evaluar la optimización
                        de su ruta de recolección.
                    </div>
                </div>
                @endif
            </td>
        </tr>
    </table>

    {!! $foot !!}
</div>
@endif

{{-- ═══════════════ CIERRE ═══════════════ --}}
<div class="slide">
    <div class="slide-head">
        <div class="slide-eyebrow">Conclusiones</div>
        <div class="slide-title">Oportunidades Estratégicas</div>
        <div class="rule"></div>
    </div>

    @if(!empty($ai['analisis']))
        <div class="insight">
            <div class="insight-label">Análisis IA — {{ $periodo }}</div>
            <div class="insight-text">{{ $ai['analisis'] }}</div>
        </div>
    @else
        <table class="cols" style="margin-bottom: 4mm;">
            <tr>
                <td>
                    <div class="insight">
                        <div class="insight-label">Análisis completo por zona</div>
                        <div class="insight-text">
                            Identificar con precisión qué zonas generan mayor volumen de residuos y en qué franja
                            horaria, optimizando las rutas y la asignación de recursos de recolección.
                        </div>
                    </div>
                </td>
                <td class="col-gap"></td>
                <td>
                    <div class="insight green">
                        <div class="insight-label">Decisiones basadas en datos</div>
                        <div class="insight-text">
                            El sistema convierte cada pesaje en información estratégica. La Municipalidad dispone de
                            reportes mensuales para evaluar la calidad y el alcance del servicio.
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    @endif

    <div class="closing">
        <div class="closing-title">Gracias</div>
        <div class="closing-sub">Infinito Reciclaje — Gestión Integral de Residuos</div>
        <div class="closing-muni">{{ $municipalidad }} · {{ $periodo }}</div>
    </div>

    <div class="foot"><table class="foot-tbl"><tr>
        <td><span class="foot-brand">Generado</span> el {{ $generado }}</td>
        <td class="r">Sistema de Balanza · Infinito Reciclaje</td>
    </tr></table></div>
</div>

</body>
</html>
