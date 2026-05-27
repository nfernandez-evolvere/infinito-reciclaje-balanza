@props(['pesajes', 'hayFiltros', 'routeHistorial', 'sortDirection' => 'desc'])

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
            icon="scale"
            title="Sin pesajes en este turno"
            description="Los pesajes aparecerán acá una vez que se registre el primero."
        />
    @endif
@else
    <x-ui.table class="bg-card">
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
            <x-ui.table.row class="{{ $pesaje->alerta_peso ? 'bg-warning/5' : '' }}">
                {{-- Ingreso --}}
                <x-ui.table.cell data-label="Ingreso">
                    <div class="flex items-center justify-center gap-1.5 text-sm">
                        <x-lucide-log-in class="size-3.5 shrink-0 text-success" />
                        <span>{{ $pesaje->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </x-ui.table.cell>
                {{-- Patente / N.° interno --}}
                <x-ui.table.cell data-label="Patente / N.° interno">
                    <div class="flex justify-center gap-2">
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
                {{-- Origen --}}
                <x-ui.table.cell class="text-sm justify-center" data-label="Origen">
                    <span>{{ $pesaje->zona->nombre }}</span>
                    @if($pesaje->turno)
                        <span class="text-muted-foreground"> — {{ $pesaje->turno }}</span>
                    @endif
                </x-ui.table.cell>
                {{-- Servicio --}}
                <x-ui.table.cell class="text-sm justify-center" data-label="Servicio">{{ $pesaje->tipoServicio->nombre }}</x-ui.table.cell>
                {{-- Peso neto --}}
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
                {{-- Estado --}}
                <x-ui.table.cell data-label="Estado">
                    <div class="flex items-center justify-center gap-1">
                        @if($pesaje->editado)
                            <x-ui.tooltip content="Editado">
                                <x-ui.badge variant="default" class="size-6 p-0 justify-center">
                                    <x-lucide-pen-line class="size-3" />
                                </x-ui.badge>
                            </x-ui.tooltip>
                        @endif
                        @if($pesaje->alerta_peso)
                            <x-ui.tooltip content="Alerta de peso">
                                <x-ui.badge variant="warning" class="size-6 p-0 justify-center">
                                    <x-lucide-triangle-alert class="size-3" />
                                </x-ui.badge>
                            </x-ui.tooltip>
                        @endif
                    </div>
                </x-ui.table.cell>
                <x-ui.table.cell class="order-first sm:order-0 justify-end border-b border-border sm:border-b-0">
                    <x-ui.dropdown-menu>
                        <x-ui.dropdown-menu.trigger>
                            <x-ui.button variant="ghost" size="icon" class="size-8">
                                <x-lucide-ellipsis class="size-4" />
                            </x-ui.button>
                        </x-ui.dropdown-menu.trigger>
                        <x-ui.dropdown-menu.content>
                            <x-ui.dropdown-menu.item href="{{ route('pesajes.show', $pesaje) }}">
                                <x-lucide-eye class="size-4" />
                                Detalles
                            </x-ui.dropdown-menu.item>
                            <x-ui.dropdown-menu.item href="{{ route('pesajes.edit', $pesaje) }}">
                                <x-lucide-pencil class="size-4" />
                                Editar
                            </x-ui.dropdown-menu.item>
                            @if($pesaje->editado)
                                <x-ui.dropdown-menu.item
                                    @click="abrirLog('{{ $pesaje->uuid }}', '{{ addslashes($pesaje->vehiculo->patente) }}')"
                                >
                                    <x-lucide-history class="size-4" />
                                    Ver cambios
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
