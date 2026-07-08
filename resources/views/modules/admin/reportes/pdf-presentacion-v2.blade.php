<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Reporte de Pesajes</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        /* Paleta verde reciclaje (OKLCH) — misma identidad que design-tokens.css */
        --g-900: oklch(0.247 0.052 144); --g-800: oklch(0.31 0.078 144);
        --g-700: oklch(0.40 0.105 144);  --g-600: oklch(0.523 0.135 144);
        --g-500: oklch(0.60 0.128 144);  --g-400: oklch(0.70 0.112 144);
        --g-300: oklch(0.80 0.08 144);   --g-200: oklch(0.89 0.05 144);
        --g-100: oklch(0.94 0.032 144);  --g-50: oklch(0.975 0.015 144);
        --ink-900: oklch(0.24 0.006 250); --ink-700: oklch(0.40 0.007 250);
        --ink-500: oklch(0.56 0.007 250); --ink-300: oklch(0.78 0.005 250);
        --line: oklch(0.905 0.004 250);   --bg: oklch(0.965 0.003 250);
    }
    html, body { margin: 0; padding: 0; }
    body {
        font-family: 'Inter', system-ui, -apple-system, Arial, sans-serif;
        color: var(--ink-900);
        background: #fff;
        -webkit-font-smoothing: antialiased;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .num { font-variant-numeric: tabular-nums; font-feature-settings: "tnum" 1; }

    @page { size: A4 landscape; margin: 0; }
    .page {
        position: relative;
        width: 297mm;
        height: 210mm;
        overflow: hidden;
        background: #fff;
        display: flex;
        flex-direction: column;
        page-break-after: always;
    }
    .page:last-child { page-break-after: avoid; }

    /* ── Encabezado de sección (patrón repetido en todas las páginas de contenido) ── */
    .p-body { flex: 1; display: flex; flex-direction: column; padding: 13mm 14mm; position: relative; z-index: 1; }
    .p-topbar { display: flex; justify-content: space-between; align-items: center; }
    .p-eyebrow-wrap { display: flex; align-items: center; gap: 10px; }
    .p-eyebrow-badge { width: 26px; height: 26px; border-radius: 6px; background: var(--g-100); color: var(--g-700); display: flex; align-items: center; justify-content: center; }
    .p-eyebrow { font-size: 11px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: var(--g-700); }
    .p-period { font-size: 11px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: var(--ink-500); }
    .p-title { margin: 16px 0 4px; font-size: 30px; font-weight: 800; letter-spacing: -.02em; color: var(--ink-900); }
    .p-desc { margin: 0 0 18px; font-size: 14.5px; color: var(--ink-500); }
    .p-topgrad { position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, var(--g-700) 0%, var(--g-500) 48%, var(--g-300) 100%); pointer-events: none; z-index: 3; }
    .p-leftbar { position: absolute; left: 0; top: 13mm; width: 6px; height: 32px; background: var(--g-600); border-radius: 0 4px 4px 0; pointer-events: none; z-index: 3; }
    .p-corner-ring { position: absolute; right: -90px; bottom: -90px; width: 340px; height: 340px; border-radius: 50%; border: 1.5px solid var(--g-100); pointer-events: none; }
    .p-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 12px; margin-top: auto; font-size: 10px; color: var(--ink-500); }

    .stat-card { border: 1px solid var(--line); border-radius: 14px; padding: 22px; }
    .stat-icon { width: 32px; height: 32px; border-radius: 9px; background: var(--g-100); display: flex; align-items: center; justify-content: center; }
</style>
</head>
<body>

@php
    $kpis      = $reporte['kpis'];
    $vehiculos = $reporte['vehiculos'];
    $desde     = $reporte['desde'];
    $hasta     = $reporte['hasta'];
    $config    = $reporte['config'] ?? null;

    $semanas       = $reporte['semanas'] ?? [];
    $diaSemana     = $reporte['diaSemana'] ?? [];
    $flotaActiva   = $reporte['flotaActiva'] ?? 0;
    $porServicio   = $reporte['porServicio'] ?? collect();
    $zonasServicio = $reporte['zonasServicio'] ?? [];

    $mismoMes = $desde->isSameMonth($hasta);

    // Si el rango cae dentro de un único mes se muestra "Junio 2026"; si cruza
    // meses (o años) se muestra el rango completo para no sugerir un mes que
    // no cubre todo el período ("Jun – Jul 2026" o "Dic 2025 – Ene 2026").
    $periodo = $mismoMes
        ? ucfirst($desde->translatedFormat('F Y'))
        : ($desde->isSameYear($hasta)
            ? ucfirst($desde->translatedFormat('M')).' – '.ucfirst($hasta->translatedFormat('M Y'))
            : ucfirst($desde->translatedFormat('M Y')).' – '.ucfirst($hasta->translatedFormat('M Y')));

    $tituloReporte    = $mismoMes ? 'Reporte mensual' : 'Reporte del período';
    $tituloReporteCap = $mismoMes ? 'Reporte Mensual de Pesajes' : 'Reporte del Período de Pesajes';
    $organizacion = $config?->municipalidad_nombre ?? 'la organización';
    $kgTotal      = (int) round(($kpis['toneladas'] ?? 0) * 1000);

    $semMax = max(array_map(fn ($s) => $s['kg'], $semanas) ?: [1]);
    $dsMax  = max(array_map(fn ($d) => $d['kg'], $diaSemana) ?: [1]);
    $svcMax = $porServicio->max('kg') ?: 1;

    $vColors = ['var(--g-600)', 'var(--g-500)', 'var(--g-300)', 'var(--g-700)', 'var(--g-400)', 'var(--g-200)'];

    // Etiqueta de eje Y: 'M' con 1 decimal si el máximo del gráfico llega al millón,
    // si no 'k' (miles) — evita que 225k y 150k colapsen ambos a "0.2 M".
    $ejeLabel = fn ($eje, $max) => $eje <= 0
        ? '0 kg'
        : ($max >= 1000000
            ? number_format($eje / 1000000, 1, ',', '.').' M'
            : number_format($eje / 1000, 0, ',', '.').' k');

    // Bloque "quiénes somos": intro configurable + hasta 3 features configurables
    // (mismo dato que usa el v1: config.servicios, con default institucional).
    $introEmpresa = $config?->intro_empresa
        ?: 'Operamos la balanza digital del predio de disposición final, registrando el ingreso de cada camión '
          .'recolector y gestionando digitalmente el flujo de residuos del período, en el marco del contrato de servicio vigente.';

    $features = collect($config?->servicios ?: [
        ['titulo' => 'Pesaje trazable', 'descripcion' => 'Cada camión se identifica por patente y se pesa con mínima intervención manual. El neto se calcula como bruto de entrada menos tara del padrón.'],
        ['titulo' => 'Registro auditado', 'descripcion' => 'Cada pesaje queda registrado con historial trazable, disponible para el control del organismo contratante.'],
        ['titulo' => 'Evidencia mensual', 'descripcion' => 'Este reporte reúne los kilos netos, los ingresos, la composición por vehículo y el desglose por servicio y zona del período.'],
    ])->take(3);

    // Iconos genéricos (paths lucide) reutilizados como badges — un set fijo, sin
    // asumir un mapeo 1:1 con nombres de servicio (los servicios son configurables).
    $icons = [
        'house'      => '<path d="M3 9.5 12 4l9 5.5"></path><path d="M5 10.5V19a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-8.5"></path><path d="M9 20v-6h6v6"></path>',
        'shield'     => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"></path><path d="m9 12 2 2 4-4"></path>',
        'file'       => '<path d="M3 3v18h18"></path><path d="m19 9-5 5-4-4-3 3"></path>',
        'calendar'   => '<path d="M8 2v4"></path><path d="M16 2v4"></path><rect width="18" height="18" x="3" y="4" rx="2"></rect><path d="M3 10h18"></path>',
        'truck'      => '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path><path d="M15 18H9"></path><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.62l-3.48-4.35A1 1 0 0 0 17.52 8H14"></path><circle cx="17" cy="18" r="2"></circle><circle cx="7" cy="18" r="2"></circle>',
        'activity'   => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>',
        'trending'   => '<path d="m12 14 4-4"></path><path d="M3.34 19a10 10 0 1 1 17.32 0"></path>',
        'bars'       => '<line x1="18" x2="18" y1="20" y2="10"></line><line x1="12" x2="12" y1="20" y2="4"></line><line x1="6" x2="6" y1="20" y2="14"></line>',
        'layers'     => '<rect width="18" height="18" x="3" y="3" rx="2"></rect><path d="M3 9h18"></path><path d="M9 21V9"></path>',
        'pin'        => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path><circle cx="12" cy="10" r="3"></circle>',
        'recycle'    => '<path d="M7 19H4.815a1.83 1.83 0 0 1-1.57-.881 1.785 1.785 0 0 1-.004-1.784L7.196 9.5"></path><path d="M11 19h8.203a1.83 1.83 0 0 0 1.556-.89 1.784 1.784 0 0 0 0-1.775l-1.226-2.12"></path><path d="m14 16-3 3 3 3"></path><path d="M8.293 13.596 7.196 9.5 3.1 10.598"></path><path d="m9.344 5.811 1.093-1.892A1.83 1.83 0 0 1 11.985 3a1.784 1.784 0 0 1 1.546.888l3.943 6.843"></path><path d="m13.378 9.633 4.096 1.098 1.097-4.096"></path>',
    ];
    $featureIconOrder = ['house', 'shield', 'file'];

    $serviciosArr   = $porServicio->values()->all();
    $topServicio    = $zonasServicio[0] ?? null;
    $otrosServicios = array_slice($zonasServicio, 1);
    $otrosChunks    = array_chunk($otrosServicios, 4);

    // Secciones habilitadas (congeladas en el snapshot al generar; los reportes
    // previos a la opción no traen la clave → todas). Portada y cierre son fijas;
    // cada separador se imprime solo si alguna página de su grupo quedó activa.
    $sec = \App\Support\ReporteSecciones::sanitizarPdf($reporte['secciones']['pdf'] ?? null);
    $on  = fn (string $key) => in_array($key, $sec, true);

    $grupoResumen = $on('resumen_ejecutivo') || $on('ingresos_semana') || $on('dia_semana');
    $grupoFlota   = $on('tipo_vehiculo');
    $grupoZonas   = $on('que_es_servicio') || $on('recoleccion_servicio') || $on('zonas_servicio');

    // Numeración de páginas: portada + cierre fijas, más las páginas y separadores
    // activos + 1 de zonas del servicio principal (si hay algún servicio con zonas)
    // + N páginas de "otros servicios" (de a 4).
    $paginasZonas = $on('zonas_servicio') ? ($topServicio ? 1 : 0) + count($otrosChunks) : 0;
    $totalPaginas = 2
        + (int) $on('quienes_somos')
        + (int) $grupoResumen + (int) $on('resumen_ejecutivo') + (int) $on('ingresos_semana') + (int) $on('dia_semana')
        + (int) $grupoFlota + (int) $on('tipo_vehiculo')
        + (int) $grupoZonas + (int) $on('que_es_servicio') + (int) $on('recoleccion_servicio')
        + $paginasZonas;
    $pagina = 0;

    // Eyebrow "NN · Título": correlativo a las secciones activas (la portada es 01),
    // así el índice no salta números cuando se desactivan páginas.
    $numSeccion = [];
    $n = 1;
    foreach (['quienes_somos', 'resumen_ejecutivo', 'ingresos_semana', 'dia_semana', 'tipo_vehiculo', 'que_es_servicio', 'recoleccion_servicio'] as $k) {
        if ($on($k)) {
            $numSeccion[$k] = str_pad((string) ++$n, 2, '0', STR_PAD_LEFT);
        }
    }
    if ($on('zonas_servicio')) {
        if ($topServicio) {
            $numSeccion['zonas_top'] = str_pad((string) ++$n, 2, '0', STR_PAD_LEFT);
        }
        if ($otrosChunks !== []) {
            $numSeccion['zonas_otros'] = str_pad((string) ++$n, 2, '0', STR_PAD_LEFT);
        }
    }

    /** Barra decorativa + eyebrow + título de página (idéntico en todas las páginas de contenido). */
    $pageHeader = function (string $numero, string $icon, string $eyebrow) use ($icons, $periodo, $tituloReporte) {
        return '
        <div class="p-topgrad"></div>
        <div class="p-leftbar"></div>
        <div class="p-topbar">
            <div class="p-eyebrow-wrap">
                <div class="p-eyebrow-badge"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">'.($icons[$icon] ?? '').'</svg></div>
                <span class="p-eyebrow">'.$numero.' · '.$eyebrow.'</span>
            </div>
            <span class="p-period">'.$tituloReporte.' · '.$periodo.'</span>
        </div>';
    };

    $pageFooter = function () use (&$pagina, $totalPaginas, $organizacion) {
        $pagina++;

        return '
        <div class="p-footer">
            <span>Infinito Reciclaje · Disposición Final · Corrientes</span>
            <span>'.$organizacion.'</span>
            <span class="num">Pág. '.str_pad((string) $pagina, 2, '0', STR_PAD_LEFT).' / '.str_pad((string) $totalPaginas, 2, '0', STR_PAD_LEFT).'</span>
        </div>';
    };
