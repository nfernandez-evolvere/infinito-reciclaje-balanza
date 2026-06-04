@props(['alertas', 'filtros' => []])

@php $hayFiltros = array_filter($filtros); $countFiltros = count(array_filter($filtros)); @endphp

<div class="flex flex-col gap-4" x-data="{ filterOpen: false }">

    {{-- Barra superior: botón filtros + acción --}}
    <div class="flex items-center justify-end gap-3">

        {{-- Marcar todas --}}
        @if($alertas->total() > 0)
            <form method="POST" action="{{ route('admin.alertas.leer-todas') }}">
                @csrf
                <x-ui.button type="submit" variant="ghost">
                    <x-lucide-check-check class="size-4" />
                    <span class="hidden sm:inline">Marcar todas como leídas</span>
                    <span class="sm:hidden">Marcar leídas</span>
                </x-ui.button>
            </form>
        @endif

        {{-- Botón abrir filtros --}}
        <div class="relative">
            <x-ui.tooltip content="Filtros" class="sm:hidden">
                <x-ui.button variant="ghost" @click="filterOpen = true">
                    <x-lucide-sliders-horizontal class="size-4" />
                </x-ui.button>
            </x-ui.tooltip>
            <x-ui.button class="hidden sm:flex gap-1.5" @click="filterOpen = true">
                <x-lucide-sliders-horizontal class="size-4" />
                Filtros
            </x-ui.button>
            @if($hayFiltros)
                <span class="pointer-events-none absolute -top-1.5 -right-1.5 flex size-4 items-center justify-center rounded-full bg-primary text-primary-foreground ring-2 ring-background text-[10px] font-semibold leading-none">
                    {{ $countFiltros }}
                </span>
            @endif
        </div>
    </div>

    {{-- Drawer de filtros --}}
    <x-ui.sheet side="right" controlledBy="filterOpen">
        <div class="flex items-center border-b border-border px-5 py-4 pr-12">
            <x-ui.typography as="h4" class="flex items-center gap-2">
                <x-lucide-sliders-horizontal class="size-5" />
                Filtros
            </x-ui.typography>
        </div>

        <form method="GET" action="{{ route('admin.alertas.index') }}" class="flex flex-col flex-1">
            <input type="hidden" name="tab" value="alertas">

            <div class="px-5 py-5 space-y-0 flex-1">
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

                <x-ui.form-field>
                    <x-ui.label>Desde</x-ui.label>
                    <x-ui.date-picker name="desde" value="{{ $filtros['desde'] ?? '' }}" placeholder="Desde" />
                </x-ui.form-field>

                <x-ui.form-field>
                    <x-ui.label>Hasta</x-ui.label>
                    <x-ui.date-picker name="hasta" value="{{ $filtros['hasta'] ?? '' }}" placeholder="Hasta" />
                </x-ui.form-field>
            </div>

            <div class="border-t border-border px-5 py-4 flex gap-2">
                <a href="{{ route('admin.alertas.index', ['tab' => 'alertas']) }}" class="flex-1">
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
    </x-ui.sheet>

    {{-- Empty state --}}
    @if($alertas->isEmpty())
        <x-ui.empty-state
            icon="bell-off"
            title="Sin alertas"
            description="No hay alertas que coincidan con los filtros seleccionados."
            class="py-16"
        />
    @else

        {{-- ── Mobile: cards ── --}}
        <div class="sm:hidden space-y-2">
            @foreach($alertas as $alerta)
                <x-ui.card variant="elevated" class="p-3 {{ $alerta->leida ? 'opacity-60' : '' }}">
                    <x-ui.card.header class="items-start gap-2">
                        <div class="flex flex-col gap-1 min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <x-ui.badge variant="{{ $alerta->tipoVariant() }}" class="text-xs">
                                    @unless($alerta->leida)
                                        <span class="size-1.5 shrink-0 rounded-full bg-warning"></span>
                                    @endunless
                                    {{ $alerta->tipoLabel() }}
                                </x-ui.badge>
                            </div>
                            <span class="text-sm font-medium truncate">{{ $alerta->titulo }}</span>
                            @if($alerta->descripcion)
                                <span class="text-xs text-muted-foreground line-clamp-2">{{ $alerta->descripcion }}</span>
                            @endif
                        </div>
                        <x-slot:actions>
                            @unless($alerta->leida)
                                <form method="POST" action="{{ route('admin.alertas.leer', $alerta) }}">
                                    @csrf @method('PATCH')
                                    <x-ui.button type="submit" variant="ghost" size="icon" class="size-7 -mr-1">
                                        <x-lucide-check class="size-4" />
                                    </x-ui.button>
                                </form>
                            @endunless
                        </x-slot:actions>
                    </x-ui.card.header>
                    <x-ui.card.content class="flex flex-col gap-1 text-xs text-muted-foreground">
                        <div class="flex items-center gap-1.5">
                            <x-lucide-calendar class="size-3.5 shrink-0 text-primary" />
                            <span>{{ $alerta->fecha_deteccion->format('d/m/Y') }}</span>
                        </div>
                        @if($alerta->leida)
                            <div class="flex items-center gap-1.5">
                                <x-lucide-check class="size-3.5 shrink-0 text-muted-foreground" />
                                <span>Leída el {{ $alerta->leida_at?->format('d/m H:i') }}</span>
                            </div>
                        @endif
                        @if($alerta->pesaje)
                            <a href="{{ route('admin.pesajes.index', ['search' => $alerta->pesaje->vehiculo?->patente]) }}"
                               class="text-primary hover:underline">
                                Ver pesaje →
                            </a>
                        @endif
                    </x-ui.card.content>
                </x-ui.card>
            @endforeach
        </div>

        {{-- ── Desktop: tabla ── --}}
        <x-ui.card class="hidden sm:block" variant="elevated">
            <x-ui.table variant="flat">
                <x-ui.table.header>
                    <x-ui.table.row>
                        <x-ui.table.head>Tipo</x-ui.table.head>
                        <x-ui.table.head>Descripción</x-ui.table.head>
                        <x-ui.table.head>Fecha detección</x-ui.table.head>
                        <x-ui.table.head>Estado</x-ui.table.head>
                        <x-ui.table.head>Acciones</x-ui.table.head>
                    </x-ui.table.row>
                </x-ui.table.header>
                <x-ui.table.body>
                    @foreach($alertas as $alerta)
                        <x-ui.table.row class="{{ $alerta->leida ? 'opacity-60' : '' }}">

                            <x-ui.table.cell>
                                <div class="flex items-center gap-2">
                                    
                                    <x-ui.badge variant="{{ $alerta->tipoVariant() }}">
                                        @unless($alerta->leida)
                                            <span class="size-1.5 shrink-0 rounded-full bg-warning"></span>
                                        @endunless
                                        {{ $alerta->tipoLabel() }}
                                    </x-ui.badge>
                                </div>
                            </x-ui.table.cell>

                            <x-ui.table.cell class="max-w-sm">
                                <p class="text-sm font-medium">{{ $alerta->titulo }}</p>
                                @if($alerta->descripcion)
                                    <p class="text-caption mt-0.5">{{ $alerta->descripcion }}</p>
                                @endif
                                @if($alerta->pesaje)
                                    <a href="{{ route('admin.pesajes.index', ['search' => $alerta->pesaje->vehiculo?->patente]) }}"
                                       class="text-xs text-primary hover:underline mt-0.5 inline-block">
                                        Ver pesaje →
                                    </a>
                                @endif
                            </x-ui.table.cell>

                            <x-ui.table.cell>
                                <span class="text-sm text-nowrap">{{ $alerta->fecha_deteccion->format('d/m/Y') }}</span>
                                <span class="text-caption block">{{ $alerta->created_at->format('H:i') }}</span>
                            </x-ui.table.cell>

                            <x-ui.table.cell>
                                @if($alerta->leida)
                                    <x-ui.badge variant="secondary">Leída</x-ui.badge>
                                @else
                                    <x-ui.badge variant="warning">Sin leer</x-ui.badge>
                                @endif
                            </x-ui.table.cell>

                            <x-ui.table.cell>
                                @unless($alerta->leida)
                                    <form method="POST" action="{{ route('admin.alertas.leer', $alerta) }}">
                                        @csrf @method('PATCH')
                                        <x-ui.tooltip content="Marcar como leída" side="left">
                                            <x-ui.button type="submit" variant="ghost" size="icon" class="size-8">
                                                <x-lucide-check class="size-4" />
                                            </x-ui.button>
                                        </x-ui.tooltip>
                                    </form>
                                @else
                                    <span class="text-caption text-nowrap">
                                        {{ $alerta->leida_at?->format('d/m H:i') }}
                                    </span>
                                @endunless
                            </x-ui.table.cell>

                        </x-ui.table.row>
                    @endforeach
                </x-ui.table.body>
            </x-ui.table>
        </x-ui.card>

        {{-- Paginación --}}
        @if($alertas->hasPages())
            <x-ui.pagination>
                <x-ui.pagination.content>
                    @if($alertas->onFirstPage())
                        <x-ui.pagination.item><x-ui.pagination.link :disabled="true">« Anterior</x-ui.pagination.link></x-ui.pagination.item>
                    @else
                        <x-ui.pagination.item><x-ui.pagination.link href="{{ $alertas->previousPageUrl() }}">« Anterior</x-ui.pagination.link></x-ui.pagination.item>
                    @endif
                    @foreach($alertas->getUrlRange(1, $alertas->lastPage()) as $page => $url)
                        <x-ui.pagination.item>
                            <x-ui.pagination.link href="{{ $url }}" :active="$page === $alertas->currentPage()">{{ $page }}</x-ui.pagination.link>
                        </x-ui.pagination.item>
                    @endforeach
                    @if($alertas->hasMorePages())
                        <x-ui.pagination.item><x-ui.pagination.link href="{{ $alertas->nextPageUrl() }}">Siguiente »</x-ui.pagination.link></x-ui.pagination.item>
                    @else
                        <x-ui.pagination.item><x-ui.pagination.link :disabled="true">Siguiente »</x-ui.pagination.link></x-ui.pagination.item>
                    @endif
                </x-ui.pagination.content>
            </x-ui.pagination>
        @endif

    @endif

</div>
