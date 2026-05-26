@props(['zonas', 'filters' => []])

@php
    $totalHa      = $zonas->sum('hectareas');
    $haFormateadas = $totalHa > 0 ? number_format($totalHa, 2, ',', '.') . ' ha en total' : null;
    $activeFilters = count(array_filter($filters, fn($v) => $v !== null && $v !== ''));
@endphp

<div class="flex items-end justify-between gap-4">
    <div>
        <x-ui.page-header
            title="Zonas operativas"
            :description="'Zonas geográficas de recolección.' . ($haFormateadas ? ' · ' . $haFormateadas . '.' : '')"
        />
    </div>
    <div class="flex items-center gap-2 shrink-0">
        <x-ui.button variant="secondary" @click="filterOpen = true" class="relative">
            <x-lucide-sliders-horizontal class="size-4" />
            <span class="hidden sm:inline">Filtros</span>
            @if($activeFilters > 0)
                <span class="absolute -top-1 -right-1 flex size-4 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground leading-none">
                    {{ $activeFilters }}
                </span>
            @endif
        </x-ui.button>
        <x-ui.button @click="openCreate()">
            <x-lucide-plus class="size-4" />
            <span class="hidden sm:inline">Agregar zona</span>
        </x-ui.button>
    </div>
</div>