@endphp

{{-- ═══════════ 01 · PORTADA ═══════════ --}}
<section class="page" style="color:#fff;background:radial-gradient(circle at 78% 12%, oklch(0.30 0.07 144), oklch(0.20 0.045 144) 72%);">
    <div style="position:absolute;right:-160px;top:-160px;width:620px;height:620px;border-radius:50%;border:1.5px solid rgba(255,255,255,.12);pointer-events:none;"></div>
    <div style="position:absolute;right:-70px;top:-70px;width:440px;height:440px;border-radius:50%;border:1.5px solid rgba(255,255,255,.10);pointer-events:none;"></div>
    <div style="position:absolute;right:120px;top:150px;width:210px;height:210px;border-radius:50%;border:1.5px solid rgba(255,255,255,.08);pointer-events:none;"></div>
    <svg width="196" height="196" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.09)" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" style="position:absolute;right:97px;top:118px;pointer-events:none;">{!! $icons['recycle'] !!}</svg>

    <div style="position:relative;flex:1;display:flex;flex-direction:column;justify-content:space-between;padding:20mm 22mm;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div style="display:flex;align-items:center;gap:13px;">
                <div style="width:46px;height:46px;border-radius:12px;background:rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">{!! $icons['recycle'] !!}</svg>
                </div>
                <div style="line-height:1.15;">
                    <div style="font-size:19px;font-weight:800;letter-spacing:-.01em;">Infinito Reciclaje</div>
                    <div style="font-size:12px;font-weight:500;color:rgba(255,255,255,.72);">{{ $tituloReporteCap }}</div>
                </div>
            </div>
            <div style="text-align:right;font-size:12px;font-weight:500;color:rgba(255,255,255,.78);line-height:1.5;">
                <div style="font-weight:700;color:#fff;">{{ $organizacion }}</div>
                <div>Predio de Disposición Final</div>
            </div>
        </div>

        <div style="max-width:680px;">
            <div style="display:inline-flex;align-items:center;gap:8px;padding:6px 14px;border:1px solid rgba(255,255,255,.28);border-radius:100px;font-size:12px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.9);margin-bottom:26px;">
                Reporte institucional
            </div>
            <h1 style="margin:0;font-size:72px;line-height:1.02;font-weight:800;letter-spacing:-.025em;">{{ $tituloReporte }}<br>de pesajes</h1>
            <div style="display:flex;align-items:baseline;gap:16px;margin-top:22px;">
                <span class="num" style="font-size:34px;font-weight:700;color:var(--g-200);">{{ $periodo }}</span>
                <span style="width:1px;height:26px;background:rgba(255,255,255,.3);"></span>
                <span style="font-size:16px;font-weight:500;color:rgba(255,255,255,.78);">Registro de balanza del predio de disposición final</span>
            </div>
        </div>

        <div style="display:flex;align-items:flex-end;justify-content:space-between;">
            <div style="display:flex;gap:48px;">
                <div>
                    <div class="num" style="font-size:42px;font-weight:800;letter-spacing:-.02em;line-height:1;">{{ number_format($kgTotal, 0, ',', '.') }}<span style="font-size:20px;font-weight:600;color:var(--g-200);"> kg</span></div>
                    <div style="font-size:13px;color:rgba(255,255,255,.72);margin-top:6px;">Total de kilos netos</div>
                </div>
                <div style="border-left:1px solid rgba(255,255,255,.2);padding-left:48px;">
                    <div class="num" style="font-size:42px;font-weight:800;letter-spacing:-.02em;line-height:1;">{{ number_format($kpis['total'], 0, ',', '.') }}</div>
                    <div style="font-size:13px;color:rgba(255,255,255,.72);margin-top:6px;">Ingresos al predio</div>
                </div>
                <div style="border-left:1px solid rgba(255,255,255,.2);padding-left:48px;">
                    <div class="num" style="font-size:42px;font-weight:800;letter-spacing:-.02em;line-height:1;">{{ $kpis['dias_op'] }}</div>
                    <div style="font-size:13px;color:rgba(255,255,255,.72);margin-top:6px;">Días de operación</div>
                </div>
            </div>
            <div style="text-align:right;font-size:11px;color:rgba(255,255,255,.55);line-height:1.6;">
                <div>Documento institucional · Uso oficial</div>
                <div>Generado a partir del registro de balanza</div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════ 02 · QUIÉNES SOMOS ═══════════ --}}
