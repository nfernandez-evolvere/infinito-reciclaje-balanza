@props(['filters', 'tiposVehiculo'])

<x-ui.filter-sheet
    controlled-by="filterOpen"
    action="{{ route('admin.tipos-servicio.index') }}"
    reset-url="{{ route('admin.tipos-servicio.index') }}"
>
    <x-ui.form-field for="filter-nombre">
        <x-ui.label for="filter-nombre">Nombre</x-ui.label>
        <x-ui.input
            id="filter-nombre"
            name="nombre"
            type="search"
            placeholder="Buscar por nombre…"
            :value="$filters['nombre'] ?? ''"
            autofocus
        />
    </x-ui.form-field>

    <x-ui.form-field for="filter-tipo-vehiculo">
        <x-ui.label for="filter-tipo-vehiculo">Vehículo habitual</x-ui.label>
        <x-ui.select name="tipo_vehiculo_id" :value="$filters['tipo_vehiculo_id'] ?? ''">
            <x-ui.select.trigger id="filter-tipo-vehiculo">
                <x-ui.select.value placeholder="Todos" />
            </x-ui.select.trigger>
            <x-ui.select.content>
                <x-ui.select.item value="">Todos</x-ui.select.item>
                @foreach($tiposVehiculo as $tv)
                    <x-ui.select.item value="{{ $tv->id }}">{{ $tv->nombre }}</x-ui.select.item>
                @endforeach
            </x-ui.select.content>
        </x-ui.select>
    </x-ui.form-field>

    <x-ui.form-field for="filter-activo">
        <x-ui.label for="filter-activo">Estado</x-ui.label>
        <x-ui.select name="activo" :value="$filters['activo'] ?? ''">
            <x-ui.select.trigger id="filter-activo">
                <x-ui.select.value placeholder="Todos" />
            </x-ui.select.trigger>
            <x-ui.select.content>
                <x-ui.select.item value="">Todos</x-ui.select.item>
                <x-ui.select.item value="1">Activo</x-ui.select.item>
                <x-ui.select.item value="0">Inactivo</x-ui.select.item>
            </x-ui.select.content>
        </x-ui.select>
    </x-ui.form-field>
</x-ui.filter-sheet>
