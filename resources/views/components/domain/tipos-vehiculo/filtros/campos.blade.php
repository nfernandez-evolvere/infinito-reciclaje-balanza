@props(['filters', 'tab' => null])

{{--
    Campos del filtro de tipos de vehículo. Se reutilizan en el sheet mobile y en el
    panel inline (md+). Cada campo es un elemento raíz. Sin id/for fijos: el markup se
    renderiza dos veces (sheet + panel). El hidden `tab` preserva la pestaña activa.
--}}

@if($tab)
    <input type="hidden" name="tab" value="{{ $tab }}">
@endif

<x-ui.form-field>
    <x-ui.label>Tipo</x-ui.label>
    <x-ui.input
        name="nombre"
        type="search"
        placeholder="Buscar por tipo…"
        :value="$filters['nombre'] ?? ''"
    />
</x-ui.form-field>

<x-ui.form-field>
    <x-ui.label>Bruto mínimo desde (kg)</x-ui.label>
    <x-ui.input
        name="peso_min"
        type="number"
        min="0"
        placeholder="0"
        :value="$filters['peso_min'] ?? ''"
    />
</x-ui.form-field>

<x-ui.form-field>
    <x-ui.label>Bruto máximo hasta (kg)</x-ui.label>
    <x-ui.input
        name="peso_max"
        type="number"
        min="0"
        placeholder="0"
        :value="$filters['peso_max'] ?? ''"
    />
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
