@php
$integrations = [
    ['name' => 'GitHub',  'desc' => 'Vinculá repositorios', 'icon' => 'github',       'connected' => true],
    ['name' => 'Slack',   'desc' => 'Notificaciones',        'icon' => 'message-circle','connected' => false],
    ['name' => 'Google',  'desc' => 'Calendar y Drive',      'icon' => 'globe',         'connected' => true],
    ['name' => 'Figma',   'desc' => 'Diseño colaborativo',   'icon' => 'layers',        'connected' => false],
    ['name' => 'Notion',  'desc' => 'Documentación',         'icon' => 'file-text',     'connected' => false],
    ['name' => 'Jira',    'desc' => 'Gestión de proyectos',  'icon' => 'kanban',        'connected' => false],
];
@endphp

<div class="space-y-6">

    <div class="space-y-2">
        <x-ui.label>Integraciones recomendadas</x-ui.label>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            @foreach($integrations as $i => $integ)
            <label
                x-data="{ checked: @js($integ['connected']) }"
                class="relative flex flex-col items-center gap-2 rounded-lg border p-4 cursor-pointer transition-colors"
                :class="checked ? 'border-primary bg-primary/5' : 'border-border hover:bg-muted/50'"
                @click="checked = !checked"
            >
                <div class="size-8 rounded-md bg-muted flex items-center justify-center">
                    <x-dynamic-component :component="'lucide-' . $integ['icon']" class="size-4 text-muted-foreground" />
                </div>
                <div class="text-center">
                    <p class="text-xs font-medium text-foreground">{{ $integ['name'] }}</p>
                    <p class="text-[10px] text-muted-foreground">{{ $integ['desc'] }}</p>
                </div>
                <div
                    x-show="checked"
                    class="absolute top-2 right-2 size-4 rounded-full bg-primary flex items-center justify-center"
                >
                    <x-lucide-check class="size-2.5 text-primary-foreground" stroke-width="3" />
                </div>
            </label>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-ui.form-field>
            <x-ui.label>Zona horaria del equipo</x-ui.label>
            <x-ui.select name="tz" value="America/Argentina/Buenos_Aires">
                <x-ui.select.trigger>
                    <x-ui.select.value placeholder="Seleccioná zona horaria" />
                </x-ui.select.trigger>
                <x-ui.select.content>
                    <x-ui.select.item value="America/Argentina/Buenos_Aires">Buenos Aires (GMT-3)</x-ui.select.item>
                    <x-ui.select.item value="America/Santiago">Santiago (GMT-4)</x-ui.select.item>
                    <x-ui.select.item value="America/Mexico_City">México (GMT-6)</x-ui.select.item>
                    <x-ui.select.item value="Europe/Madrid">Madrid (GMT+1)</x-ui.select.item>
                </x-ui.select.content>
            </x-ui.select>
        </x-ui.form-field>

        <div class="space-y-2">
            <x-ui.label>Notificaciones del equipo</x-ui.label>
            <div class="space-y-2 pt-1">
                <x-settings.notification-item label="Resumen diario por email" :checked="true" />
                <x-settings.notification-item label="Alertas en tiempo real" :checked="false" />
            </div>
        </div>
    </div>

</div>
