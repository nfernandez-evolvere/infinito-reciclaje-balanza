<x-layouts.app :title="$titulo">

<div class="flex flex-col gap-6" x-data="historial()">

    @php
        $hayFiltros = $filtros['tipo']
            || $filtros['desde']
            || $filtros['hasta']
            || $filtros['patente']
            || $filtros['operario_id']
            || $filtros['zona_id']
            || $filtros['tipo_servicio_id'];

        $universo = match($filtros['tipo']) {
            'editado'   => 'Pesajes editados',
            'cancelado' => 'Pesajes cancelados',
            default     => 'Pesajes editados o cancelados',
        };

        if ($filtros['desde'] && $filtros['hasta']) {
            $rango = $filtros['desde'] === $filtros['hasta']
                ? ' · ' . \Carbon\Carbon::parse($filtros['desde'])->format('d/m/Y')
                : ' · del ' . \Carbon\Carbon::parse($filtros['desde'])->format('d/m') . ' al ' . \Carbon\Carbon::parse($filtros['hasta'])->format('d/m/Y');
        } elseif ($filtros['desde']) {
            $rango = ' · desde el ' . \Carbon\Carbon::parse($filtros['desde'])->format('d/m/Y');
        } elseif ($filtros['hasta']) {
            $rango = ' · hasta el ' . \Carbon\Carbon::parse($filtros['hasta'])->format('d/m/Y');
        } else {
            $rango = '';
        }
        $subtitulo = $universo . $rango;
    @endphp

    <div class="flex flex-col lg:flex-row items-start justify-between gap-4">
        <div class="flex-1 min-w-0">
            <x-ui.typography as="h2" class="truncate">{{ $titulo }}</x-ui.typography>
            <x-ui.typography as="muted" class="mt-1 truncate">{{ $subtitulo }}</x-ui.typography>
        </div>
        <div class="flex items-center justify-end gap-1 w-full lg:w-auto shrink-0">
            <x-domain.modificaciones.filtros
                :filtros="$filtros"
                :operarios="$operarios"
                :hayFiltros="$hayFiltros"
                :routeModificaciones="$routeModificaciones"
                :zonas="$zonas"
                :tiposServicio="$tiposServicio"
                :sortDirection="$filtros['direction']"
            />
        </div>
    </div>

    <x-domain.historial.tabla
        :pesajes="$pesajes"
        :hayFiltros="$hayFiltros"
        :routeHistorial="$routeModificaciones"
        :sortDirection="$filtros['direction']"
        emptyIcon="file-pen-line"
        emptyTitle="Sin modificaciones"
        emptyDescription="Acá vas a ver los pesajes que se editaron o cancelaron."
    />

    <x-domain.historial.dialog-egreso />

    <x-domain.historial.dialog-cambios />

    <x-domain.historial.dialog-cancelar />

</div>
</x-layouts.app>
