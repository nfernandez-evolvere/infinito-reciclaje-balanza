@props([
    'as'    => 'div',    // div | li | button | a
    'inset' => false,    // añade pl-8 para alinear con items que tienen ícono
    'href'  => null,
])

@php
$tag   = $href ? 'a' : $as;
$base  = 'flex items-center gap-2 rounded-md px-3 py-2 text-sm transition-colors';
$inset = $inset ? 'pl-8' : '';
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    {{ $attributes->twMerge($base, $inset) }}
>
    {{ $slot }}
</{{ $tag }}>
