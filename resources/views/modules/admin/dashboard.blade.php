<x-layouts.app title="Dashboard">

@php
    $todayISO = now()->format('Y-m-d');
    $dashboardInit = [
        'refreshUrl'          => route('admin.dashboard.data'),
        'kpisDia'             => $kpisDia,
        'kpisMes'             => $kpisMes,
        'evolucion7'          => $evolucion7,
        'evolucion15'         => $evolucion15,
        'evolucion90'         => $evolucion90,
        'desgloseVehiculo'    => $desgloseVehiculo,
        'desgloseZona'        => $desgloseZona,
        'desgloseVehiculoMes' => $desgloseVehiculoMes,
        'desgloseZonaMes'     => $desgloseZonaMes,
        'alertas'             => $alertas,
    ];
@endphp

@push('scripts')
<script>
    window.__dashboardData = @json($dashboardInit);
</script>
@endpush

<div class="flex flex-col gap-6" x-data="dashboardData()"
     @dp-desde.window="tmpDesde = $event.detail"
     @dp-hasta.window="tmpHasta = $event.detail">

    {{-- Encabezado --}}
    <div class="flex items-center justify-between">
        <div>
            <x-ui.typography as="h2">Dashboard</x-ui.typography>
            <x-ui.typography as="muted" class="mt-1">
                {{ now()->translatedFormat('l d \d\e F \d\e Y') }}
            </x-ui.typography>
        </div>
        <button @click="refresh()" :disabled="refreshing"
                class="inline-flex items-center gap-1.5 rounded-full border border-border px-2.5 py-0.5 text-xs font-medium text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground disabled:opacity-50">
            <x-lucide-refresh-cw class="size-3" x-bind:class="refreshing && 'animate-spin'" />
            <span x-text="refreshing ? 'Actualizando…' : 'Últ: ' + lastRefresh"></span>
        </button>
    </div>

    {{-- Banner alertas --}}
    <x-domain.dashboard.banner-alertas />

    {{-- Tabs: Hoy / Este mes / Personalizado --}}
    <x-ui.tabs value="hoy" @activate-tab.window="active = $event.detail">
        <div class="flex items-center justify-between gap-2"
             x-data="{ _d: null, _h: null }"
             @dp-desde.window="_d = $event.detail"
             @dp-hasta.window="_h = $event.detail">
            <x-ui.tabs.list class="shrink-0">
                <x-ui.tabs.trigger value="hoy">
                    <span>Hoy</span>
                    <span class="hidden sm:inline text-muted-foreground font-normal">&nbsp;— {{ now()->translatedFormat('j \d\e F') }}</span>
                </x-ui.tabs.trigger>
                <x-ui.tabs.trigger value="mes">
                    <span class="sm:hidden">{{ now()->format('n/Y') }}</span>
                    <span class="hidden sm:inline">{{ now()->format('m/Y') }}</span>
                </x-ui.tabs.trigger>
                <x-ui.tabs.trigger value="personalizado" x-show="desdeRango" x-cloak>
                    <x-lucide-calendar-range class="size-3 opacity-60" />
                    <span x-text="rangoLabel()"></span>
                </x-ui.tabs.trigger>
            </x-ui.tabs.list>

            <div class="flex items-center gap-1 shrink-0">
            <x-domain.dashboard.mobile-kpis />

            {{-- Info --}}
            <x-ui.popover width="w-72" align="end">
                <x-slot:trigger>
                    <x-ui.button variant="ghost" size="icon">
                        <x-lucide-circle-help class="size-4" />
                    </x-ui.button>
                </x-slot:trigger>
                <div class="space-y-3">
                    <p class="text-sm font-medium">Sobre este dashboard</p>
                    <div class="space-y-2 text-xs text-muted-foreground">
                        <p>Los KPIs incluyen todos los pesajes excepto los cancelados.</p>
                        <p>Los porcentajes de variación comparan contra el mismo período del mes anterior.</p>
                        <p>Los datos se actualizan automáticamente cada 10 minutos. También podés refrescar manualmente con el botón de la esquina.</p>
                    </div>
                </div>
            </x-ui.popover>

            {{-- Date range picker --}}
            <x-ui.popover width="w-72" align="end">
                    <x-slot:trigger>
                        <x-ui.button variant="ghost"
                                x-bind:class="desdeRango ? 'text-primary' : ''"
                                class="hidden sm:flex gap-1.5 px-2.5">
                            <x-lucide-calendar-range class="size-4 shrink-0" />
                            <span x-show="!desdeRango" x-cloak
                                  class="text-sm font-medium">Filtrar por período</span>
                            <span x-show="desdeRango" x-cloak
                                  x-text="rangoLabel()"
                                  class="text-xs font-medium tabular-nums"></span>
                        </x-ui.button>
                        <x-ui.button variant="ghost" size="icon"
                                x-bind:class="desdeRango ? 'text-primary' : ''"
                                class="sm:hidden">
                            <x-lucide-calendar-range class="size-4 shrink-0" />
                        </x-ui.button>
                    </x-slot:trigger>
                    <div class="space-y-4">
                        <p class="text-sm font-medium">Filtrar por período</p>
                        <div class="space-y-3">
                            <div class="space-y-1.5">
                                <x-ui.label>Desde</x-ui.label>
                                <div x-on:date-picked="$dispatch('dp-desde', $event.detail.value)">
                                    <x-ui.date-picker
                                        placeholder="Fecha inicio"
                                        max-date="{{ $todayISO }}"
                                    />
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <x-ui.label>Hasta</x-ui.label>
                                <div x-on:date-picked="$dispatch('dp-hasta', $event.detail.value)">
                                    <x-ui.date-picker
                                        placeholder="Fecha fin"
                                        max-date="{{ $todayISO }}"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-ui.button size="sm" class="flex-1"
                                         @click="applyRango(_d, _h); _close()">
                                Aplicar
                            </x-ui.button>
                            <x-ui.button size="sm" variant="ghost" x-show="desdeRango" x-cloak
                                         @click="clearRango(); _close()">
                                Limpiar
                            </x-ui.button>
                        </div>
                    </div>
            </x-ui.popover>
            </div>{{-- /acciones --}}
        </div>{{-- /flex tabs nav --}}

        {{-- Tab: Hoy --}}
        <x-ui.tabs.content value="hoy">
            <div class="flex flex-col gap-4">
                <x-domain.dashboard.kpis-dia />
                {{-- Empty state: sin pesajes hoy --}}
                <div x-show="kpisDia.total === 0" x-cloak>
                    <x-ui.card variant="elevated">
                        <x-ui.card.content>
                            <div class="flex flex-col items-center justify-center py-10 text-center gap-3">
                                <x-lucide-scale class="size-8 text-muted-foreground" />
                                <div class="space-y-1">
                                    <p class="text-sm font-medium">Sin pesajes registrados hoy todavía.</p>
                                    <p class="text-xs text-muted-foreground">Los desgloses de flota y zona aparecerán con el primer pesaje del día.</p>
                                </div>
                            </div>
                        </x-ui.card.content>
                    </x-ui.card>
                </div>

                <div class="grid grid-cols-1 gap-6" x-show="kpisDia.total > 0" x-cloak>
                    <x-domain.dashboard.desglose-vehiculo source="desgloseVehiculo" description="Distribución de flota del día" />
                    <x-domain.dashboard.desglose-zona source="desgloseZona" description="Actividad del día por zona de recolección" />
                </div>
            </div>
        </x-ui.tabs.content>

        {{-- Tab: Este mes --}}
        <x-ui.tabs.content value="mes">
            <div class="flex flex-col gap-4">
                <x-domain.dashboard.kpis-mes />
                <x-domain.dashboard.evolucion />
                <div x-show="kpisMes.total === 0" x-cloak>
                    <x-ui.card variant="elevated">
                        <x-ui.card.content>
                            <div class="flex flex-col items-center justify-center py-10 text-center gap-3">
                                <x-lucide-scale class="size-8 text-muted-foreground" />
                                <div class="space-y-1">
                                    <p class="text-sm font-medium">Sin pesajes registrados este mes todavía.</p>
                                    <p class="text-xs text-muted-foreground">Los desgloses de flota y zona aparecerán con el primer pesaje del mes.</p>
                                </div>
                            </div>
                        </x-ui.card.content>
                    </x-ui.card>
                </div>
                <div class="grid grid-cols-1 gap-6" x-show="kpisMes.total > 0" x-cloak>
                    <x-domain.dashboard.desglose-vehiculo source="desgloseVehiculoMes" description="Distribución de flota del mes" />
                    <x-domain.dashboard.desglose-zona source="desgloseZonaMes" description="Actividad del mes por zona de recolección" />
                </div>
            </div>
        </x-ui.tabs.content>

        {{-- Tab: Período personalizado --}}
        <x-ui.tabs.content value="personalizado">
            <template x-if="kpisRango">
                <div class="flex flex-col gap-6 pt-6">
                    <x-domain.dashboard.kpis-rango />
                    <x-domain.dashboard.evolucion-rango />
                    <div class="grid grid-cols-1 gap-6">
                        <x-domain.dashboard.desglose-vehiculo source="desgloseVehiculoRango" description="Distribución de flota en el período" />
                        <x-domain.dashboard.desglose-zona source="desgloseZonaRango" description="Actividad del período por zona de recolección" />
                    </div>
                </div>
            </template>
        </x-ui.tabs.content>
    </x-ui.tabs>

</div>

</x-layouts.app>
