<x-layouts.app title="Preview · Reportes">

<div class="flex flex-col gap-8">

    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-overline">Informe de recolección</p>
            <h2 class="text-h2 mt-1">
                {{ $reporte['desde']->translatedFormat('d M') }} — {{ $reporte['hasta']->translatedFormat('d M Y') }}
            </h2>
            <div class="flex flex-wrap items-center gap-1.5 mt-2">
                <x-ui.badge variant="secondary">Zona Norte</x-ui.badge>
                <x-ui.badge variant="secondary">Camión compactador</x-ui.badge>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <x-ui.button variant="ghost" size="sm">
                <x-lucide-calendar-days class="size-4" />
                Cambiar período
            </x-ui.button>
            <x-ui.button variant="outline" size="sm">
                <x-lucide-table class="size-4" />
                <span class="hidden sm:inline">Excel</span>
            </x-ui.button>
            <x-ui.button size="sm" href="{{ route('admin.reportes.preview-pdf') }}" target="_blank">
                <x-lucide-file-text class="size-4" />
                <span class="hidden sm:inline">PDF</span>
            </x-ui.button>
        </div>
    </div>

    {{-- KPIs --}}
    <section class="flex flex-col gap-4">
        <p class="text-overline">Resumen del período</p>
        <x-domain.reportes.kpis :kpis="$kpis" />
    </section>

    {{-- Evolución --}}
    <x-domain.reportes.evolucion :evolucion="$evolucion" />

    {{-- Desglose --}}
    <section class="flex flex-col gap-4">
        <p class="text-overline">Desglose por segmento</p>
        <div class="flex flex-col gap-6">
            <x-domain.reportes.tabla-zonas :zonas="$zonas" />
            <x-domain.reportes.tabla-vehiculos :vehiculos="$vehiculos" />
        </div>
    </section>

    <p class="text-caption text-center">
        Vista de desarrollo — <a href="{{ route('admin.reportes.index') }}" class="underline">Volver a reportes</a>
    </p>

</div>

</x-layouts.app>
