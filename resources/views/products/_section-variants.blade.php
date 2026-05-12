<div class="space-y-6">

    <div class="space-y-2">
        <x-ui.label element="p">Tallas disponibles</x-ui.label>
        <x-ui.toggle-group type="multiple" value="M" variant="outline">
            @foreach(['XS','S','M','L','XL','XXL'] as $size)
            <x-ui.toggle-group.item value="{{ $size }}">{{ $size }}</x-ui.toggle-group.item>
            @endforeach
        </x-ui.toggle-group>
    </div>

    <div class="space-y-2">
        <x-ui.label element="p">Colores disponibles</x-ui.label>
        <x-ui.toggle-group type="multiple" variant="outline" class="flex-wrap">
            @foreach([
                ['value'=>'black',  'label'=>'Negro',   'color'=>'#18181b'],
                ['value'=>'white',  'label'=>'Blanco',  'color'=>'#f4f4f5'],
                ['value'=>'navy',   'label'=>'Azul',    'color'=>'#1e3a5f'],
                ['value'=>'red',    'label'=>'Rojo',    'color'=>'#dc2626'],
                ['value'=>'green',  'label'=>'Verde',   'color'=>'#16a34a'],
                ['value'=>'gray',   'label'=>'Gris',    'color'=>'#71717a'],
            ] as $color)
            <x-ui.toggle-group.item value="{{ $color['value'] }}" class="gap-2">
                <span class="size-3.5 rounded-full border border-border/50 shrink-0" style="background: {{ $color['color'] }}"></span>
                {{ $color['label'] }}
            </x-ui.toggle-group.item>
            @endforeach
        </x-ui.toggle-group>
    </div>

</div>
