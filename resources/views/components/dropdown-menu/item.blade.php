@props(['destructive' => false, 'disabled' => false, 'href' => null])

@php
    $base = 'relative flex w-full cursor-pointer select-none items-center gap-2 rounded-sm px-2 py-1.5 text-sm outline-none transition-colors focus:bg-accent focus:text-accent-foreground hover:bg-accent hover:text-accent-foreground [&>svg]:size-4 [&>svg]:shrink-0';
    $classes = $base
        . ($destructive ? ' text-destructive focus:text-destructive' : '')
        . ($disabled    ? ' pointer-events-none opacity-50' : '');
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="button" {{ $attributes->merge(['class' => 'border-0 bg-transparent text-left ' . $classes]) }}>{{ $slot }}</button>
@endif