@if($on('quienes_somos'))
<section class="page">
    <div class="p-corner-ring" style="right:-140px;bottom:-140px;width:380px;height:380px;"></div>
    <div class="p-body">
        {!! $pageHeader($numSeccion['quienes_somos'], 'recycle', 'Quiénes somos') !!}

        <div style="flex:1;display:grid;grid-template-columns:1.35fr 1fr;gap:40px;align-items:stretch;position:relative;z-index:1;">
            <div>
                <h2 style="margin:0 0 20px;font-size:38px;line-height:1.08;font-weight:800;letter-spacing:-.02em;color:var(--ink-900);">Operamos la balanza del predio de disposición final</h2>
                <p style="margin:0 0 18px;font-size:16px;line-height:1.6;color:var(--ink-700);max-width:52ch;">{{ $introEmpresa }}</p>

                <div style="display:flex;flex-direction:column;gap:16px;margin-top:24px;">
                    @foreach($features as $i => $f)
                    <div style="display:flex;gap:14px;">
                        <div style="flex-shrink:0;width:34px;height:34px;border-radius:9px;background:var(--g-50);color:var(--g-700);display:flex;align-items:center;justify-content:center;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $icons[$featureIconOrder[$i] ?? 'file'] !!}</svg>
                        </div>
                        <div>
                            <div style="font-size:15px;font-weight:700;color:var(--ink-900);">{{ $f['titulo'] ?? $f['nombre'] ?? '' }}</div>
                            <div style="font-size:13.5px;color:var(--ink-500);line-height:1.5;">{{ $f['descripcion'] ?? '' }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div style="background:var(--g-900);border-radius:16px;padding:34px 30px;color:#fff;position:relative;overflow:hidden;display:flex;flex-direction:column;justify-content:space-between;">
                <div style="position:absolute;right:-60px;top:-60px;width:180px;height:180px;border-radius:50%;border:1.5px solid rgba(255,255,255,.1);"></div>
                <div style="font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--g-300);">El servicio en cifras</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:22px 20px;margin-top:24px;position:relative;">
                    <div><div class="num" style="font-size:40px;font-weight:800;line-height:1;">1</div><div style="font-size:13px;color:rgba(255,255,255,.72);margin-top:5px;">Predio de disposición final</div></div>
                    <div><div class="num" style="font-size:40px;font-weight:800;line-height:1;">{{ $porServicio->count() }}</div><div style="font-size:13px;color:rgba(255,255,255,.72);margin-top:5px;">Tipos de servicio</div></div>
                    <div><div class="num" style="font-size:40px;font-weight:800;line-height:1;">{{ $vehiculos->count() }}</div><div style="font-size:13px;color:rgba(255,255,255,.72);margin-top:5px;">Tipos de vehículo</div></div>
                    <div><div class="num" style="font-size:40px;font-weight:800;line-height:1;">{{ $flotaActiva }}</div><div style="font-size:13px;color:rgba(255,255,255,.72);margin-top:5px;">Camiones operativos</div></div>
                </div>
                <div style="height:1px;background:rgba(255,255,255,.14);margin:26px 0 18px;"></div>
                <div style="font-size:12.5px;line-height:1.6;color:rgba(255,255,255,.82);position:relative;">Datos extraídos íntegramente del registro de balanza. Formato es-AR: separador de miles con punto, decimales con coma.</div>
            </div>
        </div>

        {!! $pageFooter() !!}
    </div>
</section>

@endif

{{-- ═══════════ SEPARADOR · RESUMEN DEL PERÍODO ═══════════ --}}
@if($grupoResumen)
<section class="page" style="color:#fff;background:radial-gradient(circle at 78% 12%, oklch(0.30 0.07 144), oklch(0.20 0.045 144) 72%);">
    <div style="position:absolute;right:-150px;top:-150px;width:560px;height:560px;border-radius:50%;border:1.5px solid rgba(255,255,255,.10);pointer-events:none;"></div>
    <div style="position:absolute;right:-60px;top:-60px;width:380px;height:380px;border-radius:50%;border:1.5px solid rgba(255,255,255,.08);pointer-events:none;"></div>
    <svg width="220" height="220" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.08)" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" style="position:absolute;right:92px;top:118px;pointer-events:none;">{!! $icons['activity'] !!}</svg>

    <div style="position:relative;flex:1;display:flex;flex-direction:column;justify-content:space-between;padding:20mm 22mm;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:40px;height:40px;border-radius:11px;background:rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;">
                    <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">{!! $icons['recycle'] !!}</svg>
                </div>
                <div style="line-height:1.15;">
                    <div style="font-size:17px;font-weight:800;">Infinito Reciclaje</div>
                    <div style="font-size:12px;color:rgba(255,255,255,.7);">{{ $tituloReporteCap }}</div>
                </div>
            </div>
            <span style="font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--g-300);">{{ $tituloReporte }} · {{ $periodo }}</span>
        </div>

        <div style="max-width:720px;">
            <div style="display:inline-flex;align-items:center;gap:9px;padding:7px 15px;border:1px solid rgba(255,255,255,.28);border-radius:100px;font-size:12px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.9);margin-bottom:26px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">{!! $icons['activity'] !!}</svg>
                Sección
            </div>
            <h2 style="margin:0;font-size:60px;line-height:1.04;font-weight:800;letter-spacing:-.025em;">Resumen del período</h2>
            <p style="margin:20px 0 0;font-size:18px;font-weight:500;line-height:1.5;color:rgba(255,255,255,.8);max-width:560px;">Volúmenes totales, evolución semanal y recolección por día.</p>
        </div>

        <div style="display:flex;align-items:center;gap:16px;">
            <span style="width:54px;height:2px;background:var(--g-300);"></span>
            <span style="font-size:12.5px;color:rgba(255,255,255,.62);">Registro de balanza del predio de disposición final · {{ $periodo }}</span>
        </div>
    </div>
</section>

@endif

