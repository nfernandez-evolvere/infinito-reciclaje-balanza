@props([
    'size'            => 'md',
    'showCloseButton' => true,
    'closeState'      => null,
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
            @click="open = false"
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
                <x-ui.button
                    variant="ghost"
                    :state="$closeState"
                    size="icon"
                    type="button"
                    class="absolute right-6 top-4 z-10 size-7"
                    aria-label="Cerrar"
                    @click="open = false"
                >
                    <x-lucide-x class="size-4" />
                </x-ui.button>
            @endif

            {{ $slot }}
        </div>
    </div>
</template>
