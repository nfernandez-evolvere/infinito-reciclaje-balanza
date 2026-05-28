@props(['filters', 'tiposVehiculo'])

<x-ui.filter-sheet
    controlled-by="filterOpen"
    action="{{ route('admin.vehiculos.index') }}"
    reset-url="{{ route('admin.vehiculos.index') }}"
>
    <x-ui.form-field for="filter-patente">
        <x-ui.label for="filter-patente">Patente</x-ui.label>
        <x-ui.input
            id="filter-patente"
            name="patente"
            type="search"
            placeholder="Buscar por patente…"
            :value="$filters['patente'] ?? ''"
            autofocus
        />
    </x-ui.form-field>

    <x-ui.form-field for="filter-numero-interno">
        <x-ui.label for="filter-numero-interno">N.° interno</x-ui.label>
        <x-ui.input
            id="filter-numero-interno"
            name="numero_interno"
            type="search"
            placeholder="Buscar por número interno…"
            :value="$filters['numero_interno'] ?? ''"
        />
    </x-ui.form-field>

    <x-ui.form-field for="filter-tipo-vehiculo">
        <x-ui.label for="filter-tipo-vehiculo">Tipo de vehículo</x-ui.label>
        <x-ui.select name="tipo_vehiculo_id" :value="$filters['tipo_vehiculo_id'] ?? ''">
            <x-ui.select.trigger id="filter-tipo-vehiculo">
                <x-ui.select.value placeholder="Todos" />
            </x-ui.select.trigger>
            <x-ui.select.content>
                <x-ui.select.item value="">Todos</x-ui.select.item>
                @foreach($tiposVehiculo as $tipo)
                    <x-ui.select.item value="{{ $tipo->id }}">{{ $tipo->nombre }}</x-ui.select.item>
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
