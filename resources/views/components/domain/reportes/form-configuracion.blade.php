@props(['config'])

{{--
    Configuración de reportes organizada en sub-secciones. El rail vertical de la
    izquierda (tablet/desktop) se reemplaza por un selector desplegable en mobile;
    ambos comparten el estado `active` del <x-ui.tabs>. Es un único <form>: los
    paneles usan x-show (no se desmontan), así todos los campos se envían aunque el
    panel no esté visible, y "Guardar" — fijo abajo, fuera de los tabs — persiste
    todo sin importar en qué sub-sección estés.
--}}
@php
    // Cada grupo de campos mapea a su sub-sección: si la validación falla en un
    // panel oculto, se abre ese tab al recargar y se marca con un punto.
    $tabsConError = [
        'identidad' => $errors->hasAny(['municipalidad_nombre', 'intro_empresa', 'servicios', 'servicios.*.titulo', 'servicios.*.descripcion']),
        'secciones' => $errors->hasAny(['secciones', 'secciones.pdf', 'secciones.pdf.*', 'secciones.excel', 'secciones.excel.*']),
        'ia'        => $errors->hasAny(['ai_enabled', 'ai_modelo', 'ai_api_key', 'ai_prompt', 'ai_proveedor']),
        'envios'    => $errors->hasAny(['tipo_informe_mensual_activo', 'tipo_alertas_activo', 'revision_requerida']),
    ];
    $tabInicial = collect($tabsConError)->filter()->keys()->first() ?? 'identidad';

    $tabs = [
        ['value' => 'identidad', 'label' => 'Identidad',               'icon' => 'landmark'],
        ['value' => 'secciones', 'label' => 'Secciones',              'icon' => 'layout-list'],
        ['value' => 'ia',        'label' => 'Inteligencia artificial', 'icon' => 'sparkles'],
        ['value' => 'envios',    'label' => 'Envíos',                  'icon' => 'send'],
    ];
@endphp

<form method="POST" action="{{ route('admin.reportes.configuracion.update') }}">
    @csrf
    @method('PUT')

    <x-ui.tabs :value="$tabInicial" orientation="vertical" class="flex flex-col gap-6">

        {{-- Navegador mobile: el rail se colapsa a un selector (< md) --}}
        <div class="md:hidden">
            <x-ui.label class="mb-1.5 block">Sección</x-ui.label>
            <x-ui.select x-modelable="value" x-model="active">
                <x-ui.select.trigger>
                    <x-ui.select.value placeholder="Seleccionar sección" />
                </x-ui.select.trigger>
                <x-ui.select.content>
                    @foreach($tabs as $t)
                        <x-ui.select.item value="{{ $t['value'] }}">{{ $t['label'] }}</x-ui.select.item>
                    @endforeach
                </x-ui.select.content>
            </x-ui.select>
        </div>

        <div class="flex flex-col gap-6 md:flex-row md:gap-8">
            {{-- Rail vertical (tablet/desktop) --}}
            <x-ui.tabs.list class="hidden md:flex w-56 shrink-0 md:sticky md:top-6 md:self-start">
                @foreach($tabs as $t)
                    <x-ui.tabs.trigger value="{{ $t['value'] }}" class="w-full justify-start gap-2.5">
                        <x-dynamic-component :component="'lucide-'.$t['icon']" class="size-4 shrink-0" />
                        <span class="truncate">{{ $t['label'] }}</span>
                        @if($tabsConError[$t['value']])
                            <span class="ml-auto size-1.5 shrink-0 rounded-full bg-destructive" title="Hay un campo con error en esta sección"></span>
                        @endif
                    </x-ui.tabs.trigger>
                @endforeach
            </x-ui.tabs.list>

            {{-- Paneles: comparten el x-data reportesConfiguracion del ancestro --}}
            <div class="flex-1 min-w-0">
                <x-ui.tabs.content value="identidad" class="mt-0">
                    <x-domain.reportes.configuracion.identidad :config="$config" />
                </x-ui.tabs.content>
                <x-ui.tabs.content value="secciones" class="mt-0">
                    <x-domain.reportes.configuracion.secciones :config="$config" />
                </x-ui.tabs.content>
                <x-ui.tabs.content value="ia" class="mt-0">
                    <x-domain.reportes.configuracion.ia :config="$config" />
                </x-ui.tabs.content>
                <x-ui.tabs.content value="envios" class="mt-0">
                    <x-domain.reportes.configuracion.envios :config="$config" />
                </x-ui.tabs.content>
            </div>
        </div>
    </x-ui.tabs>

    {{-- Barra de acción fija, fuera de los tabs: guarda toda la configuración --}}
    <div class="mt-8 flex justify-end border-t border-border pt-6">
        <x-ui.button type="submit">
            <x-lucide-save class="size-4" />
            Guardar configuración
        </x-ui.button>
    </div>
</form>
