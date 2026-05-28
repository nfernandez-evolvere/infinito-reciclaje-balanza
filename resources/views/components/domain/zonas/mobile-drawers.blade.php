@props(['filters', 'hayFiltros'])

<div class="hidden sm:flex items-center justify-end gap-2 shrink-0">
    <x-ui.button variant="ghost" @click="filterOpen = true" class="relative">
        <x-lucide-sliders-horizontal class="size-4" />
        Filtros
        @if($hayFiltros)
            <span class="absolute -top-1 -right-1 flex size-2 rounded-full bg-primary"></span>
        @endif
    </x-ui.button>
    <x-ui.button @click="openCreate()">
        <x-lucide-plus class="size-4" />
        Agregar zona
    </x-ui.button>
</div>

<div class="flex justify-end gap-2 sm:hidden">
    <x-ui.button variant="ghost" size="icon" @click="filterOpen = true" class="relative">
        <x-lucide-sliders-horizontal class="size-3.5" />
        @if($hayFiltros)
            <span class="absolute -top-1 -right-1 flex size-2 rounded-full bg-primary"></span>
        @endif
    </x-ui.button>
    <x-ui.button size="icon" @click="openCreate()">
        <x-lucide-plus class="size-3.5" />
    </x-ui.button>
</div>
