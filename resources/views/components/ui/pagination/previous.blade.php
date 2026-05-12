@props(['href' => null, 'disabled' => false])

@if($href && !$disabled)
    <a
        href="{{ $href }}"
        aria-label="Página anterior"
        {{ $attributes->twMerge('inline-flex items-center gap-1 pl-2.5 pr-3 h-9 rounded-md text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 cursor-pointer') }}
    >
        <x-lucide-chevron-left class="size-4" />
        <span>Anterior</span>
    </a>
@else
    <span
        aria-label="Página anterior"
        aria-disabled="true"
        {{ $attributes->twMerge('inline-flex items-center gap-1 pl-2.5 pr-3 h-9 rounded-md text-sm font-medium opacity-50 pointer-events-none') }}
    >
        <x-lucide-chevron-left class="size-4" />
        <span>Anterior</span>
    </span>
@endif
