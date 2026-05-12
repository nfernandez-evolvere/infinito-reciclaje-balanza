@props(['variant' => 'default', 'size' => 'default', 'pressed' => false])

@php
    $variants = [
        'default' => 'bg-transparent hover:bg-muted hover:text-muted-foreground data-[state=on]:bg-accent data-[state=on]:text-accent-foreground',
        'outline' => 'border border-input bg-transparent shadow-sm hover:bg-accent hover:text-accent-foreground data-[state=on]:bg-accent data-[state=on]:text-accent-foreground',
    ];
    $sizes = [
        'default' => 'h-9 px-3 min-w-9',
        'sm'      => 'h-8 px-2 min-w-8 text-xs',
        'lg'      => 'h-10 px-3 min-w-10',
    ];
@endphp

<button
    type="button"
    x-data="{ on: {{ $pressed ? 'true' : 'false' }} }"
    @click="on = !on; $el.dataset.state = on ? 'on' : 'off'"
    :data-state="on ? 'on' : 'off'"
    data-state="{{ $pressed ? 'on' : 'off' }}"
    {{ $attributes->merge(['class' => "inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 {$variants[$variant]} {$sizes[$size]}"]) }}
>
    {{ $slot }}
</button>
