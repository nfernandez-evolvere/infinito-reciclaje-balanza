<x-layouts.app title="Reportes">

<div class="space-y-6">

    @if($errors->has('pdf'))
    <x-ui.alert variant="destructive">
        <x-lucide-circle-alert class="size-4" />
        <x-ui.alert.title>No se pudo generar el PDF</x-ui.alert.title>
        <x-ui.alert.description>{{ $errors->first('pdf') }}</x-ui.alert.description>
    </x-ui.alert>
    @endif

    {{-- Encabezado + botones de descarga --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-h2">Reportes</h1>
            <p class="text-lead mt-1">Generá reportes de pesajes por período, zona y tipo de servicio.</p>
        </div>

        @if($reporte)
        @php
            $exportParams = array_filter(request()->only(['desde', 'hasta', 'zona_id', 'tipo_servicio_id', 'tipo_vehiculo_id']));
        @endphp
        <div class="flex items-center gap-2 shrink-0">
            <x-ui.button variant="outline" size="sm"
                href="{{ route('admin.reportes.excel', $exportParams) }}"
            >
                <x-lucide-table class="size-4 mr-2" />
                Excel
            </x-ui.button>
            <x-ui.button size="sm"
                href="{{ route('admin.reportes.pdf', $exportParams) }}"
            >
                <x-lucide-file-text class="size-4 mr-2" />
                Descargar PDF
            </x-ui.button>
        </div>
        @endif
    </div>

    {{-- Formulario de filtros --}}
    <x-domain.reportes.filtros
        :zonas="$zonas"
        :tiposServicio="$tiposServicio"
        :tiposVehiculo="$tiposVehiculo"
    />

    {{-- Resultado o empty state --}}
    @if($reporte)

        {{-- Pills de filtros activos --}}
        @php
            $filtrosActivos = array_filter([
                request('zona_id') ? 'Zona: ' . ($zonas->firstWhere('id', request('zona_id'))?->nombre ?? '') : null,
                request('tipo_servicio_id') ? 'Servicio: ' . ($tiposServicio->firstWhere('id', request('tipo_servicio_id'))?->nombre ?? '') : null,
                request('tipo_vehiculo_id') ? 'Vehículo: ' . ($tiposVehiculo->firstWhere('id', request('tipo_vehiculo_id'))?->nombre ?? '') : null,
            ]);
        @endphp
        @if($filtrosActivos)
        <div class="flex flex-wrap gap-2">
            @foreach($filtrosActivos as $filtro)
                <x-ui.badge variant="secondary">{{ $filtro }}</x-ui.badge>
            @endforeach
        </div>
        @endif

        {{-- KPIs --}}
        <x-domain.reportes.kpis :kpis="$reporte['kpis']" />

        {{-- Gráfico evolución --}}
        @if(!empty($reporte['evolucion']['datos']))
            <x-domain.reportes.evolucion :evolucion="$reporte['evolucion']" />
        @endif

        {{-- Tablas en grid --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <x-domain.reportes.tabla-zonas :zonas="$reporte['zonas']" />
            <x-domain.reportes.tabla-vehiculos :vehiculos="$reporte['vehiculos']" />
        </div>

    @else

        {{-- Empty state --}}
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <div class="rounded-full bg-muted p-4 mb-4">
                <x-lucide-file-bar-chart class="size-8 text-muted-foreground" />
            </div>
            <h3 class="text-h4 mb-1">Seleccioná un período</h3>
            <p class="text-lead max-w-sm">
                Elegí un rango de fechas y hacé clic en "Generar reporte" para ver los datos del período.
            </p>
        </div>

    @endif

</div>

</x-layouts.app>
