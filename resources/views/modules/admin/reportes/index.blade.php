@php
    $hasProgramadoErrors = $errors->hasAny(['nombre', 'tipo', 'frecuencia', 'cron_expresion', 'destinatarios', 'activo']);
    $isEditing      = old('_mode') === 'edit';
    $initialProgramado = $hasProgramadoErrors ? [
        'modalOpen' => true,
        'modalMode' => $isEditing ? 'edit' : 'create',
        'form' => [
            'id'             => (int) old('_editing_id', 0) ?: null,
            'nombre'         => old('nombre', ''),
            'tipo'           => old('tipo', 'informe_mensual'),
            'frecuencia'     => old('frecuencia', 'mensual'),
            'cron_expresion' => old('cron_expresion', '0 8 1 * *'),
            'activo'         => old('activo', true),
        ],
        '_oldDestinatariosStr' => old('destinatarios', ''),
    ] : [];
@endphp

<x-layouts.app title="Reportes">
<x-ui.tabs
    :value="$hasProgramadoErrors ? 'programados' : $tab"
    class="flex flex-col gap-6"
    x-init="$watch('active', val => {
        const url = new URL(window.location);
        url.searchParams.set('tab', val);
        history.pushState({}, '', url);
    })"
>

    <div class="flex flex-col items-start gap-2">
        <x-ui.typography as="h2">Reportes</x-ui.typography>
        <x-ui.typography as="muted">Generá y programá reportes para exportar o enviar al municipio.</x-ui.typography>
    </div>

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
                <section class="flex flex-col gap-4">
                    <p class="text-overline">Resumen del período</p>
                    <x-domain.reportes.kpis :kpis="$reporte['kpis']" />
                </section>

                @if(!empty($reporte['evolucion']['datos']))
                    <x-domain.reportes.evolucion :evolucion="$reporte['evolucion']" />
                @endif

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
            <x-domain.reportes.modal-programado />
            <x-domain.reportes.modal-enviar />
            <x-domain.reportes.modal-delete />

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
