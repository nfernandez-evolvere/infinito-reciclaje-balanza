<div
    x-data
    class="flex items-center gap-1"
    role="group"
    aria-label="Tema de color"
>
    @foreach([
        ['default',  'Zinc',     'oklch(0.145 0 0)'],
        ['sapphire', 'Sapphire', 'oklch(0.546 0.197 250)'],
        ['emerald',  'Emerald',  'oklch(0.519 0.181 145)'],
        ['rose',     'Rose',     'oklch(0.568 0.268 27)'],
        ['violet',   'Violet',   'oklch(0.553 0.252 300)'],
    ] as [$name, $label, $color])
        <button
            type="button"
            @click="$store.brandTheme.apply('{{ $name }}')"
            title="{{ $label }}"
            class="relative size-5 rounded-full ring-offset-2 ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring hover:scale-110"
            :class="$store.brandTheme.current === '{{ $name }}'
                ? 'ring-2 ring-foreground/40 scale-110'
                : ''"
            style="background-color: {{ $color }}"
            :aria-pressed="$store.brandTheme.current === '{{ $name }}'"
        >
            <span
                x-show="$store.brandTheme.current === '{{ $name }}'"
                class="absolute inset-0 flex items-center justify-center"
            >
                <svg class="size-2.5 text-white drop-shadow-sm" viewBox="0 0 12 12" fill="none">
                    <path d="M2 6l3 3 5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <span class="sr-only">{{ $label }}</span>
        </button>
    @endforeach
</div>
