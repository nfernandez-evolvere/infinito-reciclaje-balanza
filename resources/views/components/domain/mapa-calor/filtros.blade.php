@props(['desde', 'hasta'])

@php
    // El período siempre tiene valor (el controller define defaults: mes en curso).
    // El badge se muestra solo cuando el usuario aplicó un rango explícito por query.
    $hayFiltros = request()->filled('desde') || request()->filled('hasta');
@endphp

{{-- Trigger — vive bajo el x-data="mapaCalor" del padre, que expone filterOpen --}}
<div class="relative">
    <x-ui.tooltip content="Filtros" class="sm:hidden">
        <x-ui.button variant="ghost" @click="filterOpen = true">
            <x-lucide-sliders-horizontal class="size-4" />
        </x-ui.button>
    </x-ui.tooltip>
    <x-ui.button class="hidden sm:flex gap-1.5" @click="filterOpen = true">
        <x-lucide-sliders-horizontal class="size-4" />
        Filtros
    </x-ui.button>
    @if($hayFiltros)
        <span class="pointer-events-none absolute -top-1.5 -right-1.5 flex size-4 items-center justify-center rounded-full bg-primary text-primary-foreground ring-2 ring-background text-[10px] font-semibold leading-none">
            1
        </span>
    @endif
</div>

<x-ui.filter-sheet
    controlledBy="filterOpen"
    action="{{ route('admin.mapa-calor.index') }}"
    resetUrl="{{ route('admin.mapa-calor.index') }}"
>
    <x-ui.form-field>
        <x-ui.label>Desde</x-ui.label>
        <x-ui.date-picker name="desde" value="{{ $desde->format('Y-m-d') }}" placeholder="Desde" />
    </x-ui.form-field>

    <x-ui.form-field>
        <x-ui.label>Hasta</x-ui.label>
        <x-ui.date-picker name="hasta" value="{{ $hasta->format('Y-m-d') }}" placeholder="Hasta" />
    </x-ui.form-field>
</x-ui.filter-sheet>
