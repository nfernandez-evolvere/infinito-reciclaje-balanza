@props(['hayFiltros', 'activeFilters'])

{{--
    Cluster de acción (vive en el header, junto al título): trigger de filtros
    (solo <md, abre el sheet) + botón de alta. En md+ el trigger se oculta porque
    los filtros viven en el panel inline (<x-domain.tipos-servicio.filtros>).
--}}

<div class="flex items-center gap-2 shrink-0">
    <div class="md:hidden">
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

    <x-ui.button @click="openCreate()">
        <x-lucide-plus class="size-4" />
        Agregar tipo
    </x-ui.button>
</div>
