@props([
    'href'    => null,
    'active'  => false,
    'size'    => 'icon',   // icon | default
    'disabled' => false,
])

@php
$base = 'inline-flex items-center justify-center gap-1 whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2';
$sizeClass = $size === 'icon' ? 'size-9' : 'h-9 px-4';
$activeClass = $active
    ? 'border border-input bg-background shadow-sm text-foreground'
    : 'hover:bg-accent hover:text-accent-foreground text-foreground';
$disabledClass = $disabled ? 'pointer-events-none opacity-50' : 'cursor-pointer';
@endphp

@if($href && !$disabled)
    <a href="{{ $href }}" aria-current="{{ $active ? 'page' : 'false' }}" {{ $attributes->twMerge($base, $sizeClass, $activeClass, $disabledClass) }}>
        {{ $slot }}
    </a>
@else
    <span aria-disabled="true" aria-current="{{ $active ? 'page' : 'false' }}" {{ $attributes->twMerge($base, $sizeClass, $activeClass, $disabledClass) }}>
        {{ $slot }}
    </span>
@endif
