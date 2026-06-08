@props(['filtros', 'operarios', 'hayFiltros', 'zonas' => collect(), 'tiposServicio' => collect(), 'sortDirection' => 'desc', 'control' => 'filterOpen'])

@php
    // Tab «Modificaciones» de la pantalla de Pesajes: usa parámetros con prefijo `m_`
    // para no colisionar con el tab «Pesajes». Las claves de $filtros siguen siendo
    // canónicas (las arma el controller); sólo los names de inputs y de la URL son `m_*`.
    $route = route('admin.pesajes.index', ['tab' => 'modificaciones']);

    // Quita un filtro preservando el resto del query string (incluido el estado del tab Pesajes).
    $removeUrl = fn (array $overrides) => request()->fullUrlWithQuery(array_merge($overrides, ['m_page' => null]));

    $chips = [];

    if (!empty($filtros['tipo'])) {
        $chips[] = [
            'label' => $filtros['tipo'] === 'editado' ? 'Editados' : 'Cancelados',
            'url'   => $removeUrl(['m_tipo' => null]),
        ];
    }

    if ($filtros['desde'] || $filtros['hasta']) {
        $label = match(true) {
            (bool) $filtros['desde'] && (bool) $filtros['hasta'] && $filtros['desde'] === $filtros['hasta']
                => \Carbon\Carbon::parse($filtros['desde'])->format('d/m/Y'),
            (bool) $filtros['desde'] && (bool) $filtros['hasta']
                => \Carbon\Carbon::parse($filtros['desde'])->format('d/m') . ' – ' . \Carbon\Carbon::parse($filtros['hasta'])->format('d/m'),
            (bool) $filtros['desde']
                => 'Desde ' . \Carbon\Carbon::parse($filtros['desde'])->format('d/m'),
            default
                => 'Hasta ' . \Carbon\Carbon::parse($filtros['hasta'])->format('d/m'),
        };
        $chips[] = [
            'label' => $label,
            'url'   => $removeUrl(['m_desde' => null, 'm_hasta' => null]),
        ];
    }

    if (!empty($filtros['patente'])) {
        $chips[] = ['label' => strtoupper($filtros['patente']), 'url' => $removeUrl(['m_patente' => null])];
    }

    if (!empty($filtros['operario_id'])) {
        $op = $operarios->firstWhere('id', $filtros['operario_id']);
        $chips[] = ['label' => $op?->name ?? 'Operario', 'url' => $removeUrl(['m_operario_id' => null])];
    }

    if (!empty($filtros['zona_id'])) {
        $zona = $zonas->firstWhere('id', $filtros['zona_id']);
        $chips[] = ['label' => $zona?->nombre ?? 'Origen', 'url' => $removeUrl(['m_zona_id' => null])];
    }

    if (!empty($filtros['tipo_servicio_id'])) {
        $ts = $tiposServicio->firstWhere('id', $filtros['tipo_servicio_id']);
        $chips[] = ['label' => $ts?->nombre ?? 'Servicio', 'url' => $removeUrl(['m_tipo_servicio_id' => null])];
    }
@endphp

<div class="relative">
    <x-ui.tooltip content="Filtros" class="sm:hidden">
        <x-ui.button
            variant="ghost"
            @click="{{ $control }} = true"
        >
            <x-lucide-sliders-horizontal class="size-4" />
        </x-ui.button>
    </x-ui.tooltip>
    <x-ui.button
        class="hidden sm:flex gap-1.5"
        @click="{{ $control }} = true"
    >
        <x-lucide-sliders-horizontal class="size-4" />
        Filtros
    </x-ui.button>
    @if($hayFiltros)
        <span class="pointer-events-none absolute -top-1.5 -right-1.5 flex size-4 items-center justify-center rounded-full bg-primary text-primary-foreground ring-2 ring-background text-[10px] font-semibold leading-none">
            {{ count($chips) }}
        </span>
    @endif
</div>

<x-ui.filter-sheet
    controlledBy="{{ $control }}"
    action="{{ $route }}"
    resetUrl="{{ $route }}"