{{-- ═══════════ 03 · RESUMEN EJECUTIVO ═══════════ --}}
@if($on('resumen_ejecutivo'))
<section class="page">
    <div class="p-corner-ring"></div>
    <svg aria-hidden="true" width="230" height="230" viewBox="0 0 24 24" fill="none" stroke="var(--g-100)" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" style="position:absolute;right:-46px;bottom:-46px;pointer-events:none;">{!! $icons['recycle'] !!}</svg>
    <div class="p-body">
        {!! $pageHeader($numSeccion['resumen_ejecutivo'], 'activity', 'Indicadores clave') !!}
        <h2 class="p-title">Resumen ejecutivo</h2>
        <p class="p-desc"><span style="color:var(--ink-700);font-weight:600;">Indicadores generales de la operación.</span> Cifras totales del período, tal como surgen del registro de balanza del predio de disposición final.</p>

        <div style="display:grid;grid-template-columns:1.35fr 1fr;gap:20px;">
            <div style="border:1px solid var(--line);border-radius:14px;padding:24px 26px;position:relative;overflow:hidden;background:linear-gradient(180deg,var(--g-50),#fff);">
                <div style="display:flex;align-items:center;gap:10px;color:var(--g-700);">
                    <div class="stat-icon"><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $icons['truck'] !!}</svg></div>
                    <span style="font-size:14px;font-weight:600;color:var(--ink-700);">Total de kilos netos</span>
                </div>
                <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-top:14px;">
                    <div class="num" style="font-size:64px;font-weight:800;letter-spacing:-.035em;line-height:.9;color:var(--ink-900);">{{ number_format($kgTotal, 0, ',', '.') }}<span style="font-size:26px;font-weight:700;color:var(--ink-500);"> kg</span></div>
                </div>
                <div style="display:flex;align-items:flex-end;gap:6px;height:44px;margin-top:16px;">
                    @foreach($semanas as $s)
                    <div title="Sem. {{ $s['numero'] }}" style="flex:1;height:{{ $semMax > 0 ? max(10, round(($s['kg'] / $semMax) * 100)) : 10 }}%;border-radius:3px 3px 0 0;background:{{ $s['kg'] >= $semMax ? 'var(--g-600)' : 'var(--g-300)' }};"></div>
                    @endforeach
                    <span style="font-size:11px;color:var(--ink-500);margin-left:8px;align-self:flex-end;">por semana</span>
                </div>
            </div>
            <div style="border:1px solid var(--line);border-radius:14px;padding:24px 26px;display:flex;flex-direction:column;justify-content:center;">
                <div style="display:flex;align-items:center;gap:10px;color:var(--g-700);">
                    <div class="stat-icon"><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $icons['bars'] !!}</svg></div>
                    <span style="font-size:14px;font-weight:600;color:var(--ink-700);">Ingresos al predio</span>
                </div>
                <div class="num" style="font-size:64px;font-weight:800;letter-spacing:-.035em;line-height:.9;color:var(--ink-900);margin-top:14px;">{{ number_format($kpis['total'], 0, ',', '.') }}</div>
                <div style="font-size:13px;color:var(--ink-500);margin-top:10px;">Viajes de camiones registrados en el mes.</div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-top:20px;flex:1;">
            <div class="stat-card" style="display:flex;flex-direction:column;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:9px;color:var(--g-700);"><div class="stat-icon" style="width:32px;height:32px;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $icons['calendar'] !!}</svg></div><span style="font-size:13.5px;font-weight:600;color:var(--ink-700);">Días de operación</span></div>
                <div class="num" style="font-size:56px;font-weight:800;letter-spacing:-.03em;line-height:1;color:var(--ink-900);margin:14px 0 4px;">{{ $kpis['dias_op'] }}</div>
                <div style="font-size:12.5px;color:var(--ink-500);">de {{ $kpis['dias_rango'] }} días del período</div>
            </div>
            <div class="stat-card" style="display:flex;flex-direction:column;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:9px;color:var(--g-700);"><div class="stat-icon" style="width:32px;height:32px;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $icons['activity'] !!}</svg></div><span style="font-size:13.5px;font-weight:600;color:var(--ink-700);">Promedio por día</span></div>
                <div class="num" style="font-size:52px;font-weight:800;letter-spacing:-.03em;line-height:1;color:var(--ink-900);margin:14px 0 4px;">{{ number_format($kpis['dias_op'] > 0 ? round($kgTotal / $kpis['dias_op']) : 0, 0, ',', '.') }}<span style="font-size:20px;font-weight:700;color:var(--ink-500);"> kg</span></div>
                <div style="font-size:12.5px;color:var(--ink-500);">kilos netos por día operativo</div>
            </div>
            <div class="stat-card" style="display:flex;flex-direction:column;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:9px;color:var(--g-700);"><div class="stat-icon" style="width:32px;height:32px;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $icons['trending'] !!}</svg></div><span style="font-size:13.5px;font-weight:600;color:var(--ink-700);">Promedio por ingreso</span></div>
                <div class="num" style="font-size:52px;font-weight:800;letter-spacing:-.03em;line-height:1;color:var(--ink-900);margin:14px 0 4px;">{{ number_format($kpis['promedio_kg_viaje'], 0, ',', '.') }}<span style="font-size:20px;font-weight:700;color:var(--ink-500);"> kg</span></div>
                <div style="font-size:12.5px;color:var(--ink-500);">carga neta media por viaje</div>
            </div>
        </div>

        {!! $pageFooter() !!}
    </div>
</section>

@endif

{{-- ═══════════ 04 · INGRESO POR SEMANA ═══════════ --}}
@if($on('ingresos_semana'))
@php
    $semMaxEje = $semMax > 0 ? $semMax : 1;
    $semEjes = collect(range(4, 0))->map(fn ($i) => $semMaxEje * $i / 4);
    $semTop = collect($semanas)->sortByDesc('kg')->first();
    $promDiaGlobal = $kpis['dias_op'] > 0 ? round($kgTotal / $kpis['dias_op']) : 0;

    // El gráfico se dimensiona para ~4 semanas; cuando el período abarca más, el
    // gap fijo y las etiquetas hacen que las barras se salgan del ancho de página.
    // Escalamos separación, ancho de barra y tipografías según la cantidad de semanas.
    $semCount     = count($semanas);
    $semGap       = $semCount <= 5 ? 34 : ($semCount <= 8 ? 16 : 8);
    $semBarMax    = $semCount <= 5 ? 120 : ($semCount <= 8 ? 64 : 40);
    $semValFont   = $semCount <= 6 ? 15 : ($semCount <= 9 ? 12 : 10);
    $semSemFont   = $semCount <= 6 ? 13 : ($semCount <= 9 ? 11 : 10);
    $semDateFont  = $semCount <= 6 ? 11 : ($semCount <= 9 ? 10 : 9);
    $semPad       = $semCount <= 8 ? 20 : 8;
