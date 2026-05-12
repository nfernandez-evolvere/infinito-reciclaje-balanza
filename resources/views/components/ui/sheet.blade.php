@props([
    'side' => 'right',  // top | right | bottom | left
])

@php
$panelClass = match($side) {
    'top'    => 'inset-x-0 top-0 max-h-[80vh] rounded-b-xl border-b flex-col',
    'bottom' => 'inset-x-0 bottom-0 max-h-[80vh] rounded-t-xl border-t flex-col',
    'left'   => 'inset-y-0 left-0 w-80 rounded-r-xl border-r flex-col',
    default  => 'inset-y-0 right-0 w-80 rounded-l-xl border-l flex-col',
};

$enterStart = match($side) {
    'top'    => 'opacity-0 -translate-y-4',
    'bottom' => 'opacity-0 translate-y-4',
    'left'   => 'opacity-0 -translate-x-4',
    default  => 'opacity-0 translate-x-4',
};
@endphp

<div x-data="{ open: false }">

    @isset($trigger)
        <div @click="open = true" class="inline-flex">{{ $trigger }}</div>
    @endisset

    <template x-teleport="body">
        <div
            x-show="open"
            @keydown.escape.window="open = false"
            class="fixed inset-0 z-(--z-modal)"
            x-cloak
        >
            {{-- Overlay --}}
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
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
                x-transition:enter-start="{{ $enterStart }}"
                x-transition:enter-end="opacity-100 translate-x-0 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-x-0 translate-y-0"
                x-transition:leave-end="{{ $enterStart }}"
                class="absolute flex overflow-y-auto {{ $panelClass }} bg-background border-border shadow-xl"
            >
                {{-- Botón cerrar --}}
                <button
                    @click="open = false"
                    class="absolute right-4 top-4 flex size-7 items-center justify-center rounded-md text-muted-foreground hover:text-foreground hover:bg-accent transition-colors z-10"
                    aria-label="Cerrar"
                >
                    <x-lucide-x class="size-4" />
                </button>

                {{ $slot }}
            </div>
        </div>
    </template>
</div>