>
    <input type="hidden" name="tab" value="modificaciones">

    <x-ui.form-field>
        <x-ui.label>Desde</x-ui.label>
        <x-ui.date-picker name="m_desde" value="{{ $filtros['desde'] }}" placeholder="Desde" />
    </x-ui.form-field>

    <x-ui.form-field>
        <x-ui.label>Hasta</x-ui.label>
        <x-ui.date-picker name="m_hasta" value="{{ $filtros['hasta'] }}" placeholder="Hasta" />
    </x-ui.form-field>

    <x-ui.form-field>
        <x-ui.label>Tipo</x-ui.label>
        <x-ui.select name="m_tipo" value="{{ $filtros['tipo'] ?? '' }}">
            <x-ui.select.trigger>
                <x-ui.select.value placeholder="Todos" />
            </x-ui.select.trigger>
            <x-ui.select.content>
                <x-ui.select.item value="">Todos</x-ui.select.item>
                <x-ui.select.item value="editado">Editados</x-ui.select.item>
                <x-ui.select.item value="cancelado">Cancelados</x-ui.select.item>
            </x-ui.select.content>
        </x-ui.select>
    </x-ui.form-field>

    <div x-data="historialFiltroPatente({ value: '{{ $filtros['patente'] ?? '' }}', url: '{{ route('vehiculos.activos') }}' })">
        <x-ui.form-field>
            <x-ui.label>Patente</x-ui.label>
            <div class="relative">
                <x-ui.input
                    type="text"
                    name="m_patente"
                    x-model="query"
                    @focus="cargar()"
                    @blur="setTimeout(() => showSugg = false, 150)"
                    placeholder="ABC 123"
                    autocomplete="off"
                />
                <div
                    x-show="showSugg && matches.length > 0"
                    x-cloak
                    class="absolute left-0 right-0 top-full mt-1 bg-popover border border-border rounded-lg shadow-md overflow-hidden z-30 max-h-56 overflow-y-auto"
                >
                    <template x-for="v in matches" :key="v.id">
                        <div
                            class="px-3 py-2 cursor-pointer text-sm hover:bg-accent transition-colors"
                            @mousedown.prevent="seleccionar(v.patente)"
                        >
                            <span class="font-medium" x-text="v.patente"></span>
                            <span class="text-muted-foreground text-xs" x-text="' · int. ' + v.interno"></span>
                        </div>
                    </template>
                </div>
            </div>
        </x-ui.form-field>
    </div>

    <x-ui.form-field>
        <x-ui.label>Operario</x-ui.label>
        <x-ui.select name="m_operario_id" value="{{ $filtros['operario_id'] ?? '' }}">
            <x-ui.select.trigger>
                <x-ui.select.value placeholder="Todos" />
            </x-ui.select.trigger>
            <x-ui.select.content>
                <x-ui.select.item value="">Todos</x-ui.select.item>
                @foreach($operarios as $op)
                    <x-ui.select.item value="{{ $op->id }}">{{ $op->name }}</x-ui.select.item>
                @endforeach
            </x-ui.select.content>
        </x-ui.select>
    </x-ui.form-field>

    @if($zonas->isNotEmpty())
        <x-ui.form-field>
            <x-ui.label>Origen</x-ui.label>
            <x-ui.select name="m_zona_id" value="{{ $filtros['zona_id'] ?? '' }}">
                <x-ui.select.trigger>
                    <x-ui.select.value placeholder="Todos" />
                </x-ui.select.trigger>
                <x-ui.select.content>
                    <x-ui.select.item value="">Todos</x-ui.select.item>
                    @foreach($zonas as $zona)
                        <x-ui.select.item value="{{ $zona->id }}">{{ $zona->nombre }}</x-ui.select.item>
                    @endforeach
                </x-ui.select.content>
            </x-ui.select>
        </x-ui.form-field>
    @endif

    @if($tiposServicio->isNotEmpty())
        <x-ui.form-field>
            <x-ui.label>Servicio</x-ui.label>
            <x-ui.select name="m_tipo_servicio_id" value="{{ $filtros['tipo_servicio_id'] ?? '' }}">
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
    @endif

    <x-ui.form-field>
        <x-ui.label>Orden de fecha</x-ui.label>
        <x-ui.select name="m_direction" value="{{ $sortDirection }}">
            <x-ui.select.trigger>
                <x-ui.select.value placeholder="Seleccionar" />
            </x-ui.select.trigger>
            <x-ui.select.content>
                <x-ui.select.item value="desc">
                    <div class="flex items-center gap-1.5">
                        <x-lucide-arrow-down class="size-3.5" />
                        Más reciente primero
                    </div>
                </x-ui.select.item>
                <x-ui.select.item value="asc">
                    <div class="flex items-center gap-1.5">
                        <x-lucide-arrow-up class="size-3.5" />
                        Más antiguo primero
                    </div>
                </x-ui.select.item>
            </x-ui.select.content>
        </x-ui.select>
    </x-ui.form-field>
</x-ui.filter-sheet>