@endphp
<section class="page">
    <div class="p-corner-ring"></div>
    <div class="p-body">
        {!! $pageHeader($numSeccion['ingresos_semana'], 'calendar', 'Evolución semanal') !!}
        <h2 class="p-title">¿Cuánto ingresa por semana?</h2>
        <p class="p-desc"><span style="color:var(--ink-700);font-weight:600;">Kilogramos recibidos por semana.</span> Residuos ingresados al predio agrupados por semana del período.</p>

        <div style="flex:1;display:grid;grid-template-columns:1fr 258px;gap:26px;min-height:0;">
            <div style="display:flex;gap:12px;min-height:0;">
                <div class="num" style="display:flex;flex-direction:column;justify-content:space-between;padding:6px 0 30px;font-size:10.5px;color:var(--ink-500);text-align:right;width:60px;">
                    @foreach($semEjes as $eje)
                        <span>{{ $ejeLabel($eje, $semMaxEje) }}</span>
                    @endforeach
                </div>
                <div style="flex:1;position:relative;">
                    <div style="position:absolute;inset:6px 0 30px 0;">
                        <div style="position:absolute;top:0;left:0;right:0;border-top:1px solid var(--line);"></div>
                        <div style="position:absolute;top:25%;left:0;right:0;border-top:1px solid var(--line);"></div>
                        <div style="position:absolute;top:50%;left:0;right:0;border-top:1px solid var(--line);"></div>
                        <div style="position:absolute;top:75%;left:0;right:0;border-top:1px solid var(--line);"></div>
                        <div style="position:absolute;bottom:0;left:0;right:0;border-top:1.5px solid var(--ink-300);"></div>
                    </div>
                    <div style="position:absolute;inset:6px 0 30px 0;display:flex;align-items:stretch;gap:{{ $semGap }}px;padding:0 {{ $semPad }}px;">
                        @foreach($semanas as $s)
                        @php $pct = $semMaxEje > 0 ? ($s['kg'] / $semMaxEje) * 100 : 0; @endphp
                        <div style="flex:1;min-width:0;display:flex;flex-direction:column;justify-content:flex-end;align-items:center;gap:8px;height:100%;">
                            <div class="num" style="font-size:{{ $semValFont }}px;font-weight:800;white-space:nowrap;color:{{ $s['kg'] >= $semMaxEje ? 'var(--g-700)' : 'var(--ink-900)' }};">{{ number_format($s['kg'], 0, ',', '.') }}</div>
                            <div style="width:100%;max-width:{{ $semBarMax }}px;height:{{ max($pct, 1) }}%;border-radius:6px 6px 0 0;background:{{ $s['kg'] >= $semMaxEje ? 'linear-gradient(180deg,var(--g-600),var(--g-700))' : 'linear-gradient(180deg,var(--g-500),var(--g-600))' }};"></div>
                            <div style="text-align:center;line-height:1.3;">
                                <div style="font-size:{{ $semSemFont }}px;font-weight:700;white-space:nowrap;color:var(--ink-900);">Sem. {{ $s['numero'] }}</div>
                                <div style="font-size:{{ $semDateFont }}px;white-space:nowrap;color:var(--ink-500);">{{ $s['desde']->format('j') }} al {{ $s['hasta']->translatedFormat('j M') }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:14px;">
                @if($semTop)
                <div style="background:var(--g-50);border:1px solid var(--g-100);border-radius:12px;padding:18px;">
                    <div style="font-size:12px;font-weight:600;color:var(--ink-500);">Semana de mayor volumen</div>
                    <div class="num" style="font-size:30px;font-weight:800;color:var(--ink-900);letter-spacing:-.02em;margin-top:2px;">{{ number_format($semTop['kg'], 0, ',', '.') }} <span style="font-size:14px;font-weight:600;color:var(--ink-500);">kg</span></div>
                    <div style="font-size:12.5px;color:var(--ink-500);margin-top:2px;">Semana {{ $semTop['numero'] }} · {{ $semTop['desde']->format('j') }} al {{ $semTop['hasta']->translatedFormat('j \d\e F') }} ({{ $semTop['desde']->diffInDays($semTop['hasta']) + 1 }} días)</div>
                </div>
                @endif
                <div style="background:var(--g-50);border:1px solid var(--g-100);border-radius:12px;padding:18px;">
                    <div style="font-size:12px;font-weight:600;color:var(--ink-500);">Promedio por día</div>
                    <div class="num" style="font-size:30px;font-weight:800;color:var(--ink-900);letter-spacing:-.02em;margin-top:2px;">{{ number_format($promDiaGlobal, 0, ',', '.') }} <span style="font-size:14px;font-weight:600;color:var(--ink-500);">kg</span></div>
                    <div style="font-size:12.5px;color:var(--ink-500);margin-top:2px;">sobre {{ $kpis['dias_op'] }} días de operación</div>
                </div>
                <div style="background:var(--g-900);border-radius:12px;padding:18px;color:#fff;">
                    <div style="font-size:12px;font-weight:600;color:var(--g-300);">Total del mes</div>
                    <div class="num" style="font-size:30px;font-weight:800;letter-spacing:-.02em;margin-top:2px;">{{ number_format($kgTotal, 0, ',', '.') }} <span style="font-size:14px;font-weight:600;color:rgba(255,255,255,.7);">kg</span></div>
                    <div style="font-size:12.5px;color:rgba(255,255,255,.72);margin-top:2px;">
                        @if($semTop && ($semTop['desde']->diffInDays($semTop['hasta']) + 1) !== 7)
                            La semana {{ $semTop['numero'] }} abarca {{ $semTop['desde']->diffInDays($semTop['hasta']) + 1 }} días, por eso concentra el mayor total.
                        @else
                            Suma de las {{ count($semanas) }} semanas del período.
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {!! $pageFooter() !!}
    </div>
</section>

@endif

{{-- ═══════════ 05 · POR DÍA DE LA SEMANA ═══════════ --}}
@if($on('dia_semana'))
@php
    $dsMaxEje = $dsMax > 0 ? $dsMax : 1;
    $dsEjes = collect(range(4, 0))->map(fn ($i) => $dsMaxEje * $i / 4);
    $dsOrdenado = collect($diaSemana)->sortByDesc('kg')->values();
    $dsTop = $dsOrdenado->first();
    $dsBottom = $dsOrdenado->last();
@endphp
<section class="page">
    <div class="p-corner-ring"></div>
    <svg aria-hidden="true" width="230" height="230" viewBox="0 0 24 24" fill="none" stroke="var(--g-100)" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" style="position:absolute;right:-46px;bottom:-46px;pointer-events:none;">{!! $icons['bars'] !!}</svg>
    <div class="p-body">
        {!! $pageHeader($numSeccion['dia_semana'], 'bars', 'Actividad por día') !!}
        <h2 class="p-title">Recolección según el día</h2>
        <p class="p-desc"><span style="color:var(--ink-700);font-weight:600;">Kilogramos por día de la semana.</span> Total de kilos que ingresan al predio en cada día, sumando todo el período de {{ $periodo }}.</p>

        <div style="flex:1;display:grid;grid-template-columns:1fr 258px;gap:26px;min-height:0;">
            <div style="display:flex;gap:12px;min-height:0;">
                <div class="num" style="display:flex;flex-direction:column;justify-content:space-between;padding:6px 0 30px;font-size:10.5px;color:var(--ink-500);text-align:right;width:60px;">
                    @foreach($dsEjes as $eje)
                        <span>{{ $ejeLabel($eje, $dsMaxEje) }}</span>
                    @endforeach
                </div>
                <div style="flex:1;position:relative;">
                    <div style="position:absolute;inset:6px 0 30px 0;">
                        <div style="position:absolute;top:0;left:0;right:0;border-top:1px solid var(--line);"></div>
                        <div style="position:absolute;top:25%;left:0;right:0;border-top:1px solid var(--line);"></div>
                        <div style="position:absolute;top:50%;left:0;right:0;border-top:1px solid var(--line);"></div>
                        <div style="position:absolute;top:75%;left:0;right:0;border-top:1px solid var(--line);"></div>
                        <div style="position:absolute;bottom:0;left:0;right:0;border-top:1.5px solid var(--ink-300);"></div>
                    </div>
                    <div style="position:absolute;inset:6px 0 30px 0;display:flex;align-items:stretch;gap:14px;padding:0 8px;">
                        @foreach($diaSemana as $d)
                        @php $pct = $dsMaxEje > 0 ? ($d['kg'] / $dsMaxEje) * 100 : 0; $esTop = $d['kg'] >= $dsMaxEje; @endphp
                        <div style="flex:1;display:flex;flex-direction:column;justify-content:flex-end;align-items:center;gap:7px;height:100%;">
                            <div class="num" style="font-size:12px;font-weight:700;color:{{ $esTop ? 'var(--g-700)' : ($d['kg'] > 0 ? 'var(--ink-700)' : 'var(--ink-500)') }};">{{ number_format($d['kg'], 0, ',', '.') }}</div>
                            <div style="width:100%;max-width:60px;height:{{ max($pct, $d['kg'] > 0 ? 3 : 0.5) }}%;border-radius:5px 5px 0 0;background:{{ $esTop ? 'linear-gradient(180deg,var(--g-600),var(--g-700))' : ($d['kg'] > 0 ? 'linear-gradient(180deg,var(--g-500),var(--g-600))' : 'var(--g-300)') }};"></div>
                            <div style="font-size:12px;font-weight:600;color:{{ $d['kg'] > 0 ? 'var(--ink-900)' : 'var(--ink-500)' }};">{{ $d['dia'] }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:14px;justify-content:flex-start;">
                @if($dsTop)
                <div style="background:var(--g-50);border:1px solid var(--g-100);border-radius:12px;padding:18px;">
                    <div style="font-size:12px;font-weight:600;color:var(--ink-500);">Día de mayor ingreso</div>
                    <div style="font-size:26px;font-weight:800;color:var(--ink-900);letter-spacing:-.02em;margin-top:2px;">{{ $dsTop['dia'] }}</div>
                    <div class="num" style="font-size:13px;color:var(--ink-500);margin-top:2px;">{{ number_format($dsTop['kg'], 0, ',', '.') }} kg acumulados en el período</div>
                </div>
                @endif
                @if($dsTop && $dsBottom && $dsTop['dia'] !== $dsBottom['dia'])
                <div style="background:var(--g-900);border-radius:12px;padding:18px;color:#fff;">
                    <div style="font-size:12.5px;line-height:1.55;color:rgba(255,255,255,.85);">Los <strong style="color:#fff;">{{ $dsTop['dia'] }}</strong> concentran el mayor volumen de ingreso y los <strong style="color:#fff;">{{ $dsBottom['dia'] }}</strong> registran la menor actividad del período.</div>
                </div>
                @endif
            </div>
        </div>

        {!! $pageFooter() !!}
    </div>
</section>

@endif

{{-- ═══════════ SEPARADOR · ANÁLISIS POR FLOTA ═══════════ --}}
@if($grupoFlota)
<section class="page" style="color:#fff;background:radial-gradient(circle at 78% 12%, oklch(0.30 0.07 144), oklch(0.20 0.045 144) 72%);">
    <div style="position:absolute;right:-150px;top:-150px;width:560px;height:560px;border-radius:50%;border:1.5px solid rgba(255,255,255,.10);pointer-events:none;"></div>
    <div style="position:absolute;right:-60px;top:-60px;width:380px;height:380px;border-radius:50%;border:1.5px solid rgba(255,255,255,.08);pointer-events:none;"></div>
    <svg width="220" height="220" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.08)" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" style="position:absolute;right:92px;top:118px;pointer-events:none;">{!! $icons['truck'] !!}</svg>

    <div style="position:relative;flex:1;display:flex;flex-direction:column;justify-content:space-between;padding:20mm 22mm;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:40px;height:40px;border-radius:11px;background:rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;">
                    <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">{!! $icons['recycle'] !!}</svg>
                </div>
                <div style="line-height:1.15;">
                    <div style="font-size:17px;font-weight:800;">Infinito Reciclaje</div>
                    <div style="font-size:12px;color:rgba(255,255,255,.7);">{{ $tituloReporteCap }}</div>
                </div>
            </div>
            <span style="font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--g-300);">{{ $tituloReporte }} · {{ $periodo }}</span>
        </div>

        <div style="max-width:720px;">
            <div style="display:inline-flex;align-items:center;gap:9px;padding:7px 15px;border:1px solid rgba(255,255,255,.28);border-radius:100px;font-size:12px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.9);margin-bottom:26px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">{!! $icons['truck'] !!}</svg>
                Sección
            </div>
            <h2 style="margin:0;font-size:60px;line-height:1.04;font-weight:800;letter-spacing:-.025em;">Análisis por flota</h2>
            <p style="margin:20px 0 0;font-size:18px;font-weight:500;line-height:1.5;color:rgba(255,255,255,.8);max-width:560px;">Desempeño individual por número interno.</p>
        </div>

        <div style="display:flex;align-items:center;gap:16px;">
            <span style="width:54px;height:2px;background:var(--g-300);"></span>
            <span style="font-size:12.5px;color:rgba(255,255,255,.62);">Registro de balanza del predio de disposición final · {{ $periodo }}</span>
        </div>
    </div>
</section>

@endif

{{-- ═══════════ 06 · POR TIPO DE VEHÍCULO ═══════════ --}}
@if($on('tipo_vehiculo'))
@php
    $vehTotalViajes = $vehiculos->sum('viajes');
    $vehTotalKg     = (int) round($vehiculos->sum('toneladas') * 1000);
    $circunferencia = 2 * pi() * 78;
    $vehOffsetAcum  = 0;
    $vehMayorViajes = $vehiculos->sortByDesc('viajes')->first();
    $vehMayorKg     = $vehiculos->first(); // ya viene ordenado desc por toneladas
@endphp
<section class="page">
    <div class="p-corner-ring"></div>
    <div class="p-body">
        {!! $pageHeader($numSeccion['tipo_vehiculo'], 'truck', 'Parque de vehículos') !!}
        <h2 class="p-title">Composición por tipo de vehículo</h2>
        <p class="p-desc"><span style="color:var(--ink-700);font-weight:600;">Distribución por tipo de camión.</span>
            @if($vehMayorViajes && $vehMayorKg && $vehMayorViajes['nombre'] !== $vehMayorKg['nombre'])
                El {{ mb_strtolower($vehMayorKg['nombre']) }} concentra más kilos pese a tener menos viajes que el {{ mb_strtolower($vehMayorViajes['nombre']) }}: viene más cargado por viaje.
            @else
                Reparto de viajes y toneladas según el tipo de vehículo en el período.
            @endif
        </p>

        <div style="flex:1;display:grid;grid-template-columns:340px 1fr;gap:44px;align-items:center;">
            <div style="display:flex;flex-direction:column;align-items:center;">
                <div style="position:relative;width:260px;height:260px;">
                    <svg width="260" height="260" viewBox="0 0 200 200">
                        <circle cx="100" cy="100" r="78" fill="none" stroke="var(--g-100)" stroke-width="34"></circle>
                        <g transform="rotate(-90 100 100)">
                            @foreach($vehiculos as $i => $v)
                            @php
                                $frac = $vehTotalKg > 0 ? ($v['toneladas'] * 1000 / $vehTotalKg) : 0;
                                $largo = $frac * $circunferencia;
                                $offset = -$vehOffsetAcum;
                                $vehOffsetAcum += $largo;
                            @endphp
                            <circle cx="100" cy="100" r="78" fill="none" stroke="{{ $vColors[$i % count($vColors)] }}" stroke-width="34" stroke-dasharray="{{ round($largo, 2) }} {{ round($circunferencia - $largo, 2) }}" stroke-dashoffset="{{ round($offset, 2) }}"></circle>
                            @endforeach
                        </g>
                    </svg>
                    <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                        <div class="num" style="font-size:30px;font-weight:800;letter-spacing:-.03em;color:var(--ink-900);line-height:1;">{{ number_format($vehTotalKg / 1000000, 1, ',', '.') }} M</div>
                        <div style="font-size:13px;color:var(--ink-500);margin-top:2px;">kg netos</div>
                    </div>
                </div>
                <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:18px;margin-top:22px;">
                    @foreach($vehiculos as $i => $v)
                    <div style="display:flex;align-items:center;gap:7px;font-size:12.5px;color:var(--ink-700);"><span style="width:11px;height:11px;border-radius:3px;background:{{ $vColors[$i % count($vColors)] }};"></span>{{ $v['nombre'] }}</div>
                    @endforeach
                </div>
            </div>
            <div>
                <div style="display:grid;grid-template-columns:1.6fr .9fr 1.1fr .7fr;gap:0;font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-500);padding:0 0 10px;border-bottom:1.5px solid var(--ink-300);">
                    <span>Tipo de vehículo</span><span style="text-align:right;">Viajes</span><span style="text-align:right;">Kilogramos</span><span style="text-align:right;">%</span>
                </div>
                @foreach($vehiculos as $i => $v)
                <div style="display:grid;grid-template-columns:1.6fr .9fr 1.1fr .7fr;align-items:center;padding:18px 0;border-bottom:1px solid var(--line);">
                    <span style="display:flex;align-items:center;gap:10px;font-size:16px;font-weight:600;color:var(--ink-900);"><span style="width:12px;height:12px;border-radius:3px;background:{{ $vColors[$i % count($vColors)] }};"></span>{{ $v['nombre'] }}</span>
                    <span class="num" style="text-align:right;font-size:15px;color:var(--ink-700);">{{ number_format($v['viajes'], 0, ',', '.') }}</span>
                    <span class="num" style="text-align:right;font-size:16px;font-weight:700;">{{ number_format($v['toneladas'] * 1000, 0, ',', '.') }} kg</span>
                    <span class="num" style="text-align:right;font-size:15px;font-weight:700;color:var(--g-700);">{{ number_format($v['porcentaje'], 1, ',', '.') }}%</span>
                </div>
                @endforeach
                <div style="display:grid;grid-template-columns:1.6fr .9fr 1.1fr .7fr;align-items:center;padding:16px 0 0;">
                    <span style="font-size:13px;font-weight:700;color:var(--ink-500);text-transform:uppercase;letter-spacing:.06em;">Total</span>
                    <span class="num" style="text-align:right;font-size:15px;font-weight:800;color:var(--ink-900);">{{ number_format($vehTotalViajes, 0, ',', '.') }}</span>
                    <span class="num" style="text-align:right;font-size:16px;font-weight:800;color:var(--ink-900);">{{ number_format($vehTotalKg, 0, ',', '.') }} kg</span>
                    <span class="num" style="text-align:right;font-size:15px;font-weight:800;color:var(--ink-900);">100%</span>
                </div>
                @if($vehMayorViajes && $vehMayorKg && $vehMayorViajes['nombre'] !== $vehMayorKg['nombre'])
                <div style="background:var(--g-50);border:1px solid var(--g-100);border-radius:12px;padding:14px 16px;margin-top:20px;font-size:12.5px;color:var(--ink-700);line-height:1.5;">El <strong>{{ mb_strtolower($vehMayorViajes['nombre']) }}</strong> hace más viajes ({{ number_format($vehMayorViajes['viajes'], 0, ',', '.') }}) pero el <strong>{{ mb_strtolower($vehMayorKg['nombre']) }}</strong> aporta más kilos: su carga media por viaje es mayor.</div>
                @endif
            </div>
        </div>

        {!! $pageFooter() !!}
    </div>
</section>

@endif

{{-- ═══════════ SEPARADOR · ZONAS Y SERVICIOS ═══════════ --}}
@if($grupoZonas)
<section class="page" style="color:#fff;background:radial-gradient(circle at 78% 12%, oklch(0.30 0.07 144), oklch(0.20 0.045 144) 72%);">
    <div style="position:absolute;right:-150px;top:-150px;width:560px;height:560px;border-radius:50%;border:1.5px solid rgba(255,255,255,.10);pointer-events:none;"></div>
    <div style="position:absolute;right:-60px;top:-60px;width:380px;height:380px;border-radius:50%;border:1.5px solid rgba(255,255,255,.08);pointer-events:none;"></div>
    <svg width="220" height="220" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.08)" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" style="position:absolute;right:92px;top:118px;pointer-events:none;">{!! $icons['layers'] !!}</svg>

    <div style="position:relative;flex:1;display:flex;flex-direction:column;justify-content:space-between;padding:20mm 22mm;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:40px;height:40px;border-radius:11px;background:rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;">
                    <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">{!! $icons['recycle'] !!}</svg>
                </div>
                <div style="line-height:1.15;">
                    <div style="font-size:17px;font-weight:800;">Infinito Reciclaje</div>
                    <div style="font-size:12px;color:rgba(255,255,255,.7);">{{ $tituloReporteCap }}</div>
                </div>
            </div>
            <span style="font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--g-300);">{{ $tituloReporte }} · {{ $periodo }}</span>
        </div>

        <div style="max-width:720px;">
            <div style="display:inline-flex;align-items:center;gap:9px;padding:7px 15px;border:1px solid rgba(255,255,255,.28);border-radius:100px;font-size:12px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.9);margin-bottom:26px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">{!! $icons['layers'] !!}</svg>
                Sección
            </div>
            <h2 style="margin:0;font-size:60px;line-height:1.04;font-weight:800;letter-spacing:-.025em;">Zonas y servicios</h2>
            <p style="margin:20px 0 0;font-size:18px;font-weight:500;line-height:1.5;color:rgba(255,255,255,.8);max-width:560px;">Desglose por tipo de servicio y por zona dentro de cada servicio.</p>
        </div>

        <div style="display:flex;align-items:center;gap:16px;">
            <span style="width:54px;height:2px;background:var(--g-300);"></span>
            <span style="font-size:12.5px;color:rgba(255,255,255,.62);">Registro de balanza del predio de disposición final · {{ $periodo }}</span>
        </div>
    </div>
