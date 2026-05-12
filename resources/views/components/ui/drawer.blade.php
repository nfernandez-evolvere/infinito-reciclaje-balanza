@props([
    'size' => 'md',   // sm (40vh) | md (60vh) | lg (85vh) | full | auto
])

@php
$heightClass = match($size) {
    'sm'   => 'max-h-[40vh]',
    'lg'   => 'max-h-[85vh]',
    'full' => 'h-[100dvh] rounded-t-none',
    'auto' => 'max-h-[95vh]',
    default => 'max-h-[60vh]',
};
@endphp

<div x-data="{ open: false }" {{ $attributes }}>

    @isset($trigger)
        <div @click="open = true" class="inline-flex">{{ $trigger }}</div>
    @endisset

    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            @keydown.escape.window="open = false"
            class="fixed inset-0 z-(--z-modal) flex flex-col justify-end"
        >
            {{-- Overlay --}}
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="open = false"
                class="absolute inset-0 bg-surface-overlay"
            ></div>

            {{-- Panel --}}
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-full"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-full"
                class="relative z-10 flex flex-col w-full rounded-t-2xl border-t border-border bg-background shadow-xl {{ $heightClass }}"
            >
                {{-- Handle --}}
                <button
                    type="button"
                    @click="open = false"
                    aria-label="Cerrar"
                    class="flex w-full justify-center py-3 shrink-0 focus-visible:outline-none"
                >
                    <div class="h-1.5 w-12 rounded-full bg-muted-foreground/30 hover:bg-muted-foreground/60 transition-colors"></div>
                </button>

                {{ $slot }}
            </div>
        </div>
    </template>
</div>
