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
            <h2 class="text-h2 mt-1">
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

        <div class="flex items-center gap-2 shrink-0">
            <x-ui.button variant="ghost" size="sm" @click="filterOpen = true" class="relative">
                <x-lucide-calendar-days class="size-4" />
                <span>Cambiar período</span>
                @if($activeFilters > 0)
                    <span class="absolute -top-1 -right-1 flex size-4 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground leading-none">
                        {{ $activeFilters }}
                    </span>
                @endif
            </x-ui.button>

            <x-ui.button variant="outline" size="sm" href="{{ route('admin.reportes.excel', $exportParams) }}">
                <x-lucide-table class="size-4" />
                <span class="hidden sm:inline">Excel</span>
            </x-ui.button>

            <x-ui.button size="sm" href="{{ route('admin.reportes.pdf-presentacion', $exportParams) }}">
                <x-lucide-presentation class="size-4" />
                <span class="hidden sm:inline">PDF</span>
            </x-ui.button>
        </div>
    </div>
@endif