</section>

@endif

{{-- ═══════════ 07 · QUÉ ES CADA SERVICIO ═══════════ --}}
@if($on('que_es_servicio'))
@php $svcIconOrder = ['house', 'file', 'bars', 'layers', 'pin']; @endphp
<section class="page">
    <div class="p-corner-ring"></div>
    <div class="p-body">
        {!! $pageHeader($numSeccion['que_es_servicio'], 'layers', 'Tipos de servicio') !!}
        <h2 class="p-title">¿Qué es cada servicio?</h2>
        <p class="p-desc"><span style="color:var(--ink-700);font-weight:600;">Los tipos de recolección.</span> Cada tipo de servicio agrupa los residuos que ingresan al predio de disposición final.</p>

        <div style="flex:1;display:flex;flex-direction:column;gap:12px;justify-content:flex-start;">
            @foreach($porServicio as $i => $svc)
            <div style="display:flex;gap:16px;align-items:flex-start;border:1px solid var(--line);border-radius:12px;padding:26px 22px;">
                <div style="flex-shrink:0;width:38px;height:38px;border-radius:10px;background:var(--g-100);color:var(--g-700);display:flex;align-items:center;justify-content:center;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $icons[$svcIconOrder[$i % count($svcIconOrder)]] !!}</svg>
                </div>
                <div style="flex:1;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="font-size:16px;font-weight:700;color:var(--ink-900);">{{ $svc['nombre'] }}</span>
                        @if($svc['zonas'] > 0)
                        <span class="num" style="font-size:11px;font-weight:700;background:var(--g-50);color:var(--g-700);padding:3px 9px;border-radius:100px;">{{ $svc['zonas'] }} {{ $svc['zonas'] === 1 ? 'zona' : 'zonas' }}</span>
                        @endif
                    </div>
                    <div style="font-size:13px;color:var(--ink-500);line-height:1.5;margin-top:3px;">{{ $svc['descripcion'] ?: 'Sin descripción cargada para este servicio.' }}</div>
                </div>
            </div>
            @endforeach
        </div>

        {!! $pageFooter() !!}
    </div>
