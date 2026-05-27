@props(['filtros', 'operarios', 'hayFiltros', 'routeHistorial', 'zonas' => collect(), 'tiposServicio' => collect(), 'sortDirection' => 'desc'])

@php
    $merge = fn(array $overrides) => $routeHistorial . '?' . http_build_query(
        array_filter(array_merge($filtros, $overrides), fn($v) => $v !== null && $v !== '')
    );

    $chips = [];

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
            'url'   => $merge(['desde' => null, 'hasta' => null]),
        ];
    }

    if (!empty($filtros['patente'])) {
        $chips[] = ['label' => strtoupper($filtros['patente']), 'url' => $merge(['patente' => null])];
    }

    if (!empty($filtros['estado'])) {
        $chips[] = ['label' => $filtros['estado'], 'url' => $merge(['estado' => null])];
    }

    if (!empty($filtros['operario_id'])) {
        $op = $operarios->firstWhere('id', $filtros['operario_id']);
        $chips[] = ['label' => $op?->name ?? 'Operario', 'url' => $merge(['operario_id' => null])];
    }

    if (!empty($filtros['zona_id'])) {
        $zona = $zonas->firstWhere('id', $filtros['zona_id']);
        $chips[] = ['label' => $zona?->nombre ?? 'Origen', 'url' => $merge(['zona_id' => null])];
    }

    if (!empty($filtros['tipo_servicio_id'])) {
        $ts = $tiposServicio->firstWhere('id', $filtros['tipo_servicio_id']);
        $chips[] = ['label' => $ts?->nombre ?? 'Servicio', 'url' => $merge(['tipo_servicio_id' => null])];
    }

    if (!empty($filtros['solo_alerta'])) {
        $chips[] = ['label' => 'Con alerta', 'url' => $merge(['solo_alerta' => null])];
    }

    if (!empty($filtros['solo_editados'])) {
        $chips[] = ['label' => 'Solo editados', 'url' => $merge(['solo_editados' => null])];
    }
@endphp

<div class="flex items-center gap-2 flex-wrap">

    @if($chips)
        <div class="hidden sm:flex items-center gap-1.5 flex-wrap">
            @foreach($chips as $chip)
                <x-ui.chip href="{{ $chip['url'] }}">{{ $chip['label'] }}</x-ui.chip>
            @endforeach
        </div>
    @endif

    <x-ui.button
        variant="{{ $hayFiltros ? 'default' : 'outline' }}"
        size="sm"
        @click="filterOpen = true"
    >
        <x-lucide-sliders-horizontal class="size-3.5" />
        Filtros
    </x-ui.button>

</div>

<template x-teleport="body">
    <div
        x-show="filterOpen"
        @keydown.escape.window="filterOpen = false"
        class="fixed inset-0 z-(--z-modal)"
        x-cloak
    >
        {{-- Backdrop --}}
        <div
            x-show="filterOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="filterOpen = false"
            class="absolute inset-0 bg-black/50"
        ></div>

        {{-- Panel --}}
        <div
            x-show="filterOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-4"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-4"
            class="absolute inset-y-0 right-0 flex w-80 flex-col rounded-l-xl border-l border-border bg-background shadow-xl"
        >
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-border px-5 py-4">
                <x-ui.typography as="h4" class="flex items-center gap-2">
                    <x-lucide-sliders-horizontal class="size-5" />
                    Filtros
                </x-ui.typography>
                <x-ui.button type="button" variant="ghost" size="icon" @click="filterOpen = false" class="size-7 -mr-1">
                    <x-lucide-x class="size-4" />
                </x-ui.button>
            </div>

            {{-- Form --}}
            <form method="GET" action="{{ $routeHistorial }}" class="flex flex-col flex-1 min-h-0">

                <div class="flex-1 overflow-y-auto px-5 py-5">
                    <x-ui.form-field>
                        <x-ui.label>Desde</x-ui.label>
                        <x-ui.date-picker name="desde" value="{{ $filtros['desde'] }}" placeholder="Desde" />
                    </x-ui.form-field>

                    <x-ui.form-field>
                        <x-ui.label>Hasta</x-ui.label>
                        <x-ui.date-picker name="hasta" value="{{ $filtros['hasta'] }}" placeholder="Hasta" />
                    </x-ui.form-field>

                    <x-ui.form-field>
                        <x-ui.label>Patente</x-ui.label>
                        <x-ui.input type="text" name="patente" value="{{ $filtros['patente'] ?? '' }}" placeholder="ABC 123" />
                    </x-ui.form-field>

                    <x-ui.form-field>
                        <x-ui.label>Estado</x-ui.label>
                        <x-ui.select name="estado" value="{{ $filtros['estado'] ?? '' }}">
                            <x-ui.select.trigger>
                                <x-ui.select.value placeholder="Todos" />
                            </x-ui.select.trigger>
                            <x-ui.select.content>
                                <x-ui.select.item value="">Todos</x-ui.select.item>
                                <x-ui.select.item value="Activos">Activos</x-ui.select.item>
                                <x-ui.select.item value="Cancelado">Cancelados</x-ui.select.item>
                            </x-ui.select.content>
                        </x-ui.select>
                    </x-ui.form-field>

                    <x-ui.form-field>
                        <x-ui.label>Operario</x-ui.label>
                        <x-ui.select name="operario_id" value="{{ $filtros['operario_id'] ?? '' }}">
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
                            <x-ui.select name="zona_id" value="{{ $filtros['zona_id'] ?? '' }}">
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
                            <x-ui.select name="tipo_servicio_id" value="{{ $filtros['tipo_servicio_id'] ?? '' }}">
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

                    @if($zonas->isNotEmpty())
                        <div class="space-y-2 pt-1 pb-3 border-b border-border mb-4">
                            <label class="flex items-center gap-2 text-sm cursor-pointer">
                                <x-ui.checkbox name="solo_alerta" value="1" :checked="!empty($filtros['solo_alerta'])" />
                                Solo con alerta
                            </label>
                            <label class="flex items-center gap-2 text-sm cursor-pointer">
                                <x-ui.checkbox name="solo_editados" value="1" :checked="!empty($filtros['solo_editados'])" />
                                Solo editados
                            </label>
                        </div>
                    @endif

                    <x-ui.form-field>
                        <x-ui.label>Orden de fecha</x-ui.label>
                        <x-ui.select name="direction" value="{{ $sortDirection }}">
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

                </div>

                {{-- Footer --}}
                <div class="border-t border-border px-5 py-4 flex gap-2">
                    <a href="{{ $routeHistorial }}" class="flex-1">
                        <x-ui.button type="button" variant="secondary" class="w-full">
                            <x-lucide-x class="size-4" />
                            Limpiar
                        </x-ui.button>
                    </a>
                    <x-ui.button type="submit" class="flex-1">
                        <x-lucide-search class="size-4" />
                        Aplicar
                    </x-ui.button>
                </div>

            </form>
        </div>
    </div>
</template>
