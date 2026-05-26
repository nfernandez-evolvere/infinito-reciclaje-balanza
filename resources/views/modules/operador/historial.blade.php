<x-layouts.app title="Historial">

    <x-slot:footerTurno>
        <span>Pesajes hoy: <b class="text-foreground">{{ $kpis['total'] }}</b></span>
        <x-ui.separator orientation="vertical" class="h-3.5" />
        <span>Netas: <b class="text-foreground font-mono">{{ number_format($kpis['toneladas_netas'], 1, ',', '.') }} t</b></span>
        <x-ui.separator orientation="vertical" class="h-3.5" />
        <span>En predio: <b class="text-foreground">{{ $kpis['en_predio'] }}</b></span>
    </x-slot:footerTurno>

    @php $ultimoPesaje = $pesajes->first(); @endphp
    <x-slot:footerUltimo>
        @if($ultimoPesaje)
            Último: <b class="text-foreground ml-1">{{ $ultimoPesaje->vehiculo->patente }}</b>
            <span class="ml-1.5 font-mono">{{ number_format($ultimoPesaje->peso_neto_kg, 0, ',', '.') }} kg</span>
            <span class="text-muted-foreground/60 ml-1.5">{{ $ultimoPesaje->created_at->format('H:i') }}</span>
        @endif
    </x-slot:footerUltimo>

