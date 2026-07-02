@php
    $hasErrors = $errors->any();
    $errorForm = old('_form'); // 'zona' cuando el error vino del formulario de zona
    $isEditing = old('_mode') === 'edit';

    // Guía de polígonos para el editor de mapa: todas las zonas con geometría en la página.
    // Se incluye tipo_servicio_id para poder mostrar en el mapa solo las zonas del servicio en edición.
    $zonasGuia = $tipos
        ->flatMap(fn ($tipo) => $tipo->zonas)
        ->filter(fn ($z) => $z->geojson !== null && $z->geojson !== '')
        ->map(fn ($z) => [
            'id'               => $z->id,
            'tipo_servicio_id' => $z->tipo_servicio_id,
            'nombre'           => $z->nombre,
            'geojson'          => $z->geojson,
        ])
        ->values();

    // Reabrir el formulario de servicio ante errores de validación de servicio.
    $servicioInitial = ($hasErrors && $errorForm !== 'zona') ? [
        'modalOpen' => true,
        'modalMode' => $isEditing ? 'edit' : 'create',
        'form'      => [
            'id'                => (int) old('_editing_id', 0) ?: null,
            'nombre'            => old('nombre', ''),
            'tipo_vehiculo_ids' => array_map('intval', (array) old('tipo_vehiculo_ids', [])),
        ],
    ] : [];

    // Reabrir el formulario de zona ante errores de validación de zona.
    $zonaInitial = [];
    if ($hasErrors && $errorForm === 'zona') {
        $horariosPorDia = array_fill(0, 7, []);
        foreach ((array) old('horarios', []) as $diaIdx => $franjas) {
            $horariosPorDia[(int) $diaIdx] = array_values(array_map(
                fn ($f) => ['inicio' => $f['inicio'] ?? '', 'fin' => $f['fin'] ?? ''],
                (array) $franjas
            ));
        }
        $servicioNombre = optional($tipos->firstWhere('id', (int) old('tipo_servicio_id')))->nombre ?? '';

        $zonaInitial = [
            'zonaModalOpen'          => true,
            'zonaModalMode'          => $isEditing ? 'edit' : 'create',
            'selectedServicioNombre' => $servicioNombre,
            'zonaForm'               => [
                'id'               => (int) old('_editing_id', 0) ?: null,
                'tipo_servicio_id' => (int) old('tipo_servicio_id') ?: null,
                'nombre'           => old('nombre', ''),
                'hectareas'        => old('hectareas', ''),
                'barrios'          => old('barrios', ''),
                'habitantes'       => old('habitantes', ''),
                'geojson'          => old('geojson', ''),
                'centro_lat'       => old('centro_lat', ''),
                'centro_lng'       => old('centro_lng', ''),
                'turnosEnabled'    => ! empty(old('turnos', [])),
                'turnos'           => array_values((array) old('turnos', [])),
                'horariosPorDia'   => $horariosPorDia,
            ],
        ];
    }

    $initial = array_merge(['zonasGuia' => $zonasGuia], $servicioInitial, $zonaInitial);

    $hayFiltros    = collect($filters)->filter(fn($v) => $v !== null && $v !== '')->isNotEmpty();
    $activeFilters = count(array_filter($filters, fn($v) => $v !== null && $v !== ''));
@endphp

<x-layouts.app title="Servicios">
<div x-data="tiposServicio({{ Js::from($initial) }})" class="flex flex-col gap-6">

    <div class="flex items-start justify-between gap-2">
        <div class="space-y-1">
            <x-ui.typography as="h2">Servicios</x-ui.typography>
            <x-ui.typography as="muted">Servicios de recolección y las zonas de operación de cada uno.</x-ui.typography>
        </div>
        <x-domain.tipos-servicio.mobile-drawers
            :filters="$filters"
            :tiposVehiculo="$tiposVehiculo"
            :hayFiltros="$hayFiltros"
            :activeFilters="$activeFilters"
        />
    </div>

    <x-domain.tipos-servicio.servicios :tipos="$tipos" :activeFilters="$activeFilters" />

    <x-domain.tipos-servicio.drawer-filtros :filters="$filters" :tiposVehiculo="$tiposVehiculo" />
    <x-domain.tipos-servicio.modal :tiposVehiculo="$tiposVehiculo" />
    <x-domain.tipos-servicio.modal-confirm />
    <x-domain.tipos-servicio.modal-delete />

    {{-- Zonas, gestionadas dentro de cada servicio --}}
    <x-domain.tipos-servicio.modal-zona />
    <x-domain.tipos-servicio.modal-zona-confirm />
    <x-domain.tipos-servicio.modal-zona-delete />

</div>
</x-layouts.app>
