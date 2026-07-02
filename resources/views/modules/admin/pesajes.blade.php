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

    @php
        $hayFiltros = $filtros['desde']
            || $filtros['hasta']
            || $filtros['patente']
            || $filtros['estado']
            || $filtros['operario_id']
            || ($filtros['zona_id'] ?? null)
            || ($filtros['tipo_servicio_id'] ?? null)
            || ($filtros['solo_alerta'] ?? null)
            || ($filtros['solo_editados'] ?? null);

        $hayFiltrosMod = $filtrosMod['tipo']
            || $filtrosMod['desde']
            || $filtrosMod['hasta']
            || $filtrosMod['patente']
            || $filtrosMod['operario_id']
            || $filtrosMod['zona_id']
            || $filtrosMod['tipo_servicio_id'];
    @endphp

    <div x-data="historial()">
        <x-ui.tabs
            :value="$tab"
            class="flex flex-col gap-6"
            x-init="$watch('active', val => {
                const url = new URL(window.location);
                url.searchParams.set('tab', val);
                history.pushState({}, '', url);
            })"
        >

            <div class="flex flex-col items-start gap-2">
                <x-ui.typography as="h2">Pesajes</x-ui.typography>
                <x-ui.typography as="muted">Ingresos y egresos registrados en la balanza, y el detalle de las modificaciones.</x-ui.typography>
            </div>

            <x-ui.tabs.list class="flex w-full sm:w-fit">
                <x-ui.tabs.trigger value="pesajes" class="flex-1 sm:flex-none">
                    <x-lucide-scale class="size-4" />
                    Pesajes
                </x-ui.tabs.trigger>
                <x-ui.tabs.trigger value="modificaciones" class="flex-1 sm:flex-none">
                    <x-lucide-file-pen-line class="size-4" />
                    Modificaciones
                </x-ui.tabs.trigger>
            </x-ui.tabs.list>

            {{-- Tab: Pesajes (con KPIs) --}}
            <x-ui.tabs.content value="pesajes" class="mt-0 flex flex-col gap-6">

                <div class="flex items-center justify-end gap-1 xl:hidden">
                    <x-ui.tooltip content="Métricas">
                        <x-ui.button variant="ghost" size="icon" @click="metricasOpen = true">
                            <x-lucide-chart-bar class="size-4" />
                        </x-ui.button>
                    </x-ui.tooltip>
                </div>

                <x-domain.historial.mobile-drawers :kpis="$kpis" />

                <x-domain.historial.kpis :kpis="$kpis" />

                <x-domain.historial.filtros :filtros="$filtros" :operarios="$operarios" :hayFiltros="$hayFiltros" :routeHistorial="$routeHistorial" :zonas="$zonas" :tiposServicio="$tiposServicio" :sortDirection="$filtros['direction']" />

                <x-domain.historial.tabla :pesajes="$pesajes" :hayFiltros="$hayFiltros" :routeHistorial="$routeHistorial" :sortDirection="$filtros['direction']" />
            </x-ui.tabs.content>

            {{-- Tab: Modificaciones (sin KPIs) --}}
            <x-ui.tabs.content value="modificaciones" class="mt-0 flex flex-col gap-6">

                <x-domain.modificaciones.filtros :filtros="$filtrosMod" :operarios="$operarios" :hayFiltros="$hayFiltrosMod" :zonas="$zonas" :tiposServicio="$tiposServicio" :sortDirection="$filtrosMod['direction']" control="filterOpenMod" />

                <x-domain.historial.tabla
                    :pesajes="$modificaciones"
                    :hayFiltros="$hayFiltrosMod"
                    :routeHistorial="$routeHistorial . '?tab=modificaciones'"
                    :sortDirection="$filtrosMod['direction']"
                    pageParam="m_page"
                    directionParam="m_direction"
                    returnTab="modificaciones"
                    emptyIcon="file-pen-line"
                    emptyTitle="Sin modificaciones"
                    emptyDescription="Acá vas a ver los pesajes que se editaron o cancelaron."
                />
            </x-ui.tabs.content>

        </x-ui.tabs>

        {{-- Dialogs: una sola instancia para ambos tabs (estado en historial()) --}}
        <x-domain.historial.dialog-egreso />

        <x-domain.historial.dialog-cambios />

        <x-domain.historial.dialog-cancelar />
    </div>
</x-layouts.app>
