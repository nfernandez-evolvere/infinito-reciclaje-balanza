@props(['alertas', 'filtros' => []])

@php $hayFiltros = array_filter($filtros); $countFiltros = count(array_filter($filtros)); @endphp

<div class="flex flex-col gap-4" x-data="{ filterOpen: false }">

    {{-- Barra superior: trigger de filtros (solo <md) --}}
    <div class="flex items-center justify-end gap-3">

        {{-- Trigger de filtros: solo <md (en md+ los filtros viven en el panel inline) --}}
        <div class="relative md:hidden">
            <x-ui.button variant="outline" @click="filterOpen = true">
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

    {{-- Filtros: sheet mobile + panel inline (md+) --}}
    <x-domain.alertas.filtros
        :filtros="$filtros"
        :hayFiltros="(bool) $hayFiltros"
        :activeFilters="$countFiltros"
    />

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
                <x-ui.card variant="elevated" class="p-3">
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
                            <a href="{{ route('pesajes.show', $alerta->pesaje) }}"
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
                        <x-ui.table.row>

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
                                    <a href="{{ route('pesajes.show', $alerta->pesaje) }}"
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
            <div class="flex items-center justify-between px-1 pt-2 text-sm text-muted-foreground">
                <span class="flex-1">Página {{ $alertas->currentPage() }} de {{ $alertas->lastPage() }} · {{ number_format($alertas->total(), 0, ',', '.') }} alertas</span>
                <x-ui.pagination :paginator="$alertas" />
            </div>
        @endif

    @endif

</div>
