@php
$modules = [
    'Proyectos' => [
        ['label' => 'Ver proyectos',       'checked' => true],
        ['label' => 'Crear proyectos',     'checked' => true],
        ['label' => 'Editar proyectos',    'checked' => true],
        ['label' => 'Eliminar proyectos',  'checked' => false],
    ],
    'Facturación' => [
        ['label' => 'Ver facturas',        'checked' => true],
        ['label' => 'Descargar facturas',  'checked' => true],
        ['label' => 'Gestionar métodos',   'checked' => false],
        ['label' => 'Cambiar plan',        'checked' => false],
    ],
    'Equipo' => [
        ['label' => 'Ver miembros',        'checked' => true],
        ['label' => 'Invitar miembros',    'checked' => true],
        ['label' => 'Cambiar roles',       'checked' => false],
        ['label' => 'Eliminar miembros',   'checked' => false],
    ],
    'Configuración' => [
        ['label' => 'Ver configuración',   'checked' => true],
        ['label' => 'Editar configuración','checked' => false],
        ['label' => 'Gestionar API keys',  'checked' => false],
    ],
];
@endphp

<div class="pt-4 space-y-4">
    <x-ui.accordion type="multiple" :collapsible="true" value="Proyectos">
        @foreach($modules as $moduleName => $perms)
        <x-ui.accordion.item value="{{ $moduleName }}">
            <x-ui.accordion.trigger>{{ $moduleName }}</x-ui.accordion.trigger>
            <x-ui.accordion.content>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-2 pb-2">
                    @foreach($perms as $perm)
                    <label class="flex items-center gap-3 cursor-pointer py-1">
                        <x-ui.checkbox :checked="$perm['checked']" class="shrink-0" />
                        <span class="text-sm text-foreground">{{ $perm['label'] }}</span>
                    </label>
                    @endforeach
                </div>
            </x-ui.accordion.content>
        </x-ui.accordion.item>
        @endforeach
    </x-ui.accordion>

    <div class="flex justify-end pt-2">
        <x-ui.button @click="$store.toast.success('Permisos actualizados')">
            Guardar permisos
        </x-ui.button>
    </div>
</div>
