@php
    $activeTab  = $errors->any() ? old('_tab', 'vehiculos') : $tab;
    $vHasErrors = $errors->any() && $activeTab === 'vehiculos';
    $tHasErrors = $errors->any() && $activeTab === 'tipos';

    $vInitial = $vHasErrors ? [
        'modalOpen' => true,
        'modalMode' => old('_mode') === 'edit' ? 'edit' : 'create',
        'form'      => [
            'id'               => (int) old('_editing_id', 0) ?: null,
            'patente'          => old('patente', ''),
            'numero_interno'   => old('numero_interno', ''),
            'tara_kg'          => old('tara_kg', ''),
            'tipo_vehiculo_id' => old('tipo_vehiculo_id', ''),
            'titular'          => old('titular', ''),
            'capacidad_kg'     => old('capacidad_kg', ''),
            'observaciones'    => old('observaciones', ''),
        ],
    ] : [];

    $tInitial = $tHasErrors ? [
        'modalOpen' => true,
        'modalMode' => old('_mode') === 'edit' ? 'edit' : 'create',
        'form'      => [
            'id'          => (int) old('_editing_id', 0) ?: null,
            'nombre'      => old('nombre', ''),
            'peso_min_kg' => old('peso_min_kg', ''),
            'peso_max_kg' => old('peso_max_kg', ''),
        ],
    ] : [];

    $vHayFiltros    = collect($filters)->filter(fn($v) => $v !== null && $v !== '')->isNotEmpty();
    $vActiveFilters = count(array_filter($filters, fn($v) => $v !== null && $v !== ''));

    $tHayFiltros    = collect($tiposFiltros)->filter(fn($v) => $v !== null && $v !== '')->isNotEmpty();
    $tActiveFilters = count(array_filter($tiposFiltros, fn($v) => $v !== null && $v !== ''));
@endphp

<x-layouts.app title="Vehículos">
<div
    x-data="{
        tab: '{{ $activeTab }}',
        switchTab(t) {
            this.tab = t;
            const url = new URL(window.location);
            url.searchParams.set('tab', t);
            history.pushState({}, '', url);
        }
    }"
    class="flex flex-col gap-6"
>

    {{-- Tabs --}}
    <div role="tablist" class="inline-flex h-9 w-fit items-center justify-center rounded-lg bg-muted p-1 text-muted-foreground">
        <button
            role="tab"
            :aria-selected="tab === 'vehiculos'"
            @click="switchTab('vehiculos')"
            :class="tab === 'vehiculos' ? 'bg-background text-foreground shadow-sm' : 'hover:text-foreground/80'"
            class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded-md px-3 py-1 text-sm font-medium transition-all"
        >
            <x-lucide-truck class="size-4" />
            Vehículos
        </button>
        <button
            role="tab"
            :aria-selected="tab === 'tipos'"
            @click="switchTab('tipos')"
            :class="tab === 'tipos' ? 'bg-background text-foreground shadow-sm' : 'hover:text-foreground/80'"
            class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded-md px-3 py-1 text-sm font-medium transition-all"
        >
            <x-lucide-car class="size-4" />
            Tipos
        </button>
    </div>

    {{-- Tab: Vehículos --}}
    <div x-show="tab === 'vehiculos'" x-data="vehiculos({{ Js::from($vInitial) }})" class="flex flex-col gap-6">

        <x-domain.vehiculos.mobile-drawers
            :filters="$filters"
            :tiposVehiculo="$tiposVehiculo"
            :hayFiltros="$vHayFiltros"
            :activeFilters="$vActiveFilters"
        />

        <x-domain.vehiculos.tabla :vehiculos="$vehiculos" :activeFilters="$vActiveFilters" />

        <x-domain.vehiculos.drawer-filtros :filters="$filters" :tiposVehiculo="$tiposVehiculo" />
        <x-domain.vehiculos.modal :tiposVehiculo="$tiposVehiculo" />
        <x-domain.vehiculos.modal-confirm />
        <x-domain.vehiculos.modal-delete />

    </div>

    {{-- Tab: Tipos de vehículo --}}
    <div x-show="tab === 'tipos'" x-data="tiposVehiculo({{ Js::from($tInitial) }})" class="flex flex-col gap-6">

        <x-domain.tipos-vehiculo.mobile-drawers
            :filters="$tiposFiltros"
            :hayFiltros="$tHayFiltros"
            :activeFilters="$tActiveFilters"
        />

        <x-domain.tipos-vehiculo.tabla :tipos="$tipos" :activeFilters="$tActiveFilters" />

        <x-domain.tipos-vehiculo.drawer-filtros :filters="$tiposFiltros" />
        <x-domain.tipos-vehiculo.modal />
        <x-domain.tipos-vehiculo.modal-confirm />
        <x-domain.tipos-vehiculo.modal-delete />

    </div>

</div>
</x-layouts.app>
