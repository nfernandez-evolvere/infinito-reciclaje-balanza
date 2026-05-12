<button
    type="button"
    @click="next()"
    :disabled="!canNext"
    aria-label="Siguiente slide"
    :class="orientation === 'horizontal'
        ? 'top-1/2 -translate-y-1/2 -right-12'
        : 'left-1/2 -translate-x-1/2 -bottom-12'"
    {{ $attributes->twMerge(
        'absolute z-10 size-9 flex items-center justify-center rounded-full border bg-background shadow-sm transition-colors',
        'hover:bg-accent hover:text-accent-foreground',
        'disabled:pointer-events-none disabled:opacity-50'
    ) }}
>
    <x-lucide-chevron-right class="size-4" x-show="orientation === 'horizontal'" />
    <x-lucide-chevron-down class="size-4" x-show="orientation === 'vertical'" />
</button>
