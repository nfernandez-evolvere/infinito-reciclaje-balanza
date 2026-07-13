@props(['filtros' => []])

{{--
    Campos del filtro de alertas. Se reutilizan en el sheet mobile y en el panel
    inline (md+). Cada campo es un elemento raíz. Sin id/for fijos: el markup se
    renderiza dos veces (sheet + panel). El hidden `tab` preserva la pestaña activa.
--}}

<input type="hidden" name="tab" value="alertas">

<x-ui.form-field>
    <x-ui.label>Tipo</x-ui.label>
    <x-ui.select name="tipo" :value="$filtros['tipo'] ?? ''">
        <x-ui.select.trigger>
            <x-ui.select.value placeholder="Todos los tipos" />
        </x-ui.select.trigger>
        <x-ui.select.content>
            <x-ui.select.item value="">Todos los tipos</x-ui.select.item>
            <x-ui.select.item value="peso_fuera_rango">Peso fuera de rango</x-ui.select.item>
            <x-ui.select.item value="volumen_diario_atipico">Volumen atípico</x-ui.select.item>
            <x-ui.select.item value="gap_registro">Sin actividad</x-ui.select.item>
            <x-ui.select.item value="frecuencia_zona_atipica">Frecuencia atípica</x-ui.select.item>
        </x-ui.select.content>
    </x-ui.select>
</x-ui.form-field>

<x-ui.form-field>
    <x-ui.label>Estado</x-ui.label>
    <x-ui.select name="leida" :value="$filtros['leida'] ?? ''">
        <x-ui.select.trigger>
            <x-ui.select.value placeholder="Todas" />
        </x-ui.select.trigger>
        <x-ui.select.content>
            <x-ui.select.item value="">Todas</x-ui.select.item>
            <x-ui.select.item value="0">Sin leer</x-ui.select.item>
            <x-ui.select.item value="1">Leídas</x-ui.select.item>
        </x-ui.select.content>
    </x-ui.select>
</x-ui.form-field>

<x-ui.form-field class="col-span-2">
    <x-ui.label>Período</x-ui.label>
    <x-ui.date-range-picker
        startName="desde"
        endName="hasta"
        :start="$filtros['desde'] ?? null"
        :end="$filtros['hasta'] ?? null"
        placeholder="Todas las fechas"
    />
</x-ui.form-field>
