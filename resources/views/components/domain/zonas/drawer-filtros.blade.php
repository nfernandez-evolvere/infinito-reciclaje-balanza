@props(['filters' => []])

<x-ui.filter-sheet
    controlled-by="filterOpen"
    action="{{ route('admin.zonas.index') }}"
    reset-url="{{ route('admin.zonas.index') }}"
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
