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
<x-ui.tabs
    :value="$activeTab"
    class="flex flex-col gap-6"
    x-init="$watch('active', val => {
        const url = new URL(window.location);
        url.searchParams.set('tab', val);
        history.pushState({}, '', url);
    })"
>

    <div class="flex flex-col items-start gap-2">
        <x-ui.typography as="h2">Vehículos</x-ui.typography>
        <x-ui.typography as="muted">Padrón de vehículos habilitados para operar en la balanza y sus categorías.</x-ui.typography>
    </div>

    <x-ui.tabs.list class="flex w-full sm:w-fit">
        <x-ui.tabs.trigger value="vehiculos" class="flex-1 sm:flex-none">
            <x-lucide-truck class="size-4" />
            Vehículos
        </x-ui.tabs.trigger>
        <x-ui.tabs.trigger value="tipos" class="flex-1 sm:flex-none">
            <x-lucide-car class="size-4" />
            Tipos
        </x-ui.tabs.trigger>
    </x-ui.tabs.list>

    {{-- Tab: Vehículos --}}
    <x-ui.tabs.content value="vehiculos" class="mt-0">
        <div x-data="vehiculos({{ Js::from($vInitial) }})" class="flex flex-col gap-6">

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
    </x-ui.tabs.content>

    {{-- Tab: Tipos de vehículo --}}
    <x-ui.tabs.content value="tipos" class="mt-0">
        <div x-data="tiposVehiculo({{ Js::from($tInitial) }})" class="flex flex-col gap-6">

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
    </x-ui.tabs.content>

</x-ui.tabs>
</x-layouts.app>
