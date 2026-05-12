@props(['align' => 'start', 'side' => 'bottom'])

@php
    $aligns = [
        'start'  => 'left-0',
        'end'    => 'right-0',
        'center' => 'left-1/2 -translate-x-1/2',
    ];
    $sides = [
        'bottom' => 'top-full mt-1',
        'top'    => 'bottom-full mb-1',
    ];
@endphp

<div
    x-show="open"
    x-cloak
    x-transition:enter="transition ease-out duration-100"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-75"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    {{ $attributes->merge(['class' => "absolute z-50 min-w-[8rem] overflow-hidden rounded-md border bg-popover p-1 text-popover-foreground shadow-md {$aligns[$align]} {$sides[$side]}"]) }}
>
    {{ $slot }}
</div>
