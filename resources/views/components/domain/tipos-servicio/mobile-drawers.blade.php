@props(['filters', 'tiposVehiculo', 'hayFiltros', 'activeFilters'])

<div class="hidden sm:flex items-center justify-end gap-2 shrink-0">
    <x-ui.button variant="ghost" @click="filterOpen = true" class="relative">
        <x-lucide-sliders-horizontal class="size-4" />
        Filtros
        @if($activeFilters > 0)
            <span class="absolute -top-1 -right-1 flex size-4 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground leading-none">
                {{ $activeFilters }}
            </span>
        @endif
    </x-ui.button>
    <x-ui.button @click="openCreate()">
        <x-lucide-plus class="size-4" />
        Agregar tipo
    </x-ui.button>
</div>

<div class="flex justify-end gap-2 sm:hidden">
    <x-ui.button variant="ghost" size="icon" @click="filterOpen = true" class="relative">
        <x-lucide-sliders-horizontal class="size-3.5" />
        @if($hayFiltros)
            <x-ui.badge variant="secondary" class="absolute -top-1 -right-1 size-3.5 p-0 flex items-center justify-center text-[9px]">{{ $activeFilters }}</x-ui.badge>
        @endif
    </x-ui.button>
    <x-ui.button size="icon" @click="openCreate()">
        <x-lucide-plus class="size-3.5" />
    </x-ui.button>
</div>
