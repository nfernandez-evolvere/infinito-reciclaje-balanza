@props(['pesajes', 'hayFiltros'])

@if($pesajes->isEmpty())
    @if($hayFiltros)
        <x-ui.empty-state
            icon="search-x"
            title="Sin resultados"
            description="Ningún pesaje coincide con los filtros aplicados."
        >
            <x-ui.button size="sm" href="{{ route('historial') }}">
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
                <x-ui.table.head>Entrada</x-ui.table.head>
                <x-ui.table.head>Salida</x-ui.table.head>
                <x-ui.table.head>Estado</x-ui.table.head>
                <x-ui.table.head>Patente</x-ui.table.head>
                <x-ui.table.head>Servicio</x-ui.table.head>
                <x-ui.table.head>Origen</x-ui.table.head>
                <x-ui.table.head>Operario</x-ui.table.head>
                <x-ui.table.head>Bruto</x-ui.table.head>
                <x-ui.table.head>Tara</x-ui.table.head>
                <x-ui.table.head>Neto</x-ui.table.head>
                <x-ui.table.head>Acciones</x-ui.table.head>
            </x-ui.table.row>
        </x-ui.table.header>
        <x-ui.table.body>
            @foreach($pesajes as $pesaje)
            <x-ui.table.row class="{{ $pesaje->alerta_peso ? 'bg-warning/5' : '' }}">
                <x-ui.table.cell data-label="Entrada">
                    {{ $pesaje->created_at->format('d/m/Y H:i') }}
                </x-ui.table.cell>
                <x-ui.table.cell class="text-muted-foreground" data-label="Salida">
                    {{ $pesaje->hora_salida?->format('d/m/Y H:i') ?? '—' }}
                </x-ui.table.cell>
                <x-ui.table.cell data-label="Estado">
                    <div class="flex items-center gap-1.5">
                        @if($pesaje->estaEnPredio())
                            <x-ui.badge variant="default" class="text-xs">En predio</x-ui.badge>
                        @else
                            <x-ui.badge variant="secondary" class="text-xs">Cerrado</x-ui.badge>
                        @endif
                        @if($pesaje->editado)
                            <x-ui.badge variant="outline" class="text-xs">Editado</x-ui.badge>
                        @endif
                        @if($pesaje->alerta_peso)
                            <x-ui.badge variant="warning" class="text-xs">Alerta</x-ui.badge>
                        @endif
                    </div>
                </x-ui.table.cell>
                <x-ui.table.cell class="font-medium" data-label="Patente">{{ $pesaje->vehiculo->patente }}</x-ui.table.cell>
                <x-ui.table.cell class="text-sm" data-label="Servicio">{{ $pesaje->tipoServicio->nombre }}</x-ui.table.cell>
                <x-ui.table.cell class="text-sm text-muted-foreground" data-label="Origen">{{ $pesaje->zona->nombre }}</x-ui.table.cell>
                <x-ui.table.cell class="text-sm text-muted-foreground" data-label="Operario">{{ $pesaje->operador->name }}</x-ui.table.cell>
                <x-ui.table.cell data-label="Bruto">
                    {{ number_format($pesaje->peso_bruto_kg, 0, ',', '.') }} kg
                </x-ui.table.cell>
                <x-ui.table.cell class="text-muted-foreground" data-label="Tara">
                    {{ number_format($pesaje->peso_tara_kg, 0, ',', '.') }} kg
                </x-ui.table.cell>
                <x-ui.table.cell class="font-semibold" data-label="Neto">
                    {{ number_format($pesaje->peso_neto_kg, 0, ',', '.') }} kg
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
                            @if($pesaje->estaEnPredio())
                                <x-ui.dropdown-menu.separator />
                                <x-ui.dropdown-menu.item
                                    @click="abrirEgreso('{{ $pesaje->uuid }}', '{{ addslashes($pesaje->vehiculo->patente) }}')"
                                >
                                    <x-lucide-log-out class="size-4" />
                                    Marcar egreso
                                </x-ui.dropdown-menu.item>
                            @endif
                        </x-ui.dropdown-menu.content>
                    </x-ui.dropdown-menu>
                </x-ui.table.cell>
            </x-ui.table.row>
            @endforeach
        </x-ui.table.body>
    </x-ui.table>
@endif
