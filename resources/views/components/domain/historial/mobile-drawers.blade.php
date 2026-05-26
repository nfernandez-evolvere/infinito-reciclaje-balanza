@props(['kpis', 'filtros', 'operarios', 'hayFiltros', 'routeHistorial'])

<div class="grid grid-cols-2 gap-2 sm:hidden">

    {{-- Drawer resumen --}}
    <x-ui.sheet side="bottom">
        <x-slot:trigger>
            <x-ui.button variant="outline" size="sm" class="w-full">
                <x-lucide-chart-bar class="size-3.5" />
                Métricas
            </x-ui.button>
        </x-slot:trigger>
        <div class="p-6 pt-10 space-y-4">
            <p class="text-label text-base">Resumen del turno</p>
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-card rounded-xl p-3 flex flex-col gap-2">
                    <x-lucide-scale class="size-8 text-primary p-1.5 bg-primary/10 rounded-lg" />
                    <div>
                        <p class="text-overline">Pesajes</p>
                        <p class="text-2xl font-bold leading-tight">{{ $kpis['total'] }}</p>
                    </div>
                </div>
                <div class="bg-card rounded-xl p-3 flex flex-col gap-2">
                    <x-lucide-weight class="size-8 text-primary p-1.5 bg-primary/10 rounded-lg" />
                    <div>
                        <p class="text-overline">Toneladas netas</p>
                        <p class="text-2xl font-bold leading-tight">
                            {{ number_format($kpis['toneladas_netas'], 1, ',', '.') }}
                            <span class="text-sm font-normal text-muted-foreground">t</span>
                        </p>
                    </div>
                </div>
                <div class="bg-card rounded-xl p-3 flex flex-col gap-2">
                    <x-lucide-chart-bar class="size-8 text-primary p-1.5 bg-primary/10 rounded-lg" />
                    <div>
                        <p class="text-overline">Promedio neto</p>
                        <p class="text-2xl font-bold leading-tight">
                            {{ number_format($kpis['promedio_kg'], 0, ',', '.') }}
                            <span class="text-sm font-normal text-muted-foreground">kg</span>
                        </p>
                    </div>
                </div>
                <div class="bg-card rounded-xl p-3 flex flex-col gap-2">
                    <x-lucide-truck class="size-8 text-primary p-1.5 bg-primary/10 rounded-lg" />
                    <div>
                        <p class="text-overline">En predio</p>
                        <p class="text-2xl font-bold leading-tight">{{ $kpis['en_predio'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </x-ui.sheet>

    {{-- Drawer filtros --}}
    <x-ui.sheet side="bottom">
        <x-slot:trigger>
            <x-ui.button variant="{{ $hayFiltros ? 'default' : 'outline' }}" size="sm" class="w-full">
                <x-lucide-sliders-horizontal class="size-3.5" />
                Filtros
                @if($hayFiltros)
                    <x-ui.badge variant="secondary" class="ml-0.5">•</x-ui.badge>
                @endif
            </x-ui.button>
        </x-slot:trigger>
        <div class="p-6 pt-10 space-y-5 overflow-y-auto">
            <p class="text-label text-base">Filtros</p>
            <form method="GET" action="{{ $routeHistorial }}" class="space-y-3">
                <div class="space-y-1.5">
                    <x-ui.label>Desde</x-ui.label>
                    <x-ui.date-picker name="desde" value="{{ $filtros['desde'] }}" placeholder="Desde" />
                </div>
                <div class="space-y-1.5">
                    <x-ui.label>Hasta</x-ui.label>
                    <x-ui.date-picker name="hasta" value="{{ $filtros['hasta'] }}" placeholder="Hasta" />
                </div>
                <div class="space-y-1.5">
                    <x-ui.label>Patente</x-ui.label>
                    <x-ui.input type="text" name="patente" value="{{ $filtros['patente'] ?? '' }}" placeholder="ABC 123" />
                </div>
                <div class="space-y-1.5">
                    <x-ui.label>Estado</x-ui.label>
                    <x-ui.select name="estado" value="{{ $filtros['estado'] ?? '' }}">
                        <x-ui.select.trigger>
                            <x-ui.select.value placeholder="Todos" />
                        </x-ui.select.trigger>
                        <x-ui.select.content>
                            <x-ui.select.item value="">Todos</x-ui.select.item>
                            <x-ui.select.item value="En predio">En predio</x-ui.select.item>
                            <x-ui.select.item value="Cerrado">Cerrado</x-ui.select.item>
                        </x-ui.select.content>
                    </x-ui.select>
                </div>
                <div class="space-y-1.5">
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
                </div>
                <div class="flex gap-2 pt-1">
                    <x-ui.button type="submit" class="flex-1">
                        <x-lucide-search class="size-4" />
                        Filtrar
                    </x-ui.button>
                    @if($hayFiltros)
                        <x-ui.button variant="secondary" href="{{ $routeHistorial }}" class="flex-1">
                            <x-lucide-x class="size-4" />
                            Limpiar
                        </x-ui.button>
                    @endif
                </div>
            </form>
        </div>
    </x-ui.sheet>

</div>
