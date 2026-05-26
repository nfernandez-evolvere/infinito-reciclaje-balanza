<x-layouts.app :title="$titulo">

    <x-slot:footerTurno>
        <span>Pesajes hoy: <b class="text-foreground">{{ $kpisHoy['total'] }}</b></span>
        <x-ui.separator orientation="vertical" class="h-3.5 hidden sm:block" />
        <span>Netas: <b class="text-foreground">{{ number_format($kpisHoy['toneladas_netas'], 1, ',', '.') }} t</b></span>
        <x-ui.separator orientation="vertical" class="h-3.5 hidden sm:block" />
        <span>En predio: <b class="text-foreground">{{ $kpisHoy['en_predio'] }}</b></span>
    </x-slot:footerTurno>

    <x-slot:footerUltimo>
        @if($ultimoPesaje)
            <div class="flex items-center justify-between w-full sm:w-auto sm:gap-2">
                <span>Último: <b class="text-foreground">{{ $ultimoPesaje->vehiculo->patente }}</b></span>
                <div class="flex items-center gap-2">
                    <span>{{ number_format($ultimoPesaje->peso_neto_kg, 0, ',', '.') }} kg</span>
                    <span class="text-muted-foreground/60">{{ $ultimoPesaje->created_at->format('H:i') }}</span>
                </div>
            </div>
        @endif
    </x-slot:footerUltimo>

<div class="flex flex-col gap-6" x-data="historial()">

    @php
        $esHoy = $filtros['desde'] === today()->toDateString() && $filtros['hasta'] === today()->toDateString();
        $hayFiltros = !$esHoy
            || $filtros['patente']
            || $filtros['estado']
            || $filtros['operario_id']
            || ($filtros['zona_id'] ?? null)
            || ($filtros['tipo_servicio_id'] ?? null)
            || ($filtros['solo_alerta'] ?? null)
            || ($filtros['solo_editados'] ?? null);
        $subtitulo = $esHoy
            ? 'Pesajes de hoy · ' . now()->format('d/m/Y')
            : ($filtros['desde'] === $filtros['hasta']
                ? 'Pesajes del ' . \Carbon\Carbon::parse($filtros['desde'])->format('d/m/Y')
                : 'Pesajes del ' . \Carbon\Carbon::parse($filtros['desde'])->format('d/m') . ' al ' . \Carbon\Carbon::parse($filtros['hasta'])->format('d/m/Y'));
        $exportUrl     = $exportUrl ?? null;
        $zonas         = $zonas ?? collect();
        $tiposServicio = $tiposServicio ?? collect();
    @endphp

    <div class="flex items-start justify-between gap-4">
        <div>
            <x-ui.typography as="h2">{{ $titulo }}</x-ui.typography>
            <x-ui.typography as="muted" class="mt-1">{{ $subtitulo }}</x-ui.typography>
        </div>
        @if($exportUrl)
            <x-ui.button variant="outline" size="sm" href="{{ $exportUrl . '?' . http_build_query(array_filter($filtros)) }}">
                <x-lucide-download class="size-3.5" />
                Exportar Excel
            </x-ui.button>
        @endif
    </div>

    <x-domain.historial.mobile-drawers :kpis="$kpis" :filtros="$filtros" :operarios="$operarios" :hayFiltros="$hayFiltros" :routeHistorial="$routeHistorial" />

    <x-domain.historial.kpis :kpis="$kpis" />

    <x-domain.historial.filtros :filtros="$filtros" :operarios="$operarios" :hayFiltros="$hayFiltros" :routeHistorial="$routeHistorial" :zonas="$zonas" :tiposServicio="$tiposServicio" />

    <x-domain.historial.tabla :pesajes="$pesajes" :hayFiltros="$hayFiltros" :routeHistorial="$routeHistorial" />

    <x-domain.historial.dialog-egreso />

    <x-domain.historial.dialog-cambios />

</div>
</x-layouts.app>
