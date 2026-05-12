@props([
    'variant'      => 'default',   // default | destructive
    'inset'        => false,
    'disabled'     => false,
    'href'         => null,
    'closeOnClick' => true,
])

@php
$base = 'relative flex items-center gap-1.5 rounded-md px-2 py-1.5 text-sm outline-none select-none transition-colors cursor-default [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*=size-])]:size-4';

$variantClass = $variant === 'destructive'
    ? 'text-destructive data-[highlighted]:bg-destructive/10 data-[highlighted]:text-destructive [&_svg]:text-destructive focus:bg-destructive/10 focus:text-destructive hover:bg-destructive/10 hover:text-destructive'
    : 'hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground data-[highlighted]:bg-accent data-[highlighted]:text-accent-foreground';

$stateClass  = $disabled ? 'pointer-events-none opacity-50' : 'cursor-pointer';
$insetClass  = $inset ? 'pl-8' : '';
@endphp

@if($href)
    <a
        href="{{ $href }}"
        role="menuitem"
        tabindex="-1"
        @if($disabled) aria-disabled="true" data-disabled @endif
        @if($closeOnClick) @click="open = false" @endif
        @focus="$el.setAttribute('data-highlighted', '')"
        @blur="$el.removeAttribute('data-highlighted')"
        {{ $attributes->twMerge($base, $variantClass, $stateClass, $insetClass) }}
    >
        {{ $slot }}
    </a>
@else
    <div
        role="menuitem"
        tabindex="-1"
        @if($disabled) aria-disabled="true" data-disabled @endif
        @if(!$disabled && $closeOnClick) @click="open = false" @endif
        @if(!$disabled) @keydown.enter.prevent="$el.click()" @endif
        @focus="$el.setAttribute('data-highlighted', '')"
        @blur="$el.removeAttribute('data-highlighted')"
        {{ $attributes->twMerge($base, $variantClass, $stateClass, $insetClass) }}
    >
        {{ $slot }}
    </div>
@endif
