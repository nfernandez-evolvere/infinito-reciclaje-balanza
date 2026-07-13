@props(['hayFiltros', 'activeFilters'])

{{--
    Fila de acción del tab Tipos de vehículo: trigger de filtros (solo <md, abre el
    sheet). El botón de alta vive en el header de la página (comparte fila con el
    título). En md+ el trigger se oculta porque los filtros viven en el panel inline
    (<x-domain.tipos-vehiculo.filtros>) y la fila entera desaparece.
--}}

<div class="flex items-center gap-2 md:hidden">
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
