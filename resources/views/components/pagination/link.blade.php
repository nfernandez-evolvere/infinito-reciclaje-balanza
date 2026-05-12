@props(['href' => '#', 'active' => false, 'disabled' => false])

<a
    href="{{ $disabled ? '#' : $href }}"
    aria-current="{{ $active ? 'page' : 'false' }}"
    {{ $attributes->merge(['class' => 'inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium h-9 min-w-9 px-3 transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring'
        . ($active   ? ' border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground' : ' hover:bg-accent hover:text-accent-foreground')
        . ($disabled ? ' pointer-events-none opacity-50' : '')
    ]) }}
>
    {{ $slot }}
</a>
