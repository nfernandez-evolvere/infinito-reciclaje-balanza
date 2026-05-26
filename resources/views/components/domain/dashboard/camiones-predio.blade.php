@props(['camiones'])

@if($camiones->isNotEmpty())
<x-ui.card variant="elevated">
    <x-ui.card.header>
        <div class="flex items-center justify-between gap-4">
            <div>
                <x-ui.card.title>Camiones en el predio</x-ui.card.title>
                <x-ui.card.description>
                    {{ $camiones->count() }} {{ Str::plural('vehículo', $camiones->count()) }} sin egreso registrado
                </x-ui.card.description>
            </div>
            <x-ui.badge variant="default">{{ $camiones->count() }} en predio</x-ui.badge>
        </div>
    </x-ui.card.header>
    <x-ui.card.content class="pt-0">

        {{-- Mobile: tarjetas compactas --}}
        <div class="sm:hidden space-y-2">
            @foreach($camiones as $pesaje)
                @php
                    $minutos  = $pesaje->created_at->diffInMinutes(now());
                    $tardanza = $minutos > 120;
                @endphp
                <div class="rounded-lg border px-3 py-2.5 space-y-1 {{ $tardanza ? 'border-warning/40 bg-warning/5' : 'border-border bg-background' }}">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                            @if($tardanza)
                                <x-lucide-triangle-alert class="size-3.5 shrink-0 text-warning" />
                            @endif
                            <span class="font-semibold text-sm">{{ $pesaje->vehiculo->patente }}</span>
                        </div>
                        <span class="text-xs shrink-0 {{ $tardanza ? 'text-warning font-medium' : 'text-muted-foreground' }}">
                            {{ $pesaje->created_at->diffForHumans(now(), true) }}
                        </span>
                    </div>
                    <p class="text-xs text-muted-foreground">
                        {{ $pesaje->tipoServicio->nombre }} · {{ $pesaje->zona->nombre }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        Entrada {{ $pesaje->created_at->format('H:i') }}
                        · {{ number_format($pesaje->peso_neto_kg, 0, ',', '.') }} kg
                        · {{ $pesaje->operador->name }}
                    </p>
                </div>
            @endforeach
        </div>

        {{-- Desktop: tabla completa --}}
        <div class="hidden sm:block">
            <x-ui.table variant="flat">
                <x-ui.table.header>
                    <x-ui.table.row>
                        <x-ui.table.head>Patente</x-ui.table.head>
                        <x-ui.table.head>Tipo</x-ui.table.head>
                        <x-ui.table.head>Servicio</x-ui.table.head>
                        <x-ui.table.head>Origen</x-ui.table.head>
                        <x-ui.table.head>Entrada</x-ui.table.head>
                        <x-ui.table.head>Tiempo</x-ui.table.head>
                        <x-ui.table.head>Operario</x-ui.table.head>
                        <x-ui.table.head>Neto (kg)</x-ui.table.head>
                    </x-ui.table.row>
                </x-ui.table.header>
                <x-ui.table.body>
                    @foreach($camiones as $pesaje)
                        @php
                            $minutos  = $pesaje->created_at->diffInMinutes(now());
                            $tardanza = $minutos > 120;
                        @endphp
                        <x-ui.table.row class="{{ $tardanza ? 'bg-warning/5' : '' }}">
                            <x-ui.table.cell class="font-medium">
                                <div class="flex items-center gap-1.5">
                                    {{ $pesaje->vehiculo->patente }}
                                    @if($tardanza)
                                        <x-ui.tooltip content="Lleva más de 2 horas en el predio">
                                            <x-lucide-triangle-alert class="size-3.5 text-warning" />
                                        </x-ui.tooltip>
                                    @endif
                                </div>
                            </x-ui.table.cell>
                            <x-ui.table.cell class="text-muted-foreground text-sm">
                                {{ $pesaje->vehiculo->tipoVehiculo?->nombre ?? '—' }}
                            </x-ui.table.cell>
                            <x-ui.table.cell class="text-sm">{{ $pesaje->tipoServicio->nombre }}</x-ui.table.cell>
                            <x-ui.table.cell class="text-muted-foreground text-sm">{{ $pesaje->zona->nombre }}</x-ui.table.cell>
                            <x-ui.table.cell>{{ $pesaje->created_at->format('d/m/Y H:i') }}</x-ui.table.cell>
                            <x-ui.table.cell>
                                <span class="{{ $tardanza ? 'text-warning font-medium' : 'text-muted-foreground' }}">
                                    {{ $pesaje->created_at->diffForHumans(now(), true) }}
                                </span>
                            </x-ui.table.cell>
                            <x-ui.table.cell class="text-muted-foreground text-sm">{{ $pesaje->operador->name }}</x-ui.table.cell>
                            <x-ui.table.cell class="tabular-nums">
                                {{ number_format($pesaje->peso_neto_kg, 0, ',', '.') }}
                            </x-ui.table.cell>
                        </x-ui.table.row>
                    @endforeach
                </x-ui.table.body>
            </x-ui.table>
        </div>

    </x-ui.card.content>
</x-ui.card>
@endif
