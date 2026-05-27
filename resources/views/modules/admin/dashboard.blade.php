<x-layouts.app title="Dashboard">

<div
    class="flex flex-col gap-6"
    x-data="{ lastRefresh: new Date().toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' }) }"
    x-init="setInterval(() => window.location.reload(), 60000)"
>

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <x-ui.typography as="h2">Dashboard</x-ui.typography>
            <x-ui.typography as="muted" class="mt-1">
                {{ now()->translatedFormat('l d \d\e F \d\e Y') }}
            </x-ui.typography>
        </div>
        <div class="flex items-center gap-2 text-xs text-muted-foreground">
            <x-lucide-refresh-cw class="size-3.5" />
            <span>Actualiza cada 60 s &middot; Último: <span x-text="lastRefresh"></span></span>
        </div>
    </div>

    {{-- Banner alertas (oculto hasta Sprint 6) --}}
    <x-domain.dashboard.banner-alertas :alertas="$alertas" />

    {{-- Mobile: drawer con todas las métricas --}}
    <x-domain.dashboard.mobile-kpis :kpisDia="$kpisDia" :kpisMes="$kpisMes" />

    {{-- KPIs del día (desktop) --}}
    <div>
        <p class="hidden sm:block text-overline mb-0 sm:mb-3">Hoy · {{ now()->format('d/m/Y') }}</p>
        <x-domain.dashboard.kpis-dia :kpis="$kpisDia" />
    </div>

    {{-- KPIs del mes (desktop) --}}
    <div>
        <p class="hidden sm:block text-overline mb-0 sm:mb-3">{{ now()->translatedFormat('F Y') }}</p>
        <x-domain.dashboard.kpis-mes :kpis="$kpisMes" />
    </div>

    {{-- Evolución diaria --}}
    <x-domain.dashboard.evolucion :evolucion7="$evolucion7" :evolucion15="$evolucion15" :evolucion90="$evolucion90" />

    {{-- Desglose --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-domain.dashboard.desglose-zona :desglose="$desgloseZona" />
        <x-domain.dashboard.desglose-vehiculo :desglose="$desgloseVehiculo" />
    </div>

</div>

</x-layouts.app>