<div class="flex flex-col gap-6" x-data="historial()">

    {{-- Encabezado --}}
    <div>
        <x-ui.typography as="h2">Historial del turno</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1">Pesajes registrados hoy · {{ now()->format('d/m/Y') }}</x-ui.typography>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <x-ui.kpi title="Pesajes" icon="scale" help="Total de pesajes registrados en el turno actual.">
            {{ $kpis['total'] }}
        </x-ui.kpi>
        <x-ui.kpi title="Toneladas netas" icon="weight" help="Suma de pesos netos de todos los pesajes cerrados en el turno.">
            {{ number_format($kpis['toneladas_netas'], 1, ',', '.') }} t
        </x-ui.kpi>
        <x-ui.kpi title="Promedio neto" icon="chart-bar" help="Peso neto promedio por pesaje en el turno actual.">
            {{ number_format($kpis['promedio_kg'], 0, ',', '.') }} kg
        </x-ui.kpi>
        <x-ui.kpi title="En predio" icon="truck" help="Vehículos con entrada registrada que aún no tienen salida.">
            {{ $kpis['en_predio'] }}
        </x-ui.kpi>
    </div>

    {{-- Tabla --}}

    @if($pesajes->isEmpty())
        <x-ui.card variant="elevated">
            <x-ui.card.content class="p-6">
                <div class="flex flex-col items-center justify-center py-16 gap-2 text-muted-foreground">
                    <x-lucide-scale class="size-8 opacity-30" />
                    <p class="text-sm">Sin pesajes en este turno todavía.</p>
                </div>
            </x-ui.card.content>
        </x-ui.card>
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
                    <x-ui.table.head>Bruto</x-ui.table.head>
                    <x-ui.table.head>Tara</x-ui.table.head>
                    <x-ui.table.head>Neto</x-ui.table.head>
                    <x-ui.table.head>Acciones</x-ui.table.head>
                </x-ui.table.row>
            </x-ui.table.header>
            <x-ui.table.body>
                @foreach($pesajes as $pesaje)
                <x-ui.table.row class="{{ $pesaje->alerta_peso ? 'bg-warning/5' : '' }}">
                    <x-ui.table.cell class="pl-6 font-mono text-xs tabular-nums">
                        {{ $pesaje->created_at->format('H:i') }}
                    </x-ui.table.cell>
                    <x-ui.table.cell class="font-mono text-xs tabular-nums text-muted-foreground">
                        {{ $pesaje->hora_salida?->format('H:i') ?? '—' }}
                    </x-ui.table.cell>
                    <x-ui.table.cell>
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
                    <x-ui.table.cell class="font-medium">{{ $pesaje->vehiculo->patente }}</x-ui.table.cell>
                    <x-ui.table.cell class="text-sm">{{ $pesaje->tipoServicio->nombre }}</x-ui.table.cell>
                    <x-ui.table.cell class="text-sm text-muted-foreground">{{ $pesaje->zona->nombre }}</x-ui.table.cell>
                    <x-ui.table.cell class="text-right font-mono text-xs tabular-nums">
                        {{ number_format($pesaje->peso_bruto_kg, 0, ',', '.') }} kg
                    </x-ui.table.cell>
                    <x-ui.table.cell class="text-right font-mono text-xs tabular-nums text-muted-foreground">
                        {{ number_format($pesaje->peso_tara_kg, 0, ',', '.') }} kg
                    </x-ui.table.cell>
                    <x-ui.table.cell class="text-right font-mono text-xs tabular-nums font-semibold">
                        {{ number_format($pesaje->peso_neto_kg, 0, ',', '.') }} kg
                    </x-ui.table.cell>
                    <x-ui.table.cell class="pr-6 text-right">
                        <div class="flex items-center justify-end gap-1">
                            @if($pesaje->editado)
                                <x-ui.tooltip content="Ver historial de cambios" side="left">
                                    <x-ui.button variant="ghost" size="icon" class="size-7"
                                        @click="abrirLog('{{ $pesaje->uuid }}', '{{ addslashes($pesaje->vehiculo->patente) }}')">
                                        <x-lucide-history class="size-3.5" />
                                    </x-ui.button>
                                </x-ui.tooltip>
                            @endif
                            <x-ui.tooltip content="Editar pesaje" side="left">
                                <x-ui.button variant="ghost" size="icon" class="size-7"
                                    @click="abrirEdicion('{{ $pesaje->uuid }}', {
                                        patente: '{{ addslashes($pesaje->vehiculo->patente) }}',
                                        peso_bruto_kg: {{ $pesaje->peso_bruto_kg }},
                                        observaciones: '{{ addslashes($pesaje->observaciones ?? '') }}'
                                    })">
                                    <x-lucide-pencil class="size-3.5" />
                                </x-ui.button>
                            </x-ui.tooltip>
                            @if($pesaje->estaEnPredio())
                                <x-ui.tooltip content="Marcar egreso" side="left">
                                    <x-ui.button variant="ghost" size="icon" class="size-7"
                                        @click="abrirEgreso('{{ $pesaje->uuid }}', '{{ addslashes($pesaje->vehiculo->patente) }}')">
                                        <x-lucide-log-out class="size-3.5" />
                                    </x-ui.button>
                                </x-ui.tooltip>
                            @endif
                        </div>
                    </x-ui.table.cell>
                </x-ui.table.row>
                @endforeach
            </x-ui.table.body>
        </x-ui.table>
    @endif

    {{-- Modal egreso (teleportado a body, comparte x-data del padre) --}}
    <template x-teleport="body">
        <div
            x-show="modalEgreso"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center bg-background/80 backdrop-blur-sm"
            @click.self="modalEgreso = false"
        >
            <div class="w-full max-w-md rounded-xl border border-border bg-card p-6 shadow-xl" @click.stop>
                <h2 class="text-h4 mb-1">Marcar egreso</h2>
                <p class="text-sm text-muted-foreground mb-4" x-text="'Vehículo: ' + egresoPatente"></p>
                <form :action="'/pesajes/' + egresoId + '/egreso'" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <x-ui.label>Hora de egreso</x-ui.label>
                            <div class="text-sm font-mono font-semibold" x-text="horaActual"></div>
                        </div>
                        <div class="space-y-2">
                            <x-ui.label for="bruto_salida_kg">Peso bruto de salida (opcional)</x-ui.label>
                            <x-ui.input id="bruto_salida_kg" name="bruto_salida_kg" type="number" min="1" inputmode="numeric" placeholder="—" />
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 mt-6">
                        <x-ui.button type="button" variant="ghost" @click="modalEgreso = false">Cancelar</x-ui.button>
                        <x-ui.button type="submit">Registrar egreso</x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- Modal editar --}}
    <template x-teleport="body">
        <div
            x-show="modalEdicion"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center bg-background/80 backdrop-blur-sm"
            @click.self="modalEdicion = false"
        >
            <div class="w-full max-w-md rounded-xl border border-border bg-card p-6 shadow-xl" @click.stop>
                <h2 class="text-h4 mb-1">Editar pesaje</h2>
                <p class="text-sm text-muted-foreground mb-4" x-text="'Vehículo: ' + edicionData.patente"></p>
                <form :action="'/pesajes/' + edicionId" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <x-ui.label for="edit_peso_bruto_kg">Peso bruto (kg)</x-ui.label>
                            <x-ui.input id="edit_peso_bruto_kg" name="peso_bruto_kg" type="number" min="1"
                                inputmode="numeric" x-bind:value="edicionData.peso_bruto_kg" />
                        </div>
                        <div class="space-y-2">
                            <x-ui.label for="edit_observaciones">Observaciones</x-ui.label>
                            <textarea id="edit_observaciones" name="observaciones" rows="2"
                                placeholder="Observaciones del pesaje…"
                                x-bind:value="edicionData.observaciones"
                                class="flex min-h-15 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                            ></textarea>
                        </div>
                        <div class="space-y-2">
                            <x-ui.label for="edit_motivo">Motivo de la edición</x-ui.label>
                            <x-ui.input id="edit_motivo" name="motivo" type="text"
                                placeholder="Ej.: corrección de patente, error en el peso bruto…"
                                x-model="edicionMotivo" />
                            <p x-show="!edicionMotivo" x-cloak class="text-xs text-destructive">Describí el motivo antes de guardar.</p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 mt-6">
                        <x-ui.button type="button" variant="ghost" @click="modalEdicion = false">Cancelar</x-ui.button>
                        <x-ui.button type="submit" x-bind:disabled="!edicionMotivo">Guardar cambios</x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- Modal historial de cambios --}}
    <template x-teleport="body">
        <div
            x-show="modalLog"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center bg-background/80 backdrop-blur-sm"
            @click.self="modalLog = false"
        >
            <div class="w-full max-w-lg rounded-xl border border-border bg-card p-6 shadow-xl" @click.stop>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-h4">Historial de cambios</h2>
                        <p class="text-sm text-muted-foreground" x-text="'Vehículo: ' + logPatente"></p>
                    </div>
                    <x-ui.button variant="ghost" size="icon" @click="modalLog = false">
                        <x-lucide-x class="size-4" />
                    </x-ui.button>
                </div>

                <div x-show="logCargando" class="py-8 flex justify-center">
                    <x-ui.skeleton class="h-4 w-48" />
                </div>

                <div x-show="!logCargando && logEntradas.length === 0" x-cloak class="py-8 text-center text-sm text-muted-foreground">
                    Sin cambios registrados.
                </div>

                <div x-show="!logCargando && logEntradas.length > 0" class="space-y-3 max-h-80 overflow-y-auto pr-1">
                    <template x-for="entrada in logEntradas" :key="entrada.id">
                        <div class="rounded-md border border-border p-3 text-sm">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-medium" x-text="entrada.campo"></span>
                                <span class="text-xs text-muted-foreground" x-text="entrada.fecha"></span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                <span x-text="entrada.valor_anterior || '—'"></span>
                                <x-lucide-arrow-right class="size-3 shrink-0" />
                                <span class="font-medium text-foreground" x-text="entrada.valor_nuevo || '—'"></span>
                            </div>
                            <div class="mt-1.5 text-xs text-muted-foreground">
                                <span class="font-medium">Motivo:</span> <span x-text="entrada.motivo"></span>
                                · <span x-text="entrada.usuario"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>

</div>


</x-layouts.app>
