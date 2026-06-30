@php
    $hasProgramadoErrors = $errors->hasAny(['nombre', 'tipo', 'frecuencia', 'destinatarios', 'formatos', 'revision', 'activo']);
    $isEditing      = old('_mode') === 'edit';
    $defaultTipo    = ($config->tipo_informe_mensual_activo ?? true) ? 'informe_mensual' : 'alertas';
    $initialProgramado = $hasProgramadoErrors ? [
        'modalOpen' => true,
        'modalMode' => $isEditing ? 'edit' : 'create',
        'form' => [
            'id'         => (int) old('_editing_id', 0) ?: null,
            'nombre'     => old('nombre', ''),
            'tipo'       => old('tipo', $defaultTipo),
            'frecuencia' => old('frecuencia', 'mensual'),
            'formatos'   => array_values(array_intersect(['pdf', 'excel'], (array) old('formatos', ['pdf']))) ?: ['pdf'],
            'revision'   => old('revision', 'heredar'),
            'activo'     => old('activo', true),
        ],
        '_oldDestinatariosStr' => old('destinatarios', ''),
    ] : [];
@endphp

<x-layouts.app title="Reportes">
<x-ui.tabs
    :value="$hasProgramadoErrors ? 'programados' : $tab"
    class="flex flex-col gap-6"
    x-init="$store.reportesPendientes.count = {{ (int) $pendientesRevision }};
    $watch('active', val => {
        const url = new URL(window.location);
        url.searchParams.set('tab', val);
        history.pushState({}, '', url);
    })"
