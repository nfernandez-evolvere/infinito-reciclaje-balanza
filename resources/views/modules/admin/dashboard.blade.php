<x-layouts.app title="Dashboard">

@php
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

<div class="flex flex-col gap-6" x-data="dashboardData()">

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <x-ui.typography as="h2">Dashboard</x-ui.typography>
            <x-ui.typography as="muted" class="mt-1">
                {{ now()->translatedFormat('l d \d\e F \d\e Y') }}
            </x-ui.typography>
        </div>
        <div class="flex items-center gap-2 text-xs text-muted-foreground">
            <x-lucide-refresh-cw class="size-3.5" x-bind:class="refreshing && 'animate-spin'" />
            <span>Actualiza cada 60 s &middot; Último: <span x-text="lastRefresh"></span></span>
        </div>
    </div>

    {{-- Banner alertas --}}
    <x-domain.dashboard.banner-alertas />

    {{-- Tabs: Hoy / Este mes --}}
    <x-ui.tabs value="hoy">
        <x-ui.tabs.list>
            <x-ui.tabs.trigger value="hoy">Hoy &middot; {{ now()->format('d/m') }}</x-ui.tabs.trigger>
            <x-ui.tabs.trigger value="mes">{{ ucfirst(now()->translatedFormat('F Y')) }}</x-ui.tabs.trigger>
        </x-ui.tabs.list>

        {{-- Tab: Hoy --}}
        <x-ui.tabs.content value="hoy">
            <div class="flex flex-col gap-6 pt-6">
                <x-domain.dashboard.kpis-dia />
                <div class="grid grid-cols-1 gap-6">
                    <x-domain.dashboard.desglose-vehiculo source="desgloseVehiculo" description="Distribución de flota del día" />
                    <x-domain.dashboard.desglose-zona source="desgloseZona" description="Actividad del día por zona de recolección" />
                </div>
            </div>
        </x-ui.tabs.content>

        {{-- Tab: Este mes --}}
        <x-ui.tabs.content value="mes">
            <div class="flex flex-col gap-6 pt-6">
                <x-domain.dashboard.kpis-mes />
                <x-domain.dashboard.evolucion />
                <div class="grid grid-cols-1 gap-6">
                    <x-domain.dashboard.desglose-vehiculo source="desgloseVehiculoMes" description="Distribución de flota del mes" />
                    <x-domain.dashboard.desglose-zona source="desgloseZonaMes" description="Actividad del mes por zona de recolección" />
                </div>
            </div>
        </x-ui.tabs.content>
    </x-ui.tabs>

</div>

</x-layouts.app>
