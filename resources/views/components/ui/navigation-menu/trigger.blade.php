<button
    type="button"
    x-ref="trigger"
    :aria-expanded="open.toString()"
    aria-haspopup="true"
    :data-state="open ? 'open' : 'closed'"
    @click="toggle()"
    {{ $attributes->twMerge(
        'group inline-flex h-9 w-max items-center justify-center gap-1 rounded-md px-4 py-2 text-sm font-medium transition-colors',
        'bg-background hover:bg-accent hover:text-accent-foreground',
        'focus:bg-accent focus:text-accent-foreground focus:outline-none',
        'data-[state=open]:bg-accent/50',
        'disabled:pointer-events-none disabled:opacity-50'
    ) }}
>
    {{ $slot }}
    <x-lucide-chevron-down class="size-3 text-muted-foreground transition-transform duration-200 group-data-[state=open]:rotate-180" />
</button>
