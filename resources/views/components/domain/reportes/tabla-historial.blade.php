@props(['historial'])

@php
    $tipoLabels = ['informe_mensual' => 'Informe', 'alertas' => 'Alertas'];
    $origenLabels = ['manual' => 'Manual', 'programado' => 'Programado'];
    $estadoVariants = ['generado' => 'secondary', 'enviado' => 'success', 'fallido' => 'destructive'];
    $estadoLabels = ['generado' => 'Descargado', 'enviado' => 'Enviado', 'fallido' => 'Falló el envío'];

    $periodo = fn ($g) => $g->periodo_desde->format('d/m/Y').' – '.$g->periodo_hasta->format('d/m/Y');
    $formato = fn ($g) => collect(explode('+', $g->formato))->map(fn ($f) => strtoupper($f))->implode(' · ');
    $autor = fn ($g) => $g->usuario?->name ?? ($g->origen === 'programado' ? 'Automático' : '—');
@endphp

@if($historial->isEmpty())
    <x-ui.empty-state
        icon="history"
        title="Sin reportes en el historial"
        description="Cuando descargues un reporte o se envíe uno programado, vas a verlo listado acá."
        class="py-16"
    />
@else

    {{-- ── Mobile: cards ── --}}
    <div class="sm:hidden space-y-2">
        @foreach($historial as $g)
        <x-ui.card variant="elevated" class="p-3">
            <x-ui.card.header class="items-start gap-2">
                <div class="flex flex-col gap-1 min-w-0">
                    <span class="font-semibold text-sm truncate">{{ $tipoLabels[$g->tipo] ?? $g->tipo }}</span>
                    <div class="flex flex-wrap items-center gap-1.5">
                        @if($g->estado === 'fallido' && $g->error)
                            <x-ui.badge state="destructive">{{ $estadoLabels[$g->estado] }}</x-ui.badge>
                        @else
                            <x-ui.badge variant="{{ $estadoVariants[$g->estado] ?? 'secondary' }}">{{ $estadoLabels[$g->estado] ?? $g->estado }}</x-ui.badge>
                        @endif
                        <x-ui.badge variant="outline" class="text-xs">{{ $origenLabels[$g->origen] ?? $g->origen }}</x-ui.badge>
                    </div>
                </div>
                <x-slot:actions>
                    <x-ui.tooltip content="Volver a abrir">
                        <x-ui.button href="{{ route('admin.reportes.historial.download', $g) }}" variant="ghost" size="icon" class="size-7 -mr-1">
                            <x-lucide-download class="size-4" />
                        </x-ui.button>
                    </x-ui.tooltip>
                </x-slot:actions>
            </x-ui.card.header>

            <x-ui.card.content class="flex flex-col gap-1.5 text-xs text-muted-foreground">
                <div class="flex items-center gap-1.5">
                    <x-lucide-calendar-range class="size-3.5 shrink-0 text-primary" />
                    <span>{{ $periodo($g) }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <x-lucide-file-type class="size-3.5 shrink-0 text-primary" />
                    <span>{{ $formato($g) }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <x-lucide-clock class="size-3.5 shrink-0 text-primary" />
                    <span>{{ $g->created_at->format('d/m/Y H:i') }} · {{ $autor($g) }}</span>
                </div>
            </x-ui.card.content>
        </x-ui.card>
        @endforeach
    </div>

    {{-- ── Desktop: tabla ── --}}
    <x-ui.card class="hidden sm:block" variant="elevated">
        <x-ui.table variant="flat">
            <x-ui.table.header>
                <x-ui.table.row>
                    <x-ui.table.head>Fecha</x-ui.table.head>
                    <x-ui.table.head>Tipo</x-ui.table.head>
                    <x-ui.table.head>Período</x-ui.table.head>
                    <x-ui.table.head>Formato</x-ui.table.head>
                    <x-ui.table.head>Origen</x-ui.table.head>
                    <x-ui.table.head>Estado</x-ui.table.head>
                    <x-ui.table.head>Acciones</x-ui.table.head>
                </x-ui.table.row>
            </x-ui.table.header>
            <x-ui.table.body>
                @foreach($historial as $g)
                <x-ui.table.row>
                    <x-ui.table.cell>
                        <span class="text-sm text-nowrap">{{ $g->created_at->format('d/m/Y') }}</span>
                        <span class="text-caption block">{{ $g->created_at->format('H:i') }}</span>
                    </x-ui.table.cell>
                    <x-ui.table.cell>
                        <x-ui.badge class="text-nowrap" variant="{{ $g->tipo === 'informe_mensual' ? 'default' : 'warning' }}">
                            {{ $tipoLabels[$g->tipo] ?? $g->tipo }}
                        </x-ui.badge>
                    </x-ui.table.cell>
                    <x-ui.table.cell>
                        <span class="text-sm text-nowrap">{{ $periodo($g) }}</span>
                    </x-ui.table.cell>
                    <x-ui.table.cell>
                        <span class="text-caption font-medium text-nowrap">{{ $formato($g) }}</span>
                    </x-ui.table.cell>
                    <x-ui.table.cell>
                        <x-ui.badge variant="outline" class="text-nowrap">{{ $origenLabels[$g->origen] ?? $g->origen }}</x-ui.badge>
                        <span class="text-caption block mt-0.5">{{ $autor($g) }}</span>
                    </x-ui.table.cell>
                    <x-ui.table.cell>
                        @if($g->estado === 'fallido' && $g->error)
                            <x-ui.badge state="destructive">{{ $estadoLabels[$g->estado] }}</x-ui.badge>
                        @else
                            <x-ui.badge variant="{{ $estadoVariants[$g->estado] ?? 'secondary' }}">{{ $estadoLabels[$g->estado] ?? $g->estado }}</x-ui.badge>
                        @endif
                        @if($g->estado === 'enviado' && !empty($g->destinatarios))
                            <span class="text-caption block mt-0.5">{{ count($g->destinatarios) }} destinatario{{ count($g->destinatarios) === 1 ? '' : 's' }}</span>
                        @endif
                    </x-ui.table.cell>
                    <x-ui.table.cell class="text-right">
                        <x-ui.tooltip content="Volver a abrir" side="left">
                            <x-ui.button href="{{ route('admin.reportes.historial.download', $g) }}" variant="ghost" size="icon" class="size-8">
                                <x-lucide-download class="size-4" />
                            </x-ui.button>
                        </x-ui.tooltip>
                    </x-ui.table.cell>
                </x-ui.table.row>
                @endforeach
            </x-ui.table.body>
        </x-ui.table>
    </x-ui.card>

    {{-- Paginación --}}
    @if($historial->hasPages())
        <x-ui.pagination>
            <x-ui.pagination.content>
                @if($historial->onFirstPage())
                    <x-ui.pagination.item><x-ui.pagination.link :disabled="true">« Anterior</x-ui.pagination.link></x-ui.pagination.item>
                @else
                    <x-ui.pagination.item><x-ui.pagination.link href="{{ $historial->previousPageUrl() }}">« Anterior</x-ui.pagination.link></x-ui.pagination.item>
                @endif
                @foreach($historial->getUrlRange(1, $historial->lastPage()) as $page => $url)
                    <x-ui.pagination.item>
                        <x-ui.pagination.link href="{{ $url }}" :active="$page === $historial->currentPage()">{{ $page }}</x-ui.pagination.link>
                    </x-ui.pagination.item>
                @endforeach
                @if($historial->hasMorePages())
                    <x-ui.pagination.item><x-ui.pagination.link href="{{ $historial->nextPageUrl() }}">Siguiente »</x-ui.pagination.link></x-ui.pagination.item>
                @else
                    <x-ui.pagination.item><x-ui.pagination.link :disabled="true">Siguiente »</x-ui.pagination.link></x-ui.pagination.item>
                @endif
            </x-ui.pagination.content>
        </x-ui.pagination>
    @endif

@endif
