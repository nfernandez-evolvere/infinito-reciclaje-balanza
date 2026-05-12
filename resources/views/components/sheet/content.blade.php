@props(['side' => 'right'])

@php
    $sides = [
        'right'  => 'inset-y-0 right-0 h-full w-3/4 sm:max-w-sm',
        'left'   => 'inset-y-0 left-0 h-full w-3/4 sm:max-w-sm',
        'top'    => 'inset-x-0 top-0 w-full',
        'bottom' => 'inset-x-0 bottom-0 w-full',
    ];
@endphp

<template x-teleport="body">
    <div
        x-show="open"
        x-cloak
        x-transition.opacity
        @keydown.escape.window="open = false"
        x-effect="
            if (open) {
                previousFocus = document.activeElement;
                $nextTick(() => {
                    const els = [...$el.querySelectorAll('a[href], button, input, select, textarea, [tabindex]')]
                        .filter(el => !el.disabled && el.tabIndex >= 0);
                    if (els.length) els[0].focus();
                });
            } else if (previousFocus) {
                previousFocus.focus();
                previousFocus = null;
            }
        "
        @keydown.tab="
            const els = [...$el.querySelectorAll('a[href], button, input, select, textarea, [tabindex]')]
                .filter(el => !el.disabled && el.tabIndex >= 0);
            if (!els.length) return;
            const first = els[0], last = els[els.length - 1];
            if ($event.shiftKey) {
                if (document.activeElement === first) { $event.preventDefault(); last.focus(); }
            } else {
                if (document.activeElement === last) { $event.preventDefault(); first.focus(); }
            }
        "
        class="fixed inset-0 z-50"
        role="dialog"
        aria-modal="true"
    >
        <div class="fixed inset-0 bg-black/50" @click="open = false"></div>

        <div
            x-show="open"
            x-transition:enter="transition ease-in-out duration-300"
            x-transition:enter-start="{{ $side === 'right' ? 'translate-x-full' : ($side === 'left' ? '-translate-x-full' : ($side === 'top' ? '-translate-y-full' : 'translate-y-full')) }}"
            x-transition:enter-end="translate-x-0 translate-y-0"
            x-transition:leave="transition ease-in-out duration-300"
            x-transition:leave-start="translate-x-0 translate-y-0"
            x-transition:leave-end="{{ $side === 'right' ? 'translate-x-full' : ($side === 'left' ? '-translate-x-full' : ($side === 'top' ? '-translate-y-full' : 'translate-y-full')) }}"
            {{ $attributes->merge(['class' => "fixed z-50 gap-4 bg-background p-6 shadow-lg flex flex-col {$sides[$side]}"]) }}
        >
            <button
                type="button"
                @click="open = false"
                class="absolute right-4 top-4 rounded-sm opacity-70 hover:opacity-100 focus:outline-none focus:ring-1 focus:ring-ring"
                aria-label="Cerrar"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
            {{ $slot }}
        </div>
    </div>
</template>
