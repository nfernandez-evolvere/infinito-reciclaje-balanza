@props(['filters'])

<x-ui.filter-sheet
    controlled-by="filterOpen"
    action="{{ route('admin.usuarios.index') }}"
    reset-url="{{ route('admin.usuarios.index') }}"
>
    <x-ui.form-field for="filter-buscar">
        <x-ui.label for="filter-buscar">Nombre o correo</x-ui.label>
        <x-ui.input
            id="filter-buscar"
            name="buscar"
            type="search"
            placeholder="Buscar usuario…"
            :value="$filters['buscar'] ?? ''"
            autofocus
        />
    </x-ui.form-field>

    <x-ui.form-field for="filter-role">
        <x-ui.label for="filter-role">Rol</x-ui.label>
        <x-ui.select name="role" :value="$filters['role'] ?? ''">
            <x-ui.select.trigger id="filter-role">
                <x-ui.select.value placeholder="Todos" />
            </x-ui.select.trigger>
            <x-ui.select.content>
                <x-ui.select.item value="">Todos</x-ui.select.item>
                <x-ui.select.item value="operador">Operador</x-ui.select.item>
                <x-ui.select.item value="admin">Admin</x-ui.select.item>
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
