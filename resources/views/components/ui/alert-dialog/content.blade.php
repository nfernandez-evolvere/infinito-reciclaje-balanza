@props(['size' => 'default'])  {{-- default | sm --}}

@php
$sizeClass = $size === 'sm' ? 'max-w-xs' : 'max-w-xs sm:max-w-sm';
@endphp

<template x-teleport="body">
    <div
        x-show="open"
        x-cloak
        @keydown.escape.window="open = false"
        class="fixed inset-0 z-(--z-modal) flex items-center justify-center p-4"
        role="alertdialog"
        aria-modal="true"
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
            data-size="{{ $size }}"
            {{ $attributes->twMerge(
                'group/alert-dialog-content relative z-10 grid w-full gap-4 rounded-xl bg-popover p-4 text-popover-foreground ring-1 ring-foreground/10 shadow-xl',
                $sizeClass
            ) }}
        >
            {{ $slot }}
        </div>
    </div>
</template>
