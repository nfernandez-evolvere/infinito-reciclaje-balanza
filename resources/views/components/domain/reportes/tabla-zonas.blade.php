@props(['zonas'])

@php
    // Agrupamos las zonas por su servicio (Zona pertenece a un TipoServicio). Cada
    // grupo trae su subtotal — la regla del informe es que el total de un servicio es
    // la suma de sus zonas. Grupos y filas ordenados por volumen (toneladas) desc.
    $grupos = $zonas
        ->groupBy('tipo_servicio_id')
        ->map(fn ($filas) => [
            'nombre'     => $filas->first()['tipo_servicio'],
            'filas'      => $filas->sortByDesc('toneladas')->values(),
            'viajes'     => $filas->sum('viajes'),
            'toneladas'  => round($filas->sum('toneladas'), 2),
            'porcentaje' => round($filas->sum('porcentaje'), 1),
        ])
        ->sortByDesc('toneladas')
        ->values();
@endphp

<x-ui.card variant="elevated">
    <x-ui.card.header>
        <x-ui.card.title>Por zona y turno</x-ui.card.title>
        <x-ui.card.description>Desglose de viajes y toneladas por zona y turno, agrupado por servicio. Una zona puede ocupar varias filas, una por turno.</x-ui.card.description>
    </x-ui.card.header>
    <x-ui.card.content class="pt-0">
        @if($zonas->isEmpty())
            <div class="flex items-center justify-center py-10">
                <p class="text-caption">Sin datos para el período seleccionado.</p>
            </div>
        @else

            {{-- Mobile: secciones por servicio con filas compactas --}}
            <div class="sm:hidden space-y-4">
                @foreach($grupos as $g)
                    <div class="space-y-1.5">
                        <div class="flex items-baseline justify-between gap-2 px-1">
                            <span class="text-overline">{{ $g['nombre'] }}</span>
                            <span class="text-xs text-muted-foreground tabular-nums shrink-0">
                                {{ number_format($g['viajes']) }} viajes · {{ number_format($g['toneladas'], 1) }} t · {{ $g['porcentaje'] }}%
                            </span>
                        </div>
                        @foreach($g['filas'] as $zona)
                            <div class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg border border-border bg-background">
                                <div class="flex flex-col gap-0.5 min-w-0">
                                    <span class="font-medium text-sm truncate">
                                        {{ $zona['nombre'] }}
                                        @if($zona['turno'])
                                            <span class="text-muted-foreground font-normal"> · {{ $zona['turno'] }}</span>
                                        @endif
                                    </span>
                                    <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                        <span><span class="tabular-nums">{{ number_format($zona['viajes']) }}</span> viajes</span>
                                        <span>·</span>
                                        <span><span class="tabular-nums">{{ number_format($zona['toneladas'], 1) }}</span> t</span>
                                        <span>·</span>
                                        <span><span class="tabular-nums">{{ number_format($zona['kg_viaje']) }}</span> kg/v</span>
                                    </div>
                                </div>
                                <span class="text-sm font-semibold tabular-nums shrink-0 text-muted-foreground">
                                    {{ $zona['porcentaje'] }}%
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

            {{-- Desktop: tabla agrupada por servicio --}}
            <x-ui.table variant="flat" class="hidden sm:block">
                <x-ui.table.header>
                    <x-ui.table.row>
                        <x-ui.table.head>Zona</x-ui.table.head>
                        <x-ui.table.head>Turno</x-ui.table.head>
                        <x-ui.table.head>Viajes</x-ui.table.head>
                        <x-ui.table.head>Toneladas</x-ui.table.head>
                        <x-ui.table.head>kg/viaje</x-ui.table.head>
                        <x-ui.table.head>kg/ha</x-ui.table.head>
                        <x-ui.table.head>% Total</x-ui.table.head>
                    </x-ui.table.row>
                </x-ui.table.header>
                <x-ui.table.body>
                    @foreach($grupos as $g)
                        {{-- Cabecera del servicio con su subtotal --}}
                        <x-ui.table.row class="bg-muted/40">
                            <x-ui.table.cell colspan="2" class="font-semibold text-foreground">{{ $g['nombre'] }}</x-ui.table.cell>
                            <x-ui.table.cell class="font-semibold tabular-nums">{{ number_format($g['viajes']) }}</x-ui.table.cell>
                            <x-ui.table.cell class="font-semibold tabular-nums">{{ number_format($g['toneladas'], 1) }} t</x-ui.table.cell>
                            <x-ui.table.cell colspan="2"></x-ui.table.cell>
                            <x-ui.table.cell class="font-semibold tabular-nums text-muted-foreground">{{ $g['porcentaje'] }}%</x-ui.table.cell>
                        </x-ui.table.row>

                        @foreach($g['filas'] as $zona)
                        <x-ui.table.row>
                            <x-ui.table.cell data-label="Zona" class="font-medium pl-6">{{ $zona['nombre'] }}</x-ui.table.cell>
                            <x-ui.table.cell data-label="Turno">
                                @if($zona['turno'])
                                    <x-ui.badge variant="secondary">{{ $zona['turno'] }}</x-ui.badge>
                                @else
                                    <span class="text-muted-foreground">—</span>
                                @endif
                            </x-ui.table.cell>
                            <x-ui.table.cell data-label="Viajes" class="tabular-nums">
                                {{ number_format($zona['viajes']) }}
                            </x-ui.table.cell>
                            <x-ui.table.cell data-label="Toneladas" class="tabular-nums">
                                {{ number_format($zona['toneladas'], 1) }} t
                            </x-ui.table.cell>
                            <x-ui.table.cell data-label="kg/viaje" class="tabular-nums text-muted-foreground">
                                {{ number_format($zona['kg_viaje']) }} kg
                            </x-ui.table.cell>
                            <x-ui.table.cell data-label="kg/ha" class="tabular-nums">
                                @if($zona['kg_ha'] !== null)
                                    <div class="flex flex-col gap-0.5">
                                        <span>{{ number_format($zona['kg_ha'], 1) }} kg</span>
                                        <span class="text-xs text-muted-foreground">{{ number_format($zona['kg_ha'] / 1000, 2) }} t/ha</span>
                                    </div>
                                @else
                                    <span class="text-muted-foreground">—</span>
                                @endif
                            </x-ui.table.cell>
                            <x-ui.table.cell data-label="% Total" class="tabular-nums text-muted-foreground">
                                {{ $zona['porcentaje'] }}%
                            </x-ui.table.cell>
                        </x-ui.table.row>
                        @endforeach
                    @endforeach
                </x-ui.table.body>
                <x-ui.table.footer>
                    <x-ui.table.row>
                        <x-ui.table.cell class="font-semibold" colspan="2">Total</x-ui.table.cell>
                        <x-ui.table.cell class="font-semibold tabular-nums">{{ number_format($zonas->sum('viajes')) }}</x-ui.table.cell>
                        <x-ui.table.cell class="font-semibold tabular-nums">{{ number_format($zonas->sum('toneladas'), 1) }} t</x-ui.table.cell>
                        <x-ui.table.cell colspan="3"></x-ui.table.cell>
                    </x-ui.table.row>
                </x-ui.table.footer>
            </x-ui.table>

        @endif
    </x-ui.card.content>
</x-ui.card>