</section>

@endif

{{-- ═══════════ 08 · CUÁNTO RECOLECTA CADA SERVICIO ═══════════ --}}
@if($on('recoleccion_servicio'))
@php
    $svcTop2 = $porServicio->take(2);
    $svcTop2Pct = $porServicio->sum('kg') > 0 ? round(($svcTop2->sum('kg') / $porServicio->sum('kg')) * 100) : 0;
@endphp
<section class="page">
    <div class="p-corner-ring"></div>
    <svg aria-hidden="true" width="230" height="230" viewBox="0 0 24 24" fill="none" stroke="var(--g-100)" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" style="position:absolute;right:-46px;bottom:-46px;pointer-events:none;">{!! $icons['recycle'] !!}</svg>
    <div class="p-body">
        {!! $pageHeader($numSeccion['recoleccion_servicio'], 'trending', 'Aporte por servicio') !!}
        <h2 class="p-title">¿Cuánto recolecta cada servicio?</h2>
        <p class="p-desc"><span style="color:var(--ink-700);font-weight:600;">Participación de cada servicio en el total.</span> Kilogramos recolectados por cada tipo de servicio y su participación en el total del período.</p>

        <div style="display:flex;flex-direction:column;justify-content:flex-start;gap:22px;margin-top:8px;">
            @foreach($porServicio as $svc)
            <div style="display:grid;grid-template-columns:190px 1fr 150px 66px;gap:18px;align-items:center;">
                <span style="font-size:16px;font-weight:600;color:var(--ink-900);">{{ $svc['nombre'] }}</span>
                <div style="height:40px;background:var(--g-50);border-radius:7px;overflow:hidden;">
                    <div style="height:100%;width:{{ $svcMax > 0 ? round(($svc['kg'] / $svcMax) * 100, 1) : 0 }}%;background:linear-gradient(90deg,var(--g-500),var(--g-700));border-radius:7px;"></div>
                </div>
                <span class="num" style="text-align:right;font-size:16px;font-weight:700;color:var(--ink-900);">{{ number_format($svc['kg'], 0, ',', '.') }} kg</span>
                <span class="num" style="text-align:right;font-size:16px;font-weight:800;color:var(--g-700);">{{ number_format($svc['porcentaje'], 1, ',', '.') }}%</span>
            </div>
            @endforeach
        </div>

        @if($svcTop2->count() >= 2)
        <div style="background:var(--g-900);border-radius:12px;padding:18px 22px;color:#fff;margin-top:8px;">
            <div style="font-size:13.5px;line-height:1.55;color:rgba(255,255,255,.9);">Los servicios <strong style="color:#fff;">{{ $svcTop2->pluck('nombre')->implode(' y ') }}</strong> concentran el {{ $svcTop2Pct }}% del total recolectado en el período.</div>
        </div>
        @endif

        {!! $pageFooter() !!}
    </div>
</section>

@endif

{{-- ═══════════ 09 · ZONAS DEL SERVICIO PRINCIPAL ═══════════ --}}
@if($topServicio && $on('zonas_servicio'))
@php
    $topZonas = collect($topServicio['zonas'])->take(12)->values();
    $topZonaMax = $topZonas->max('kg') ?: 1;
    $topResto = count($topServicio['zonas']) - $topZonas->count();
@endphp
<section class="page">
    <div class="p-corner-ring"></div>
    <div class="p-body">
        {!! $pageHeader($numSeccion['zonas_top'], 'pin', 'Zonas — '.$topServicio['nombre']) !!}
        <h2 class="p-title">Recolección por zona — {{ $topServicio['nombre'] }}</h2>
        <p class="p-desc">
            @if($topResto > 0)
                Se muestran las {{ $topZonas->count() }} zonas de mayor volumen sobre un total de {{ count($topServicio['zonas']) }}.
            @else
                Desglose por zona del servicio con mayor volumen del período.
            @endif
        </p>

        <div style="display:flex;gap:14px;margin-bottom:16px;">
            <div style="flex:1;background:var(--g-900);border-radius:12px;padding:16px 20px;color:#fff;">
                <div class="num" style="font-size:26px;font-weight:800;letter-spacing:-.02em;">{{ number_format($topServicio['kg'], 0, ',', '.') }} <span style="font-size:14px;font-weight:600;color:rgba(255,255,255,.7);">kg</span></div>
                <div style="font-size:12px;color:rgba(255,255,255,.7);margin-top:2px;">Kilogramos recolectados</div>
            </div>
            <div style="flex:1;background:var(--g-50);border:1px solid var(--g-100);border-radius:12px;padding:16px 20px;">
                <div class="num" style="font-size:26px;font-weight:800;letter-spacing:-.02em;color:var(--ink-900);">{{ number_format($topServicio['viajes'], 0, ',', '.') }}</div>
                <div style="font-size:12px;color:var(--ink-500);margin-top:2px;">Viajes de camiones</div>
            </div>
            <div style="flex:1;background:var(--g-50);border:1px solid var(--g-100);border-radius:12px;padding:16px 20px;">
                <div class="num" style="font-size:26px;font-weight:800;letter-spacing:-.02em;color:var(--ink-900);">{{ count($topServicio['zonas']) }}</div>
                <div style="font-size:12px;color:var(--ink-500);margin-top:2px;">Zonas</div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;column-gap:32px;row-gap:2px;align-content:start;margin-bottom:14px;">
            @foreach($topZonas as $z)
            <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--line);">
                <span style="width:150px;font-size:13.5px;font-weight:600;color:var(--ink-900);">{{ $z['label'] }}</span>
                <div style="flex:1;height:16px;background:var(--g-50);border-radius:4px;overflow:hidden;">
                    <div style="height:100%;width:{{ round(($z['kg'] / $topZonaMax) * 100, 1) }}%;background:linear-gradient(90deg,var(--g-500),var(--g-700));"></div>
                </div>
                <span class="num" style="width:78px;text-align:right;font-size:13px;font-weight:700;color:var(--ink-900);">{{ number_format($z['kg'], 0, ',', '.') }}</span>
            </div>
            @endforeach
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;background:var(--g-50);border:1px solid var(--g-100);border-radius:10px;padding:10px 16px;margin-top:12px;">
            <span style="font-size:12.5px;color:var(--ink-700);">
                @if($topResto > 0)
                    Se listan las {{ $topZonas->count() }} zonas principales · las {{ $topResto }} restantes suman el resto del total.
                @else
                    Todas las zonas del servicio están listadas.
                @endif
            </span>
            <span class="num" style="font-size:13px;font-weight:800;color:var(--ink-900);">Total: {{ number_format($topServicio['kg'], 0, ',', '.') }} kg · {{ number_format($topServicio['viajes'], 0, ',', '.') }} viajes · {{ count($topServicio['zonas']) }} zonas</span>
        </div>

        {!! $pageFooter() !!}
    </div>
