@props(['programados'])

@php
    $tiposLabel = ['informe_mensual' => 'Informe', 'alertas' => 'Alertas'];
    $frecLabels = ['diaria' => 'Diaria', 'semanal' => 'Semanal', 'quincenal' => 'Quincenal', 'mensual' => 'Mensual'];
@endphp

@if($programados->isEmpty())
    <x-ui.empty-state
        icon="clock"
        title="Sin reportes programados"
        description="Creá el primer reporte programado para automatizar el envío mensual al municipio."
    >
        <x-ui.button variant="outline" @click="openCreate()">
            <x-lucide-plus class="size-4" />
            Crear programado
        </x-ui.button>
    </x-ui.empty-state>
@else

    {{-- ── Mobile: cards ── --}}
    <div class="sm:hidden space-y-2">
        @foreach($programados as $p)
        <x-ui.card variant="elevated" class="p-3">
            <x-ui.card.header class="items-start gap-2">
                <div class="flex flex-col gap-1 min-w-0">
                    <span class="font-semibold text-sm truncate">{{ $p->nombre }}</span>
                    <div class="flex items-center gap-1.5">
                        <x-ui.badge variant="{{ $p->tipo === 'informe_mensual' ? 'default' : 'warning' }}" class="text-xs">
                            {{ $tiposLabel[$p->tipo] ?? $p->tipo }}
                        </x-ui.badge>
                        @if($p->activo)
                            <x-ui.badge variant="success" class="text-xs">Activo</x-ui.badge>
                        @else
                            <x-ui.badge variant="secondary" class="text-xs">Inactivo</x-ui.badge>
                        @endif
                    </div>
                </div>
                <x-slot:actions>
                    <x-ui.dropdown-menu>
                        <x-ui.dropdown-menu.trigger>
                            <x-ui.button variant="ghost" size="icon" class="size-7 -mr-1">
                                <x-lucide-ellipsis class="size-4" />
                            </x-ui.button>
                        </x-ui.dropdown-menu.trigger>
                        <x-ui.dropdown-menu.content align="end">
                            <x-ui.dropdown-menu.item
                                @click="openEdit({{ Js::from(['id' => $p->id, 'nombre' => $p->nombre, 'tipo' => $p->tipo, 'frecuencia' => $p->frecuencia, 'destinatarios_str' => implode(', ', $p->destinatarios), 'formatos' => $p->formatos(), 'revision' => $p->revisionOpcion(), 'activo' => $p->activo]) }})">
                                <x-lucide-pencil class="size-4" /> Editar
                            </x-ui.dropdown-menu.item>
                            <x-ui.dropdown-menu.item @click="confirmEnviar({{ $p->id }}, '{{ addslashes($p->nombre) }}', '{{ route('admin.reportes.programados.enviar-ahora', $p) }}')">
                                <x-lucide-send class="size-4" /> Enviar ahora
                            </x-ui.dropdown-menu.item>
                            <x-ui.dropdown-menu.item href="{{ route('admin.reportes.programados.pdf', $p) }}">
                                <x-lucide-file-text class="size-4" /> Descargar PDF
                            </x-ui.dropdown-menu.item>
                            <x-ui.dropdown-menu.item href="{{ route('admin.reportes.programados.excel', $p) }}">
                                <x-lucide-file-spreadsheet class="size-4" /> Descargar Excel
                            </x-ui.dropdown-menu.item>
                            <x-ui.dropdown-menu.separator />
                            <form id="delete-{{ $p->id }}" method="POST" action="{{ route('admin.reportes.programados.destroy', $p) }}">
                                @csrf @method('DELETE')
                            </form>
                            <x-ui.dropdown-menu.item variant="destructive" @click="confirmDelete({{ $p->id }}, '{{ addslashes($p->nombre) }}')">
                                <x-lucide-trash-2 class="size-4" /> Eliminar
                            </x-ui.dropdown-menu.item>
                        </x-ui.dropdown-menu.content>
                    </x-ui.dropdown-menu>
                </x-slot:actions>
            </x-ui.card.header>

            <x-ui.card.content class="flex flex-col gap-1.5 text-xs text-muted-foreground">
                <div class="flex items-center gap-1.5">
                    <x-lucide-calendar-clock class="size-3.5 shrink-0 text-primary" />
                    <span>
                        Próximo:
                        <span class="text-foreground font-medium">
                            {{ $p->proximo_envio_at?->format('d/m/Y H:i') ?? 'Sin calcular' }}
                        </span>
                    </span>
                </div>
                <div class="flex items-center gap-1.5">
                    <x-lucide-repeat class="size-3.5 shrink-0 text-primary" />
                    <span>{{ $frecLabels[$p->frecuencia] ?? $p->frecuencia }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <x-lucide-mail class="size-3.5 shrink-0 text-primary" />
                    <span class="truncate">{{ implode(', ', $p->destinatarios) }}</span>
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
                    <x-ui.table.head>Nombre</x-ui.table.head>
                    <x-ui.table.head>Tipo</x-ui.table.head>
                    <x-ui.table.head>Frecuencia</x-ui.table.head>
                    <x-ui.table.head>Destinatarios</x-ui.table.head>
                    <x-ui.table.head>Último envío</x-ui.table.head>
                    <x-ui.table.head>Próximo envío</x-ui.table.head>
                    <x-ui.table.head>Estado</x-ui.table.head>
                    <x-ui.table.head >Acciones</x-ui.table.head>
                </x-ui.table.row>
            </x-ui.table.header>
            <x-ui.table.body>
                @foreach($programados as $p)
                <x-ui.table.row>
                    <x-ui.table.cell class="font-medium text-nowrap">{{ $p->nombre }}</x-ui.table.cell>
                    <x-ui.table.cell>
                        <x-ui.badge class="text-nowrap" variant="{{ $p->tipo === 'informe_mensual' ? 'default' : 'warning' }}">
                            {{ $tiposLabel[$p->tipo] ?? $p->tipo }}
                        </x-ui.badge>
                    </x-ui.table.cell>
                    <x-ui.table.cell>{{ $frecLabels[$p->frecuencia] ?? $p->frecuencia }}</x-ui.table.cell>
                    <x-ui.table.cell>
                        <span class="text-caption">{{ implode(', ', $p->destinatarios) }}</span>
                    </x-ui.table.cell>
                    <x-ui.table.cell>
                        <span class="text-caption text-nowrap">{{ $p->ultimo_envio_at?->format('d/m/Y H:i') ?? '—' }}</span>
                    </x-ui.table.cell>
                    <x-ui.table.cell>
                        <span class="text-caption text-nowrap">{{ $p->proximo_envio_at?->format('d/m/Y H:i') ?? '—' }}</span>
                    </x-ui.table.cell>
                    <x-ui.table.cell>
                        @if($p->activo)
                            <x-ui.badge variant="success">Activo</x-ui.badge>
                        @else
                            <x-ui.badge variant="secondary">Inactivo</x-ui.badge>
                        @endif
                    </x-ui.table.cell>
                    <x-ui.table.cell class="text-right">
                        <x-ui.dropdown-menu>
                            <x-ui.dropdown-menu.trigger>
                                <x-ui.button variant="ghost" size="icon">
                                    <x-lucide-ellipsis-vertical class="size-4" />
                                </x-ui.button>
                            </x-ui.dropdown-menu.trigger>
                            <x-ui.dropdown-menu.content align="end">
                                <x-ui.dropdown-menu.item
                                    @click="openEdit({{ Js::from(['id' => $p->id, 'nombre' => $p->nombre, 'tipo' => $p->tipo, 'frecuencia' => $p->frecuencia, 'destinatarios_str' => implode(', ', $p->destinatarios), 'formatos' => $p->formatos(), 'revision' => $p->revisionOpcion(), 'activo' => $p->activo]) }})">
                                    <x-lucide-pencil class="size-4" /> Editar
                                </x-ui.dropdown-menu.item>
                                <x-ui.dropdown-menu.item @click="confirmEnviar({{ $p->id }}, '{{ addslashes($p->nombre) }}', '{{ route('admin.reportes.programados.enviar-ahora', $p) }}')">
                                    <x-lucide-send class="size-4" /> Enviar ahora
                                </x-ui.dropdown-menu.item>
                                <x-ui.dropdown-menu.item href="{{ route('admin.reportes.programados.pdf', $p) }}">
                                    <x-lucide-file-text class="size-4" /> Descargar PDF
                                </x-ui.dropdown-menu.item>
                                <x-ui.dropdown-menu.item href="{{ route('admin.reportes.programados.excel', $p) }}">
                                    <x-lucide-file-spreadsheet class="size-4" /> Descargar Excel
                                </x-ui.dropdown-menu.item>
                                <x-ui.dropdown-menu.separator />
                                <form id="delete-{{ $p->id }}" method="POST" action="{{ route('admin.reportes.programados.destroy', $p) }}">
                                    @csrf @method('DELETE')
                                </form>
                                <x-ui.dropdown-menu.item variant="destructive" @click="confirmDelete({{ $p->id }}, '{{ addslashes($p->nombre) }}')">
                                    <x-lucide-trash-2 class="size-4" /> Eliminar
                                </x-ui.dropdown-menu.item>
                            </x-ui.dropdown-menu.content>
                        </x-ui.dropdown-menu>
                    </x-ui.table.cell>
                </x-ui.table.row>
                @endforeach
            </x-ui.table.body>
        </x-ui.table>
    </x-ui.card>

@endif
