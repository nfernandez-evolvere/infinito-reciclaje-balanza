@props(['filters'])

{{--
    Campos del filtro de usuarios. Se reutilizan tal cual en el sheet mobile
    (<x-ui.filter-sheet>, stack vertical) y en el panel inline (<x-ui.filter-panel>,
    grilla). Cada campo es un elemento raíz para que herede el layout del contenedor.
    Sin id/for fijos: el markup se renderiza dos veces (sheet + panel).
--}}

<x-ui.form-field>
    <x-ui.label>Nombre o correo</x-ui.label>
    <x-ui.input
        name="buscar"
        type="search"
        placeholder="Buscar usuario…"
        :value="$filters['buscar'] ?? ''"
    />
</x-ui.form-field>

<x-ui.form-field>
    <x-ui.label>Rol</x-ui.label>
    <x-ui.select name="role" :value="$filters['role'] ?? ''">
        <x-ui.select.trigger>
            <x-ui.select.value placeholder="Todos" />
        </x-ui.select.trigger>
        <x-ui.select.content>
            <x-ui.select.item value="">Todos</x-ui.select.item>
            <x-ui.select.item value="operador">Operador</x-ui.select.item>
            <x-ui.select.item value="admin">Admin</x-ui.select.item>
        </x-ui.select.content>
    </x-ui.select>
</x-ui.form-field>

<x-ui.form-field>
    <x-ui.label>Estado</x-ui.label>
    <x-ui.select name="activo" :value="$filters['activo'] ?? ''">
        <x-ui.select.trigger>
            <x-ui.select.value placeholder="Todos" />
        </x-ui.select.trigger>
        <x-ui.select.content>
            <x-ui.select.item value="">Todos</x-ui.select.item>
            <x-ui.select.item value="1">Activo</x-ui.select.item>
            <x-ui.select.item value="0">Inactivo</x-ui.select.item>
        </x-ui.select.content>
    </x-ui.select>
</x-ui.form-field>
