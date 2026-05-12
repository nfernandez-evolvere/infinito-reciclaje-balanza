@props([])

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
        :aria-labelledby="dialogId"
    >
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-black/50" @click="open = false"></div>

        {{-- Panel --}}
        <div class="fixed left-1/2 top-1/2 z-50 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg">
            <div
                x-show="open"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                {{ $attributes->merge(['class' => 'relative grid w-full gap-4 bg-background p-6 shadow-lg rounded-lg border mx-4']) }}
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
    </div>
</template>
