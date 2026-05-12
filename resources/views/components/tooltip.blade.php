@props(['text', 'side' => 'top'])

@php
    $positions = [
        'top'    => 'bottom-full left-1/2 -translate-x-1/2 mb-2',
        'bottom' => 'top-full left-1/2 -translate-x-1/2 mt-2',
        'left'   => 'right-full top-1/2 -translate-y-1/2 mr-2',
        'right'  => 'left-full top-1/2 -translate-y-1/2 ml-2',
    ];
@endphp

<div x-data="{ show: false }" class="relative inline-flex" {{ $attributes }}>
    <div @mouseenter="show = true" @mouseleave="show = false" @focus="show = true" @blur="show = false">
        {{ $slot }}
    </div>
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 {{ $positions[$side] }} whitespace-nowrap rounded-md bg-primary px-3 py-1.5 text-xs text-primary-foreground shadow pointer-events-none"
        role="tooltip"
    >
        {{ $text }}
    </div>
</div>
