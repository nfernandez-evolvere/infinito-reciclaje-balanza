@props([
    'href'  => null,   // url que quita el filtro (chip removible); si es null, chip estático
    'label' => null,   // texto del chip (o usar el slot)
])

{{--
    Chip de filtro activo, con color primario (tratamiento «primary-subtle»).
    Con `href` es removible: navega a la url que quita ese filtro y muestra la ✕.
--}}

@php
    $base = 'inline-flex items-center gap-1 rounded-full border border-primary/20 bg-primary/10 py-1 text-xs font-medium text-primary transition-colors';
    $pad  = $href ? 'pl-2.5 pr-1.5' : 'px-2.5';
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->twMerge("$base $pad hover:bg-primary/20") }}>
        <span class="max-w-40 truncate">{{ $label ?? $slot }}</span>
        <x-lucide-x class="size-3 shrink-0" />
    </a>
@else
    <span {{ $attributes->twMerge("$base $pad") }}>
        <span class="max-w-40 truncate">{{ $label ?? $slot }}</span>
    </span>
@endif
