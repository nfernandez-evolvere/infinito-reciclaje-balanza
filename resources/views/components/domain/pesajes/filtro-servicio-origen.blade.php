@props([
    'zonas',
    'tiposServicio',
    'servicioId'   => '',
    'zonaId'       => '',
    'prefix'       => '',
])

{{--
    Par de selects vinculados «Servicio» → «Origen» para los filtros de pesajes.
    Las zonas dependen del servicio: al elegir un servicio solo se muestran sus
    orígenes y, si la zona elegida deja de pertenecer al servicio, se limpia.

    `prefix` permite reutilizarlo en el tab «Modificaciones» (names con `m_`).
    El wrapper usa `display:contents` para no romper la grilla del filter-panel:
    cada <x-ui.form-field> queda como celda directa de la grilla del contenedor.
--}}

@php
    $zonasData = $zonas->map(fn ($z) => ['id' => $z->id, 'tipo_servicio_id' => $z->tipo_servicio_id])->values();
@endphp

<div
    class="contents"
    x-data="filtroServicioOrigen({
        servicio: '{{ $servicioId }}',
        zona: '{{ $zonaId }}',
        zonas: {{ $zonasData->toJson() }},
    })"
>
    <x-ui.form-field>
        <x-ui.label>Servicio</x-ui.label>
        <x-ui.select name="{{ $prefix }}tipo_servicio_id" x-model="servicio">
            <x-ui.select.trigger>
                <x-ui.select.value placeholder="Todos" />
            </x-ui.select.trigger>
            <x-ui.select.content>
                <x-ui.select.item value="">Todos</x-ui.select.item>
                @foreach($tiposServicio as $ts)
                    <x-ui.select.item value="{{ $ts->id }}">{{ $ts->nombre }}</x-ui.select.item>
                @endforeach
            </x-ui.select.content>
        </x-ui.select>
    </x-ui.form-field>

    <x-ui.form-field>
        <x-ui.label>Zona</x-ui.label>
        <x-ui.select name="{{ $prefix }}zona_id" x-model="zona">
            <x-ui.select.trigger>
                <x-ui.select.value placeholder="Todos" />
            </x-ui.select.trigger>
            <x-ui.select.content>
                <x-ui.select.item value="">Todos</x-ui.select.item>
                @foreach($zonas as $zona)
                    <x-ui.select.item
                        value="{{ $zona->id }}"
                        x-show="!servicio || servicio === '{{ $zona->tipo_servicio_id }}'"
                    >{{ $zona->nombre }}</x-ui.select.item>
                @endforeach
            </x-ui.select.content>
        </x-ui.select>
    </x-ui.form-field>
</div>
