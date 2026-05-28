<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reporte de Pesajes</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 11px;
    color: #18181b;
    line-height: 1.4;
}

.page-header {
    border-bottom: 2px solid #18181b;
    padding-bottom: 10px;
    margin-bottom: 20px;
}
.page-header h1 { font-size: 18px; font-weight: 700; }
.page-header .meta { font-size: 10px; color: #71717a; margin-top: 4px; }

.section { margin-bottom: 20px; }
.section-title {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #71717a;
    margin-bottom: 8px;
    padding-bottom: 4px;
    border-bottom: 1px solid #e4e4e7;
}

/* KPIs */
.kpi-grid { display: flex; gap: 12px; margin-bottom: 4px; }
.kpi-box {
    flex: 1;
    border: 1px solid #e4e4e7;
    border-radius: 6px;
    padding: 10px 12px;
}
.kpi-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.06em; color: #71717a; }
.kpi-value { font-size: 22px; font-weight: 700; margin-top: 2px; }
.kpi-sub { font-size: 9px; color: #a1a1aa; margin-top: 2px; }

/* Tablas */
table { width: 100%; border-collapse: collapse; font-size: 10px; }
thead tr { background-color: #18181b; color: #ffffff; }
thead th { padding: 6px 8px; text-align: left; font-weight: 600; }
thead th.right { text-align: right; }
tbody tr:nth-child(even) { background-color: #f9f9f9; }
tbody td { padding: 5px 8px; border-bottom: 1px solid #f0f0f0; }
tbody td.right { text-align: right; font-family: 'Courier New', monospace; }
tfoot tr { background-color: #f4f4f5; }
tfoot td { padding: 5px 8px; font-weight: 700; border-top: 1px solid #e4e4e7; }
tfoot td.right { text-align: right; font-family: 'Courier New', monospace; }

.badge {
    display: inline-block;
    padding: 1px 6px;
    border-radius: 10px;
    background: #f4f4f5;
    color: #71717a;
    font-size: 9px;
}

.page-footer {
    margin-top: 24px;
    padding-top: 8px;
    border-top: 1px solid #e4e4e7;
    font-size: 9px;
    color: #a1a1aa;
    display: flex;
    justify-content: space-between;
}

.filters-row { font-size: 10px; color: #71717a; margin-bottom: 16px; }
.filters-row strong { color: #18181b; }
</style>
</head>
<body>

@php
    $kpis     = $reporte['kpis'];
    $evolucion = $reporte['evolucion'];
    $zonas    = $reporte['zonas'];
    $vehs     = $reporte['vehiculos'];
    $desde    = $reporte['desde']->translatedFormat('d \d\e F \d\e Y');
    $hasta    = $reporte['hasta']->translatedFormat('d \d\e F \d\e Y');
    $generado = now()->format('d/m/Y H:i');
@endphp

{{-- Encabezado --}}
<div class="page-header">
    <h1>Reporte de Pesajes</h1>
    <div class="meta">
        Período: {{ $desde }} al {{ $hasta }}
        &nbsp;&middot;&nbsp; Generado: {{ $generado }}
    </div>
</div>

{{-- Filtros activos --}}
@php
    $filtros = $reporte['filtros'];
    $filtroTextos = [];
    if (!empty($filtros['zona_id'])) {
        $filtroTextos[] = 'Zona filtrada';
    }
@endphp

{{-- KPIs --}}
<div class="section">
    <div class="section-title">Resumen del período</div>
    <div class="kpi-grid">
        <div class="kpi-box">
            <div class="kpi-label">Viajes</div>
            <div class="kpi-value">{{ number_format($kpis['total']) }}</div>
            <div class="kpi-sub">pesajes registrados</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-label">Toneladas</div>
            <div class="kpi-value">{{ number_format($kpis['toneladas'], 1) }}</div>
            <div class="kpi-sub">toneladas netas</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-label">Días operativos</div>
            <div class="kpi-value">{{ $kpis['dias_op'] }}</div>
            <div class="kpi-sub">de {{ $kpis['dias_rango'] }} días</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-label">Prom. ton/día</div>
            <div class="kpi-value">{{ number_format($kpis['promedio_ton_dia'], 1) }}</div>
            <div class="kpi-sub">en días operativos</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-label">kg/viaje</div>
            <div class="kpi-value">{{ number_format($kpis['promedio_kg_viaje']) }}</div>
            <div class="kpi-sub">promedio por viaje</div>
        </div>
    </div>
</div>

{{-- Por zona y turno --}}
@if($zonas->isNotEmpty())
<div class="section">
    <div class="section-title">Por zona y turno</div>
    <table>
        <thead>
            <tr>
                <th>Zona</th>
                <th>Turno</th>
                <th class="right">Viajes</th>
                <th class="right">Toneladas</th>
                <th class="right">kg/viaje</th>
                <th class="right">% Total</th>
                <th class="right">kg/ha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($zonas as $zona)
            <tr>
                <td>{{ $zona['nombre'] }}</td>
                <td>{{ $zona['turno'] ?? '—' }}</td>
                <td class="right">{{ number_format($zona['viajes']) }}</td>
                <td class="right">{{ number_format($zona['toneladas'], 1) }}</td>
                <td class="right">{{ number_format($zona['kg_viaje']) }}</td>
                <td class="right">{{ $zona['porcentaje'] }}%</td>
                <td class="right">{{ $zona['kg_ha'] !== null ? number_format($zona['kg_ha'], 1) : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Total</td>
                <td class="right">{{ number_format($zonas->sum('viajes')) }}</td>
                <td class="right">{{ number_format($zonas->sum('toneladas'), 1) }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

{{-- Por tipo de vehículo --}}
@if($vehs->isNotEmpty())
<div class="section">
    <div class="section-title">Por tipo de vehículo</div>
    <table>
        <thead>
            <tr>
                <th>Tipo</th>
                <th class="right">Viajes</th>
                <th class="right">Toneladas</th>
                <th class="right">kg/viaje</th>
                <th class="right">% Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vehs as $veh)
            <tr>
                <td>{{ $veh['nombre'] }}</td>
                <td class="right">{{ number_format($veh['viajes']) }}</td>
                <td class="right">{{ number_format($veh['toneladas'], 1) }}</td>
                <td class="right">{{ number_format($veh['kg_viaje']) }}</td>
                <td class="right">{{ $veh['porcentaje'] }}%</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td class="right">{{ number_format($vehs->sum('viajes')) }}</td>
                <td class="right">{{ number_format($vehs->sum('toneladas'), 1) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

{{-- Evolución diaria --}}
@if(!empty($evolucion['datos']))
<div class="section">
    <div class="section-title">Evolución diaria</div>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th class="right">Viajes</th>
                <th class="right">Toneladas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($evolucion['datos'] as $dia)
            @if($dia['viajes'] > 0)
            <tr>
                <td>{{ $dia['fecha'] }}</td>
                <td class="right">{{ $dia['viajes'] }}</td>
                <td class="right">{{ number_format($dia['toneladas'], 1) }}</td>
            </tr>
            @endif
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Promedio días operativos</td>
                <td></td>
                <td class="right">{{ number_format($evolucion['promedio'], 1) }} t</td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

{{-- Footer --}}
<div class="page-footer">
    <span>Infinito Reciclaje — Sistema de Balanza</span>
    <span>Generado el {{ $generado }}</span>
</div>

</body>
</html>