</section>
@endif

{{-- ═══════════ 10 · ZONAS DE LOS OTROS SERVICIOS ═══════════ --}}
@if($on('zonas_servicio'))
@foreach($otrosChunks as $chunkIdx => $chunk)
<section class="page">
    <div class="p-corner-ring"></div>
    <div class="p-body">
        {!! $pageHeader($numSeccion['zonas_otros'], 'pin', 'Zonas — otros servicios'.(count($otrosChunks) > 1 ? ' ('.($chunkIdx + 1).'/'.count($otrosChunks).')' : '')) !!}
        <h2 class="p-title">{{ collect($chunk)->pluck('nombre')->implode(', ') }}</h2>
        <p class="p-desc">Desglose por zona de los servicios restantes, según el registro de balanza del período.</p>

        <div style="flex:1;display:grid;grid-template-columns:1fr 1fr;gap:16px;min-height:0;align-items:start;align-content:start;">
            @foreach($chunk as $svc)
            @php
                $zonasSvc = collect($svc['zonas']);
                $zonasSvcMax = $zonasSvc->max('kg') ?: 1;
                $unidad = count($svc['zonas']) === 1 ? 'zona' : 'zonas';
            @endphp
            @if($zonasSvc->count() <= 1)
            <div style="border:1px solid var(--line);border-radius:12px;padding:16px 18px;background:var(--g-50);">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <div style="font-size:15px;font-weight:800;color:var(--ink-900);">{{ $svc['nombre'] }}</div>
                        <div style="font-size:12px;color:var(--ink-500);margin-top:2px;">{{ count($svc['zonas']) }} {{ $unidad }}</div>
                    </div>
                    <div style="text-align:right;">
                        <div class="num" style="font-size:20px;font-weight:800;color:var(--ink-900);">{{ number_format($svc['kg'], 0, ',', '.') }} <span style="font-size:12px;font-weight:600;color:var(--ink-500);">kg</span></div>
                        <div class="num" style="font-size:12px;color:var(--ink-500);">{{ number_format($svc['viajes'], 0, ',', '.') }} viajes</div>
                    </div>
                </div>
            </div>
            @else
            <div style="border:1px solid var(--line);border-radius:12px;padding:16px 18px;display:flex;flex-direction:column;">
                <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:10px;">
                    <span style="font-size:15px;font-weight:800;color:var(--ink-900);">{{ $svc['nombre'] }}</span>
                    <span class="num" style="font-size:12px;color:var(--ink-500);">{{ number_format($svc['kg'], 0, ',', '.') }} kg · {{ number_format($svc['viajes'], 0, ',', '.') }} viajes · {{ count($svc['zonas']) }} {{ $unidad }}</span>
                </div>
                <div style="display:flex;flex-direction:column;flex:1;">
                    @foreach($zonasSvc->take(9) as $zi => $z)
                    <div style="display:flex;align-items:center;gap:10px;padding:5.5px 0;{{ $zi < min($zonasSvc->count(), 9) - 1 ? 'border-bottom:1px solid var(--line);' : '' }}">
                        <span style="flex:1;font-size:12.5px;color:var(--ink-900);">{{ $z['label'] }}</span>
                        <div style="width:80px;height:12px;background:var(--g-50);border-radius:3px;overflow:hidden;">
                            <div style="height:100%;width:{{ round(($z['kg'] / $zonasSvcMax) * 100, 1) }}%;background:var(--g-600);"></div>
                        </div>
                        <span class="num" style="width:64px;text-align:right;font-size:12.5px;font-weight:700;">{{ number_format($z['kg'], 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            @endforeach
        </div>

        {!! $pageFooter() !!}
    </div>
</section>
@endforeach
@endif

{{-- ═══════════ 11 · CIERRE ═══════════ --}}
<section class="page" style="color:#fff;background:radial-gradient(circle at 80% 15%, oklch(0.30 0.07 144), oklch(0.20 0.045 144) 72%);">
    <div style="position:absolute;right:-160px;bottom:-160px;width:520px;height:520px;border-radius:50%;border:1.5px solid rgba(255,255,255,.10);pointer-events:none;"></div>
    <div style="position:absolute;right:-40px;bottom:-40px;width:300px;height:300px;border-radius:50%;border:1.5px solid rgba(255,255,255,.08);pointer-events:none;"></div>
    <svg width="196" height="196" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.07)" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" style="position:absolute;right:70px;bottom:70px;pointer-events:none;">{!! $icons['recycle'] !!}</svg>

    <div style="flex:1;display:flex;flex-direction:column;justify-content:space-between;padding:18mm 22mm;position:relative;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:40px;height:40px;border-radius:11px;background:rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;">
                    <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">{!! $icons['recycle'] !!}</svg>
                </div>
                <div style="line-height:1.15;">
                    <div style="font-size:17px;font-weight:800;">Infinito Reciclaje</div>
                    <div style="font-size:12px;color:rgba(255,255,255,.7);">{{ $tituloReporteCap }}</div>
                </div>
            </div>
            <span style="font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--g-300);">{{ $totalPaginas }} · Cierre</span>
        </div>

        <div>
            <div style="font-size:12px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:var(--g-300);margin-bottom:16px;">Síntesis del período</div>
            <h2 style="margin:0;font-size:46px;line-height:1.1;font-weight:800;letter-spacing:-.02em;max-width:820px;"><span class="num">{{ number_format($kgTotal, 0, ',', '.') }} kg</span> netos gestionados en <span class="num">{{ number_format($kpis['total'], 0, ',', '.') }}</span> ingresos a lo largo de {{ $kpis['dias_op'] }} días de operación.</h2>
            <p style="margin:18px 0 0;font-size:15px;line-height:1.6;color:rgba(255,255,255,.78);max-width:660px;">Muchas gracias. Reporte generado a partir del registro de balanza del predio de disposición final · {{ $periodo }}.</p>
        </div>

        <div style="display:grid;grid-template-columns:1.3fr 1fr 1fr;gap:32px;align-items:end;">
            <div style="border-top:1px solid rgba(255,255,255,.2);padding-top:16px;">
                <div style="font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--g-300);margin-bottom:8px;">Nota metodológica</div>
                <div style="font-size:12.5px;line-height:1.6;color:rgba(255,255,255,.75);">Datos extraídos del registro de balanza. Neto = bruto de entrada − tara del padrón. Formato es-AR. Todos los pesajes son trazables y auditables.</div>
            </div>
            <div style="border-top:1px solid rgba(255,255,255,.2);padding-top:16px;">
                <div style="font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--g-300);margin-bottom:8px;">Elaboración</div>
                <div style="font-size:13.5px;font-weight:600;">Infinito Reciclaje</div>
                <div style="font-size:12.5px;color:rgba(255,255,255,.7);margin-top:2px;">Período: {{ $periodo }}</div>
            </div>
            <div style="border-top:1px solid rgba(255,255,255,.2);padding-top:16px;">
                <div style="font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--g-300);margin-bottom:8px;">Recepción</div>
                <div style="height:34px;border-bottom:1px solid rgba(255,255,255,.3);"></div>
                <div style="font-size:11.5px;color:rgba(255,255,255,.6);margin-top:6px;">{{ $organizacion }} · Firma y sello</div>
            </div>
        </div>
    </div>
</section>

</body>
</html>