>

    <div class="flex flex-col items-start gap-2">
        <x-ui.typography as="h2">Reportes</x-ui.typography>
        <x-ui.typography as="muted">Generá y programá reportes para exportar o enviar al municipio.</x-ui.typography>
    </div>

    <x-ui.alert
        state="warning"
        description="No se enviarán a los destinatarios hasta que los apruebes."
        x-show="$store.reportesPendientes.count > 0"
        x-cloak
    >
        <x-slot:title>
            <span x-text="$store.reportesPendientes.count === 1
                ? 'Hay 1 reporte pendiente de revisión'
                : 'Hay ' + $store.reportesPendientes.count + ' reportes pendientes de revisión'"></span>
        </x-slot:title>
        <x-slot:action>
            <x-ui.button variant="ghost" state="warning" @click="active = 'historial'">
                Revisar
            </x-ui.button>
        </x-slot:action>
    </x-ui.alert>

    <x-ui.tabs.list class="flex w-full sm:w-fit">
        <x-ui.tabs.trigger value="programados" class="flex-1 sm:flex-none">
            <x-lucide-clock class="size-4" />
            <span x-show="active === 'programados'" x-cloak class="sm:inline">Programados</span>
            <span class="hidden sm:inline" x-show="active !== 'programados'">Programados</span>
        </x-ui.tabs.trigger>
        <x-ui.tabs.trigger value="generar" class="flex-1 sm:flex-none">
            <x-lucide-file-bar-chart class="size-4" />
            <span x-show="active === 'generar'" x-cloak class="sm:inline">Generar</span>
            <span class="hidden sm:inline" x-show="active !== 'generar'">Generar</span>
        </x-ui.tabs.trigger>
        <x-ui.tabs.trigger value="historial" class="flex-1 sm:flex-none">
            <x-lucide-history class="size-4" />
            <span x-show="active === 'historial'" x-cloak class="sm:inline">Historial</span>
            <span class="hidden sm:inline" x-show="active !== 'historial'">Historial</span>
            <x-ui.badge variant="warning" class="text-xs"
                x-show="$store.reportesPendientes.count > 0" x-cloak
                x-text="$store.reportesPendientes.count"></x-ui.badge>
        </x-ui.tabs.trigger>
        <x-ui.tabs.trigger value="configuracion" class="flex-1 sm:flex-none">
            <x-lucide-settings-2 class="size-4" />
            <span x-show="active === 'configuracion'" x-cloak class="sm:inline">Configuración</span>
            <span class="hidden sm:inline" x-show="active !== 'configuracion'">Configuración</span>
        </x-ui.tabs.trigger>
    </x-ui.tabs.list>

    {{-- ── Tab: Generar reporte ── --}}
    <x-ui.tabs.content value="generar" class="mt-0">
        <div x-data="{ filterOpen: false }" class="flex flex-col gap-8">

            <x-domain.reportes.header-generar
                :reporte="$reporte"
                :filters="$filters"
                :zonas="$zonas"
                :tiposServicio="$tiposServicio"
                :tiposVehiculo="$tiposVehiculo"
                :activeFilters="$activeFilters"
            />

            @if($reporte)
                {{-- Resumen del período · en mobile/tablet vive en el drawer del header --}}
                <section class="hidden xl:flex xl:flex-col gap-4">
                    <p class="text-overline">Resumen del período</p>
                    <x-domain.reportes.kpis :kpis="$reporte['kpis']" />
                </section>

                @if(!empty($reporte['evolucion']['datos']))
                    <x-domain.reportes.evolucion :evolucion="$reporte['evolucion']" />
                @endif

                {{-- En reportes el selector refleja el alcance del informe (servicios
                     presentes en el dataset), por eso no se pasa la lista completa. --}}
                <x-domain.mapa-calor.panel
                    :zonas="$mapaZonas"
                    description="Intensidad de recolección del período por zona, según los filtros aplicados. Suma todos los turnos de cada zona."
                />

                <section class="flex flex-col gap-4">
                    <p class="text-overline">Desglose por segmento</p>
                    <div class="flex flex-col gap-6">
                        <x-domain.reportes.tabla-zonas :zonas="$reporte['zonas']" />
                        <x-domain.reportes.tabla-vehiculos :vehiculos="$reporte['vehiculos']" />
                    </div>
                </section>
            @else
                <x-ui.empty-state
                    icon="file-bar-chart"
                    title="Sin reporte generado"
                    description="Usá los filtros para seleccionar un período y generar el reporte."
                    class="py-20"
                >
                    <x-ui.button variant="outline" @click="filterOpen = true">
                        <x-lucide-calendar-days class="size-4" />
                        Seleccionar período
                    </x-ui.button>
                </x-ui.empty-state>
            @endif

            <x-domain.reportes.filtros
                :zonas="$zonas"
                :tiposServicio="$tiposServicio"
                :tiposVehiculo="$tiposVehiculo"
                :filters="$filters"
            />

        </div>
    </x-ui.tabs.content>

    {{-- ── Tab: Programados ── --}}
    <x-ui.tabs.content value="programados" class="mt-0">
        <div x-data="reportesProgramados({{ Js::from($initialProgramado) }})" class="flex flex-col gap-6">

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <x-ui.typography as="muted">Configurá el envío automático de reportes por email.</x-ui.typography>
                <x-ui.button @click="openCreate()">
                    <x-lucide-plus class="size-4" />
                    Programar reporte
                </x-ui.button>
            </div>

            <x-domain.reportes.tabla-programados :programados="$programados" />
            <x-domain.reportes.modal-programado :config="$config" />
            <x-domain.reportes.modal-enviar />
            <x-domain.reportes.modal-delete />

        </div>
    </x-ui.tabs.content>

    {{-- ── Tab: Historial ── --}}
    <x-ui.tabs.content value="historial" class="mt-0">
        <div x-data="reportesHistorial({ parcialUrl: '{{ route('admin.reportes.historial.parcial') }}' })" class="flex flex-col gap-6">
            <x-ui.typography as="muted">Reportes descargados y enviados. Volvé a abrir cualquiera para regenerarlo con los datos del período.</x-ui.typography>

            <div id="historial-tabla">
                <x-domain.reportes.tabla-historial :historial="$historial" />
            </div>
            <x-domain.reportes.modal-revision />
        </div>
    </x-ui.tabs.content>

    {{-- ── Tab: Configuración ── --}}
    <x-ui.tabs.content value="configuracion" class="mt-0">
        <div x-data="reportesConfiguracion({{ Js::from(['servicios' => $config->servicios ?? [['titulo'=>'','descripcion'=>'']], 'aiEnabled' => $config->ai_enabled ?? false]) }})" class="flex flex-col gap-6">

            <x-domain.reportes.form-configuracion :config="$config" />

        </div>
    </x-ui.tabs.content>

</x-ui.tabs>
</x-layouts.app>
