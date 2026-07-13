@props(['historial'])

@php
    $tipoLabels = ['informe_mensual' => 'Reporte', 'alertas' => 'Alertas'];
    $origenLabels = ['manual' => 'Manual', 'programado' => 'Programado'];
    $estadoVariants = [
        'generando'   => 'secondary',
        'en_revision' => 'warning',
        'enviando'    => 'secondary',
        'generado'    => 'secondary',
        'enviado'     => 'success',
        'fallido'     => 'destructive',
        'descartado'  => 'outline',
    ];
    $estadoLabels = [
        'generando'   => 'Generando…',
        'en_revision' => 'En revisión',
        'enviando'    => 'Enviando…',
        'generado'    => 'Descargado',
        'enviado'     => 'Enviado',
        'fallido'     => 'Fallido',
        'descartado'  => 'Descartado',
    ];

    $periodo = fn ($g) => $g->periodo_desde->format('d/m/Y').' – '.$g->periodo_hasta->format('d/m/Y');
    $formato = fn ($g) => collect(explode('+', $g->formato))->map(fn ($f) => strtoupper($f))->implode(' · ');
    $autor = fn ($g) => $g->usuario?->name ?? ($g->origen === 'programado' ? 'Automático' : '—');

    // En estados transitorios no hay nada que accionar; en fallido sin snapshot
    // no hay archivos que descargar (la generación nunca terminó).
    $enProceso = fn ($g) => in_array($g->estado, ['generando', 'enviando'], true);
    $conDescargas = fn ($g) => ! $enProceso($g)
        && ($g->estado !== 'fallido' || (bool) $g->tiene_snapshot);

    // Payload del dialog de revisión: URLs generadas server-side con route().
    $revisionPayload = function ($g) use ($tipoLabels, $periodo, $autor) {
        $formatos = explode('+', $g->formato);

        return [
            'id'            => $g->id,
            'tipo'          => $tipoLabels[$g->tipo] ?? $g->tipo,
            'periodo'       => $periodo($g),
            'generado'      => $g->created_at->format('d/m/Y H:i'),
            'autor'         => $autor($g),
            'destinatarios' => $g->destinatarios ?? [],
            'esInforme'     => $g->tipo === 'informe_mensual',
            'conclusiones'  => $g->conclusiones ?? '',
            'urls'          => [
                'pdf'          => in_array('pdf', $formatos, true) ? route('admin.reportes.historial.download', ['generado' => $g, 'formato' => 'pdf']) : null,
                'excel'        => in_array('excel', $formatos, true) ? route('admin.reportes.historial.download', ['generado' => $g, 'formato' => 'excel']) : null,
                'aprobar'      => route('admin.reportes.historial.aprobar', $g),
                'descartar'    => route('admin.reportes.historial.descartar', $g),
                'conclusiones' => route('admin.reportes.historial.conclusiones.update', $g),
            ],
        ];
    };
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
                        <x-ui.badge variant="{{ $estadoVariants[$g->estado] ?? 'secondary' }}" title="{{ $g->estado === 'fallido' ? $g->error : '' }}">
                            {{ $estadoLabels[$g->estado] ?? $g->estado }}
                        </x-ui.badge>
                        <x-ui.badge variant="outline" class="text-xs">{{ $origenLabels[$g->origen] ?? $g->origen }}</x-ui.badge>
                    </div>
                </div>
                <x-slot:actions>
                    @if(!$enProceso($g))
                    <x-ui.dropdown-menu>
                        <x-ui.dropdown-menu.trigger>
                            <x-ui.button variant="ghost" size="icon" class="size-7 -mr-1">
                                <x-lucide-ellipsis class="size-4" />
                            </x-ui.button>
                        </x-ui.dropdown-menu.trigger>
                        <x-ui.dropdown-menu.content align="end">
                            @if($g->estado === 'en_revision')
                                <x-ui.dropdown-menu.item @click="openRevision({{ Js::from($revisionPayload($g)) }})">
                                    <x-lucide-eye class="size-4" /> Revisar
                                </x-ui.dropdown-menu.item>
                            @endif
                            @if($g->estado === 'fallido')
                                <form id="reintentar-m-{{ $g->id }}" method="POST" action="{{ route('admin.reportes.historial.reintentar', $g) }}">
                                    @csrf
                                </form>
                                <x-ui.dropdown-menu.item @click="document.getElementById('reintentar-m-{{ $g->id }}').submit()">
                                    <x-lucide-refresh-cw class="size-4" /> Reintentar
                                </x-ui.dropdown-menu.item>
                            @endif
                            @if($conDescargas($g))
                                @foreach(explode('+', $g->formato) as $f)
                                    <x-ui.dropdown-menu.item href="{{ route('admin.reportes.historial.download', ['generado' => $g, 'formato' => $f]) }}">
                                        @if($f === 'excel')
                                            <x-lucide-file-spreadsheet class="size-4" /> Descargar Excel
                                        @else
                                            <x-lucide-file-text class="size-4" /> Descargar PDF
                                        @endif
                                    </x-ui.dropdown-menu.item>
                                @endforeach
                            @endif
                        </x-ui.dropdown-menu.content>
                    </x-ui.dropdown-menu>
                    @endif
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
                        <x-ui.badge class="text-nowrap" variant="{{ $estadoVariants[$g->estado] ?? 'secondary' }}" title="{{ $g->estado === 'fallido' ? $g->error : '' }}">
                            {{ $estadoLabels[$g->estado] ?? $g->estado }}
                        </x-ui.badge>
                        @if($g->estado === 'enviado')
                            <span class="text-caption block mt-0.5">
                                @if(!empty($g->destinatarios)){{ count($g->destinatarios) }} destinatario{{ count($g->destinatarios) === 1 ? '' : 's' }}@endif
                                @if($g->revisadoPor) · aprobado por {{ $g->revisadoPor->name }}@endif
                            </span>
                        @elseif($g->estado === 'descartado')
                            <span class="text-caption block mt-0.5" @if($g->motivo_descarte) title="{{ $g->motivo_descarte }}" @endif>
                                por {{ $g->revisadoPor?->name ?? '—' }}{{ $g->motivo_descarte ? ' · '.Str::limit($g->motivo_descarte, 40) : '' }}
                            </span>
                        @elseif($g->estado === 'en_revision')
                            <span class="text-caption block mt-0.5">esperando aprobación</span>
                        @elseif($g->estado === 'fallido' && $g->error)
                            <span class="text-caption block mt-0.5">{{ Str::limit($g->error, 50) }}</span>
                        @endif
                    </x-ui.table.cell>
                    <x-ui.table.cell class="text-right">
                        @if($g->estado === 'en_revision')
                            <x-ui.button variant="outline" size="sm" @click="openRevision({{ Js::from($revisionPayload($g)) }})">
                                <x-lucide-eye class="size-4" />
                                Revisar
                            </x-ui.button>
                        @elseif(!$enProceso($g))
                            <x-ui.dropdown-menu>
                                <x-ui.dropdown-menu.trigger>
                                    <x-ui.button variant="ghost" size="icon" class="size-8">
                                        <x-lucide-ellipsis class="size-4" />
                                    </x-ui.button>
                                </x-ui.dropdown-menu.trigger>
                                <x-ui.dropdown-menu.content align="end">
                                    @if($g->estado === 'fallido')
                                        <form id="reintentar-{{ $g->id }}" method="POST" action="{{ route('admin.reportes.historial.reintentar', $g) }}">
                                            @csrf
                                        </form>
                                        <x-ui.dropdown-menu.item @click="document.getElementById('reintentar-{{ $g->id }}').submit()">
                                            <x-lucide-refresh-cw class="size-4" /> Reintentar
                                        </x-ui.dropdown-menu.item>
                                    @endif
                                    @if($conDescargas($g))
                                        @foreach(explode('+', $g->formato) as $f)
                                            <x-ui.dropdown-menu.item href="{{ route('admin.reportes.historial.download', ['generado' => $g, 'formato' => $f]) }}">
                                                @if($f === 'excel')
                                                    <x-lucide-file-spreadsheet class="size-4" /> Descargar Excel
                                                @else
                                                    <x-lucide-file-text class="size-4" /> Descargar PDF
                                                @endif
                                            </x-ui.dropdown-menu.item>
                                        @endforeach
                                    @endif
                                </x-ui.dropdown-menu.content>
                            </x-ui.dropdown-menu>
                        @endif
                    </x-ui.table.cell>
                </x-ui.table.row>
                @endforeach
            </x-ui.table.body>
        </x-ui.table>
    </x-ui.card>

    {{-- Paginación --}}
    @if($historial->hasPages())
        <div class="flex items-center justify-between px-1 pt-2 text-sm text-muted-foreground">
            <span class="flex-1">Página {{ $historial->currentPage() }} de {{ $historial->lastPage() }} · {{ number_format($historial->total(), 0, ',', '.') }} reportes</span>
            <x-ui.pagination :paginator="$historial" />
        </div>
    @endif

@endif
