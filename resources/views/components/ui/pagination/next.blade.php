@props(['href' => null, 'disabled' => false])

@if($href && !$disabled)
    <a
        href="{{ $href }}"
        aria-label="Página siguiente"
        {{ $attributes->twMerge('inline-flex items-center gap-1 pr-2.5 pl-3 h-9 rounded-md text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 cursor-pointer') }}
    >
        <span>Siguiente</span>
        <x-lucide-chevron-right class="size-4" />
    </a>
@else
    <span
        aria-label="Página siguiente"
        aria-disabled="true"
        {{ $attributes->twMerge('inline-flex items-center gap-1 pr-2.5 pl-3 h-9 rounded-md text-sm font-medium opacity-50 pointer-events-none') }}
    >
        <span>Siguiente</span>
        <x-lucide-chevron-right class="size-4" />
    </span>
@endif
