@props(['hayFiltros', 'activeFilters'])

{{--
    Fila de acción de la pantalla: trigger de filtros (solo <md, abre el sheet).
    En md+ se oculta porque los filtros viven en el panel inline
    (<x-domain.usuarios.filtros>). El botón de alta vive en el header.
--}}

<div class="flex items-center justify-end gap-2 md:hidden">
    <x-ui.button variant="outline" @click="filterOpen = true" class="relative">
        <x-lucide-sliders-horizontal class="size-4" />
        Filtros
        @if($activeFilters > 0)
            <span class="pointer-events-none absolute -top-1.5 -right-1.5 flex size-4 items-center justify-center rounded-full bg-primary text-primary-foreground ring-2 ring-background text-[10px] font-semibold leading-none">
                {{ $activeFilters }}
            </span>
        @endif
    </x-ui.button>
</div>
