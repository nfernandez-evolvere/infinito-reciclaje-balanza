@props([
    'size'            => 'md',
    'showCloseButton' => true,
    'closeState'      => null,
    'closeOnBackdrop' => false,
])

@php
$sizeClass = ['sm' => 'max-w-sm', 'lg' => 'max-w-2xl', 'xl' => 'max-w-4xl'][$size] ?? 'max-w-lg';
@endphp

<template x-teleport="body">
    <div
        x-show="open"
        @keydown.escape.window="open = false"
        class="fixed inset-0 z-(--z-modal) flex items-center justify-center p-4"
        x-cloak
    >
        {{-- Overlay --}}
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @if($closeOnBackdrop) @click="open = false" @endif
            class="absolute inset-0 bg-surface-overlay"
        ></div>

        {{-- Panel --}}
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            role="dialog"
            aria-modal="true"
            class="relative z-10 flex flex-col w-full {{ $sizeClass }} max-h-[90vh] rounded-xl border border-border bg-background shadow-xl"
        >
            @if($showCloseButton)
                @php
                $btnBase = 'absolute right-6 top-4 z-10 size-7 inline-flex items-center justify-center rounded-full transition-colors cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4';
                $btnColor = $closeState ? '' : 'text-muted-foreground hover:bg-accent focus-visible:ring-ring';
                @endphp
                <button
                    type="button"
                    class="{{ $btnBase }} {{ $btnColor }}"
                    @if($closeState)
                    x-bind:class="
                        ({{ $closeState }}) === 'destructive' ? 'text-destructive hover:bg-destructive/10 focus-visible:ring-destructive' :
                        ({{ $closeState }}) === 'success'     ? 'text-success hover:bg-success/10 focus-visible:ring-success' :
                        'text-muted-foreground hover:bg-accent focus-visible:ring-ring'
                    "
                    @endif
                    @click="open = false"
                    aria-label="Cerrar"
                >
                    <x-lucide-x class="size-4" />
                </button>
            @endif

            {{ $slot }}
        </div>
    </div>
</template>
