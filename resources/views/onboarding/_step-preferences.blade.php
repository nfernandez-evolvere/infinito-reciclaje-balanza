@php
$roles = [
    ['value' => 'admin',    'label' => 'Administrador', 'desc' => 'Control total',    'icon' => 'shield-check'],
    ['value' => 'editor',   'label' => 'Editor',        'desc' => 'Gestiona contenido','icon' => 'pencil'],
    ['value' => 'member',   'label' => 'Miembro',       'desc' => 'Solo lectura',     'icon' => 'user'],
];
$sizes = ['Solo', '2-10', '11-50', '51-200', '200+'];
@endphp

<div class="space-y-6">

    <div class="space-y-2">
        <x-ui.label>Tu rol en la organización</x-ui.label>
        <x-ui.radio-group value="editor" name="role" class="grid grid-cols-3 gap-3">
            @foreach($roles as $role)
            <label
                class="flex flex-col items-center gap-2 rounded-lg border p-4 cursor-pointer transition-colors"
                :class="value === @js($role['value'])
                    ? 'border-primary bg-primary/5 text-primary'
                    : 'border-border hover:bg-muted/50'"
                @click="value = @js($role['value'])"
            >
                <x-dynamic-component :component="'lucide-' . $role['icon']" class="size-6" />
                <div class="text-center">
                    <p class="text-sm font-medium text-foreground">{{ $role['label'] }}</p>
                    <p class="text-xs text-muted-foreground">{{ $role['desc'] }}</p>
                </div>
            </label>
            @endforeach
        </x-ui.radio-group>
    </div>

    <div class="space-y-2">
        <x-ui.label>Tamaño de tu empresa</x-ui.label>
        <x-ui.toggle-group value="11-50" variant="outline" class="flex-wrap">
            @foreach($sizes as $size)
            <x-ui.toggle-group.item value="{{ $size }}">{{ $size }}</x-ui.toggle-group.item>
            @endforeach
        </x-ui.toggle-group>
    </div>

</div>
