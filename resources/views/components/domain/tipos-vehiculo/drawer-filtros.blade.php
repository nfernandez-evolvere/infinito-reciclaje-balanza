@props(['filters'])

<x-ui.filter-sheet
    controlled-by="filterOpen"
    action="{{ route('admin.vehiculos.index') }}"
    reset-url="{{ route('admin.vehiculos.index') }}"
>
    <x-ui.form-field for="filter-nombre">
        <x-ui.label for="filter-nombre">Tipo</x-ui.label>
        <x-ui.input
            id="filter-nombre"
            name="nombre"
            type="search"
            placeholder="Buscar por tipo…"
            :value="$filters['nombre'] ?? ''"
            autofocus
        />
    </x-ui.form-field>

    <div class="space-y-2">
        <x-ui.form-field for="filter-peso-min">
            <x-ui.label for="filter-peso-min">Bruto mínimo desde (kg)</x-ui.label>
            <x-ui.input
                id="filter-peso-min"
                name="peso_min"
                type="number"
                min="0"
                placeholder="0"
                :value="$filters['peso_min'] ?? ''"
            />
        </x-ui.form-field>

        <x-ui.form-field for="filter-peso-max">
            <x-ui.label for="filter-peso-max">Bruto máximo hasta (kg)</x-ui.label>
            <x-ui.input
                id="filter-peso-max"
                name="peso_max"
                type="number"
                min="0"
                placeholder="0"
                :value="$filters['peso_max'] ?? ''"
            />
        </x-ui.form-field>
    </div>

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
