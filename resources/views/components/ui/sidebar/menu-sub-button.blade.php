@props([
    'active' => false,
    'size'   => 'md',   // sm | md
    'href'   => null,
])

@php
$sizeClass  = $size === 'sm' ? 'text-xs' : 'text-sm';
$activeClass = $active ? 'bg-sidebar-accent text-sidebar-accent-foreground font-medium' : '';
$base = 'flex h-7 min-w-0 -translate-x-px items-center gap-2 overflow-hidden rounded-md px-2 text-sidebar-foreground outline-none ring-sidebar-ring transition-[width,height,padding] focus-visible:ring-2 [&>svg]:size-4 [&>svg]:shrink-0 [&>span]:truncate';
$hoverClass = 'hover:bg-sidebar-accent hover:text-sidebar-accent-foreground';
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->twMerge($base, $sizeClass, $activeClass, $hoverClass) }}>
        {{ $slot }}
    </a>
@else
    <button type="button" {{ $attributes->twMerge($base, $sizeClass, $activeClass, $hoverClass) }}>
        {{ $slot }}
    </button>
@endif
