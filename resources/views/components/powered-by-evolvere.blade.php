{{--
    Sello "Powered by EVOLVERE" reutilizable.
    Props:
      - href:  enlace al sitio de EVOLVERE (null = solo texto, sin enlace).
      - align: 'center' | 'start' — alineación horizontal.
--}}
@props([
    'href'  => 'https://evolvere.ar',
    'align' => 'center',
])

@php
    $justify = $align === 'start' ? 'justify-start' : 'justify-center';
@endphp

@if ($href)
    <a
        href="{{ $href }}"
        target="_blank"
        rel="noopener noreferrer"
        {{ $attributes->merge(['class' => "group inline-flex items-center gap-1 $justify text-xs transition-colors"]) }}
    >
        <span class="text-muted-foreground transition-colors group-hover:text-foreground/70">Powered by</span>
        <span class="font-bold tracking-wide text-foreground/80 transition-colors group-hover:text-foreground">EVOLVERE</span>
    </a>
@else
    <p {{ $attributes->merge(['class' => "flex items-center gap-1 $justify text-xs"]) }}>
        <span class="text-muted-foreground">Powered by</span>
        <span class="font-bold tracking-wide text-foreground/80">EVOLVERE</span>
    </p>
@endif
