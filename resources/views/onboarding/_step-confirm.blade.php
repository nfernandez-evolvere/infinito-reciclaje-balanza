<div class="space-y-4">
    <x-ui.alert state="success">
        <x-lucide-circle-check class="size-4" />
        <x-ui.alert.title>¡Todo listo para comenzar!</x-ui.alert.title>
        <x-ui.alert.description>Revisá tu configuración antes de finalizar.</x-ui.alert.description>
    </x-ui.alert>

    @php
    $summary = [
        ['label' => 'Nombre',        'value' => 'Juan García',                    'icon' => 'user'],
        ['label' => 'Email',         'value' => 'juan@empresa.com',               'icon' => 'mail'],
        ['label' => 'Rol',           'value' => 'Editor',                         'icon' => 'pencil'],
        ['label' => 'Empresa',       'value' => '11-50 personas',                 'icon' => 'building-2'],
        ['label' => 'Zona horaria',  'value' => 'Buenos Aires (GMT-3)',            'icon' => 'clock'],
        ['label' => 'Integraciones', 'value' => 'GitHub, Google',                 'icon' => 'plug'],
    ];
    @endphp

    <x-ui.card>
        <x-ui.card.content class="p-0">
            @foreach($summary as $item)
            <div class="flex items-center gap-3 px-6 py-3 border-b border-border last:border-0">
                <div class="size-7 rounded-md bg-muted flex items-center justify-center shrink-0">
                    <x-dynamic-component :component="'lucide-' . $item['icon']" class="size-3.5 text-muted-foreground" />
                </div>
                <x-ui.typography as="muted" class="text-sm w-32 shrink-0">{{ $item['label'] }}</x-ui.typography>
                <span class="text-sm font-medium text-foreground">{{ $item['value'] }}</span>
            </div>
            @endforeach
        </x-ui.card.content>
    </x-ui.card>
</div>
