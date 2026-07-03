@props(['reporte', 'filters', 'zonas', 'tiposServicio', 'tiposVehiculo', 'activeFilters'])

@if($reporte)
    @php
        $exportParams = array_filter(request()->only(['desde', 'hasta', 'zona_id', 'tipo_servicio_id', 'tipo_vehiculo_id']));
        $pills = array_filter([
            $filters['zona_id']          ? $zonas->firstWhere('id', $filters['zona_id'])?->nombre           : null,
            $filters['tipo_servicio_id'] ? $tiposServicio->firstWhere('id', $filters['tipo_servicio_id'])?->nombre : null,
            $filters['tipo_vehiculo_id'] ? $tiposVehiculo->firstWhere('id', $filters['tipo_vehiculo_id'])?->nombre : null,
        ]);
    @endphp

    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-overline">Informe de recolección</p>
            <h2 class="text-h3 mt-1">
                {{ $reporte['desde']->translatedFormat('d M') }} — {{ $reporte['hasta']->translatedFormat('d M Y') }}
            </h2>
            @if(!empty($pills))
                <div class="flex flex-wrap items-center gap-1.5 mt-2">
                    @foreach($pills as $pill)
                        <x-ui.badge variant="secondary">{{ $pill }}</x-ui.badge>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="flex items-center justify-end gap-2 sm:shrink-0">
            {{-- KPIs (mobile/tablet) + cambiar período: ghost solo-ícono, estilo dashboard --}}
            <x-domain.reportes.mobile-kpis :kpis="$reporte['kpis']" />

            <x-ui.button variant="ghost" size="icon" @click="filterOpen = true" class="relative md:hidden" aria-label="Cambiar período">
                <x-lucide-calendar-days class="size-4" />
                @if($activeFilters > 0)
                    <span class="absolute -top-1 -right-1 flex size-4 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground leading-none">
                        {{ $activeFilters }}
                    </span>
                @endif
            </x-ui.button>

            {{-- Exportar: con label --}}
            <x-ui.button variant="outline" href="{{ route('admin.reportes.excel', $exportParams) }}">
                <x-lucide-table class="size-4" />
                <span>Excel</span>
            </x-ui.button>

            <x-ui.button href="{{ route('admin.reportes.pdf-presentacion', $exportParams) }}">
                <x-lucide-file-text class="size-4" />
                <span>PDF</span>
            </x-ui.button>
        </div>
    </div>
@endif
