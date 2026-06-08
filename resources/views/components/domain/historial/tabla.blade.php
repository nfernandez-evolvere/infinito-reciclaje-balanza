@props([
    'pesajes',
    'hayFiltros',
    'routeHistorial',
    'sortDirection' => 'desc',
    'emptyIcon' => 'scale',
    'emptyTitle' => 'Sin pesajes en este turno',
    'emptyDescription' => 'Los pesajes aparecerán acá una vez que se registre el primero.',
])

@if($pesajes->isEmpty())
    @if($hayFiltros)
        <x-ui.empty-state
            icon="search-x"
            title="Sin resultados"
            description="Ningún pesaje coincide con los filtros aplicados."
        >
            <x-ui.button size="sm" href="{{ $routeHistorial }}">
                <x-lucide-x class="size-3.5" />
                Limpiar filtros
            </x-ui.button>
        </x-ui.empty-state>
    @else
        <x-ui.empty-state
            :icon="$emptyIcon"
            :title="$emptyTitle"
            :description="$emptyDescription"
        />
    @endif
@else

    {{-- ── Mobile: cards ────────────────────────────────────────── --}}
    <div class="lg:hidden space-y-2">
        @foreach($pesajes as $pesaje)
            @php
                $cardClass = $pesaje->estaCancelado()
                    ? 'opacity-60'
                    : ($pesaje->alerta_peso ? 'bg-warning/5 border-warning/40' : '');
            @endphp
            <x-ui.card class="p-3 {{ $cardClass }}" variant="elevated">
                <x-ui.card.header class="items-center gap-2">
                    <div class="flex items-center gap-1.5 min-w-0">
                        <x-lucide-car class="size-3.5 shrink-0 text-muted-foreground" />
                        <span class="font-semibold text-sm">{{ $pesaje->vehiculo->patente }}</span>
                        @if($pesaje->vehiculo->numero_interno)
                            <span class="text-xs text-muted-foreground">#{{ $pesaje->vehiculo->numero_interno }}</span>
                        @endif
                    </div>
                    <x-slot:actions>

                        <x-ui.dropdown-menu>
                            <x-ui.dropdown-menu.trigger>
                                <x-ui.button variant="ghost" size="icon" class="size-7 -mr-1">
                                    <x-lucide-ellipsis class="size-4" />
                                </x-ui.button>
                            </x-ui.dropdown-menu.trigger>
                            <x-ui.dropdown-menu.content align="end">
                                <x-ui.dropdown-menu.item href="{{ route('pesajes.show', $pesaje) }}">
                                    <x-lucide-eye class="size-4" />
                                    Detalles
                                </x-ui.dropdown-menu.item>
                                @if(!$pesaje->estaCancelado())
                                    <x-ui.dropdown-menu.item href="{{ route('pesajes.edit', ['pesaje' => $pesaje, 'origen' => request()->route()?->getName()]) }}">
                                        <x-lucide-pencil class="size-4" />
                                        Editar
                                    </x-ui.dropdown-menu.item>
                                @endif
                                @if($pesaje->estaEnPredio())
                                    <x-ui.dropdown-menu.item
                                        @click="abrirEgreso('{{ $pesaje->uuid }}', '{{ addslashes($pesaje->vehiculo->patente) }}')"
                                    >
                                        <x-lucide-log-out class="size-4" />
                                        Marcar egreso
                                    </x-ui.dropdown-menu.item>
                                @endif
                                @if($pesaje->editado || $pesaje->estaCancelado())
                                    <x-ui.dropdown-menu.item
                                        @click="abrirLog('{{ $pesaje->uuid }}', '{{ addslashes($pesaje->vehiculo->patente) }}')"
                                    >
                                        <x-lucide-history class="size-4" />
                                        Ver cambios
                                    </x-ui.dropdown-menu.item>
                                @endif
                                @if(!$pesaje->estaCancelado())
                                    <x-ui.dropdown-menu.separator />
                                    <x-ui.dropdown-menu.item
                                        variant="destructive"
                                        @click="abrirCancelar('{{ $pesaje->uuid }}', '{{ addslashes($pesaje->vehiculo->patente) }}')"
                                    >
                                        <x-lucide-ban class="size-4" />
                                        Cancelar pesaje
                                    </x-ui.dropdown-menu.item>
                                @endif
                            </x-ui.dropdown-menu.content>
                        </x-ui.dropdown-menu>
                    </x-slot:actions>
                </x-ui.card.header>

                <x-ui.card.content class="flex items-end justify-between gap-3">
                    <div class="flex flex-col gap-1 text-xs text-muted-foreground min-w-0">
                        <div class="flex flex-wrap items-center gap-1">
                            @if($pesaje->estaCancelado())
                                <x-ui.badge state="destructive" class="gap-1">
                                    <x-lucide-ban class="size-3" />
                                    Cancelado
                                </x-ui.badge>
                            @else
                                @if($pesaje->editado)
                                    <x-ui.badge state="warning" class="gap-1">
                                        <x-lucide-pen-line class="size-3" />
                                        Editado
                                    </x-ui.badge>
                                @endif
                                @if($pesaje->alerta_peso)
                                    <x-ui.badge state="warning" class="gap-1">
                                        <x-lucide-triangle-alert class="size-3" />
                                        Alerta
                                    </x-ui.badge>
                                @endif
                            @endif
                        </div>
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-1">
                                <x-lucide-map-pin class="size-3.5 shrink-0 text-primary" />
                                <span class="truncate">{{ $pesaje->zona->nombre }}@if($pesaje->turno) — {{ $pesaje->turno }}@endif</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <x-lucide-briefcase class="size-3.5 shrink-0 text-primary" />
                                <span class="truncate">{{ $pesaje->tipoServicio->nombre }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            <x-lucide-log-in class="size-3 shrink-0 text-primary" />
                            <span>{{ $pesaje->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                    <x-ui.popover side="top" align="end" width="w-56">
                        <x-slot:trigger>
                            <div class="flex items-center gap-1">
                                <span class="font-semibold tabular-nums text-sm">
                                    {{ number_format($pesaje->peso_neto_kg, 0, ',', '.') }} kg
                                </span>
                                <x-lucide-info class="size-3.5 text-muted-foreground" />
                            </div>
                        </x-slot:trigger>
                        <p class="text-xs font-medium text-muted-foreground mb-2">Cálculo de peso</p>
                        <div class="flex flex-col gap-1 text-sm tabular-nums">
                            <div class="flex items-center justify-between gap-6">
                                <div class="flex items-center gap-1.5 text-muted-foreground">
                                    <x-lucide-package class="size-3.5 shrink-0" />
                                    <span>Bruto</span>
                                </div>
                                <span>{{ number_format($pesaje->peso_bruto_kg, 0, ',', '.') }} kg</span>
                            </div>
                            <div class="flex items-center justify-between gap-6">
                                <div class="flex items-center gap-1.5 text-muted-foreground">
                                    <x-lucide-minus class="size-3.5 shrink-0" />
                                    <span>Tara</span>
                                </div>
                                <span>{{ number_format($pesaje->peso_tara_kg, 0, ',', '.') }} kg</span>
                            </div>
                            <div class="flex items-center justify-between gap-6 font-semibold border-t border-border pt-1 mt-0.5">
                                <div class="flex items-center gap-1.5">
                                    <x-lucide-equal class="size-3.5 shrink-0" />
                                    <span>Neto</span>
                                </div>
                                <span>{{ number_format($pesaje->peso_neto_kg, 0, ',', '.') }} kg</span>
                            </div>
                        </div>
                    </x-ui.popover>
                </x-ui.card.content>
            </x-ui.card>
        @endforeach
    </div>

    {{-- ── Desktop: tabla ────────────────────────────────────────── --}}
    <x-ui.table class="hidden lg:block bg-card">
        <x-ui.table.header>
            <x-ui.table.row>
                <x-ui.table.head>
                    @php
                        $nextDirection = $sortDirection === 'desc' ? 'asc' : 'desc';
                        $sortUrl = request()->fullUrlWithQuery(['direction' => $nextDirection, 'page' => null]);
                    @endphp
                    <a href="{{ $sortUrl }}" class="inline-flex items-center justify-center gap-1 hover:text-foreground transition-colors">
                        Ingreso
                        @if($sortDirection === 'desc')
                            <x-lucide-arrow-down class="size-3.5" />
                        @else
                            <x-lucide-arrow-up class="size-3.5" />
                        @endif
                    </a>
                </x-ui.table.head>
                <x-ui.table.head>Patente / N.° interno</x-ui.table.head>
                <x-ui.table.head>Origen</x-ui.table.head>
                <x-ui.table.head>Servicio</x-ui.table.head>
                <x-ui.table.head>Peso neto</x-ui.table.head>
                <x-ui.table.head>Estado</x-ui.table.head>
                <x-ui.table.head>Acciones</x-ui.table.head>
            </x-ui.table.row>
        </x-ui.table.header>
        <x-ui.table.body>
            @foreach($pesajes as $pesaje)
            <x-ui.table.row class="{{ $pesaje->estaCancelado() ? 'opacity-60' : ($pesaje->alerta_peso ? 'bg-warning/5' : '') }}">
                <x-ui.table.cell data-label="Ingreso">
                    <div class="flex items-center justify-center gap-1.5 text-sm">
                        <x-lucide-log-in class="size-3.5 shrink-0 text-success" />
                        <span>{{ $pesaje->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </x-ui.table.cell>
                <x-ui.table.cell data-label="Patente / N.° interno">
                    <div class="flex items-center justify-center gap-2">
                        <div class="flex items-center gap-1">
                            <x-ui.tooltip content="Patente">
                                <x-lucide-car class="size-3.5 shrink-0 text-muted-foreground" />
                            </x-ui.tooltip>
                            <span class="font-medium">{{ $pesaje->vehiculo->patente }}</span>
                        </div>
                        @if($pesaje->vehiculo->numero_interno)
                            <div class="flex items-center gap-1">
                                <x-ui.tooltip content="N.° interno">
                                    <x-lucide-hash class="size-3.5 shrink-0 text-muted-foreground" />
                                </x-ui.tooltip>
                                <span class="font-medium">{{ $pesaje->vehiculo->numero_interno }}</span>
                            </div>
                        @endif
                    </div>
                </x-ui.table.cell>
                <x-ui.table.cell class="text-sm" data-label="Origen">
                    <span>{{ $pesaje->zona->nombre }}</span>
                    @if($pesaje->turno)
                        <span class="text-muted-foreground"> — {{ $pesaje->turno }}</span>
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell class="text-sm" data-label="Servicio">{{ $pesaje->tipoServicio->nombre }}</x-ui.table.cell>
                <x-ui.table.cell data-label="Peso neto">
                    <div class="flex items-center justify-center gap-1">
                        <span class="font-semibold tabular-nums text-sm">
                            {{ number_format($pesaje->peso_neto_kg, 0, ',', '.') }} kg
                        </span>
                        <x-ui.popover align="end">
                            <x-slot:trigger>
                                <x-lucide-info class="size-3.5 text-muted-foreground hover:text-foreground transition-colors" />
                            </x-slot:trigger>
                            <p class="text-xs font-medium text-muted-foreground mb-2">Cálculo de peso</p>
                            <div class="flex flex-col gap-1 text-sm tabular-nums">
                                <div class="flex items-center justify-between gap-6">
                                    <div class="flex items-center gap-1.5 text-muted-foreground">
                                        <x-lucide-package class="size-3.5 shrink-0" />
                                        <span>Bruto</span>
                                    </div>
                                    <span>{{ number_format($pesaje->peso_bruto_kg, 0, ',', '.') }} kg</span>
                                </div>
                                <div class="flex items-center justify-between gap-6">
                                    <div class="flex items-center gap-1.5 text-muted-foreground">
                                        <x-lucide-minus class="size-3.5 shrink-0" />
                                        <span>Tara</span>
                                    </div>
                                    <span>{{ number_format($pesaje->peso_tara_kg, 0, ',', '.') }} kg</span>
                                </div>
                                <div class="flex items-center justify-between gap-6 font-semibold border-t border-border pt-1 mt-0.5">
                                    <div class="flex items-center gap-1.5">
                                        <x-lucide-equal class="size-3.5 shrink-0" />
                                        <span>Neto</span>
                                    </div>
                                    <span>{{ number_format($pesaje->peso_neto_kg, 0, ',', '.') }} kg</span>
                                </div>
                            </div>
                        </x-ui.popover>
                    </div>
                </x-ui.table.cell>
                <x-ui.table.cell data-label="Estado">
                    <div class="flex items-center justify-center gap-1">
                        @if($pesaje->estaCancelado())
                            <x-ui.badge state="destructive" class="gap-1">
                                <x-lucide-ban class="size-3" />
                                Cancelado
                            </x-ui.badge>
                        @else
                            @if($pesaje->editado)
                                <x-ui.badge state="warning" class="gap-1">
                                    <x-lucide-pen-line class="size-3" />
                                    Editado
                                </x-ui.badge>
                            @endif
                            @if($pesaje->alerta_peso)
                                <x-ui.tooltip content="Alerta de peso">
                                    <x-ui.badge state="warning" class="gap-1">
                                        <x-lucide-triangle-alert class="size-3" />
                                        Alerta
                                    </x-ui.badge>
                                </x-ui.tooltip>
                            @endif
                        @endif
                    </div>
                </x-ui.table.cell>
                <x-ui.table.cell :actions="true" class="border-b border-border sm:border-b-0">
                    <x-ui.dropdown-menu>
                        <x-ui.dropdown-menu.trigger>
                            <x-ui.button variant="ghost" size="icon" class="size-8">
                                <x-lucide-ellipsis class="size-4" />
                            </x-ui.button>
                        </x-ui.dropdown-menu.trigger>
                        <x-ui.dropdown-menu.content align="start">
                            <x-ui.dropdown-menu.item href="{{ route('pesajes.show', $pesaje) }}">
                                <x-lucide-eye class="size-4" />
                                Detalles
                            </x-ui.dropdown-menu.item>
                            @if(!$pesaje->estaCancelado())
                                <x-ui.dropdown-menu.item href="{{ route('pesajes.edit', ['pesaje' => $pesaje, 'origen' => request()->route()?->getName()]) }}">
                                    <x-lucide-pencil class="size-4" />
                                    Editar
                                </x-ui.dropdown-menu.item>
                            @endif
                            @if($pesaje->estaEnPredio())
                                <x-ui.dropdown-menu.item
                                    @click="abrirEgreso('{{ $pesaje->uuid }}', '{{ addslashes($pesaje->vehiculo->patente) }}')"
                                >
                                    <x-lucide-log-out class="size-4" />
                                    Marcar egreso
                                </x-ui.dropdown-menu.item>
                            @endif
                            @if($pesaje->editado || $pesaje->estaCancelado())
                                <x-ui.dropdown-menu.item
                                    @click="abrirLog('{{ $pesaje->uuid }}', '{{ addslashes($pesaje->vehiculo->patente) }}')"
                                >
                                    <x-lucide-history class="size-4" />
                                    Ver cambios
                                </x-ui.dropdown-menu.item>
                            @endif
                            @if(!$pesaje->estaCancelado())
                                <x-ui.dropdown-menu.separator />
                                <x-ui.dropdown-menu.item
                                    variant="destructive"
                                    @click="abrirCancelar('{{ $pesaje->uuid }}', '{{ addslashes($pesaje->vehiculo->patente) }}')"
                                >
                                    <x-lucide-ban class="size-4" />
                                    Cancelar pesaje
                                </x-ui.dropdown-menu.item>
                            @endif
                        </x-ui.dropdown-menu.content>
                    </x-ui.dropdown-menu>
                </x-ui.table.cell>
            </x-ui.table.row>
            @endforeach
        </x-ui.table.body>
    </x-ui.table>

    @if($pesajes->hasPages())
        <div class="flex items-center justify-between px-1 pt-2 text-sm text-muted-foreground">
            <span class="flex-1">Página {{ $pesajes->currentPage() }} de {{ $pesajes->lastPage() }} · {{ number_format($pesajes->total(), 0, ',', '.') }} pesajes</span>
            <x-ui.pagination :paginator="$pesajes" />
        </div>
    @endif

@endif
