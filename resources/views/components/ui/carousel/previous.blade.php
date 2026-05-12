<button
    type="button"
    @click="prev()"
    :disabled="!canPrev"
    aria-label="Slide anterior"
    :class="orientation === 'horizontal'
        ? 'top-1/2 -translate-y-1/2 -left-12'
        : 'left-1/2 -translate-x-1/2 -top-12'"
    {{ $attributes->twMerge(
        'absolute z-10 size-9 flex items-center justify-center rounded-full border bg-background shadow-sm transition-colors',
        'hover:bg-accent hover:text-accent-foreground',
        'disabled:pointer-events-none disabled:opacity-50'
    ) }}
>
    <x-lucide-chevron-left class="size-4" x-show="orientation === 'horizontal'" />
    <x-lucide-chevron-up class="size-4" x-show="orientation === 'vertical'" />
</button>
