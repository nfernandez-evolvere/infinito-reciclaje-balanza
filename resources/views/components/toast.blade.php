{{--
    Toast system — add <x-toast /> once in your layout.
    Trigger from anywhere with:
        $dispatch('toast', { message: 'Text', variant: 'default', duration: 4000 })
--}}

<div
    x-data="{
        toasts: [],
        add(message, variant = 'default', duration = 4000) {
            const id = Date.now();
            this.toasts.push({ id, message, variant });
            setTimeout(() => this.remove(id), duration);
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }"
    @toast.window="add($event.detail.message, $event.detail.variant ?? 'default', $event.detail.duration ?? 4000)"
    class="fixed bottom-4 right-4 z-[100] flex flex-col gap-2 w-full max-w-sm pointer-events-none"
    aria-live="polite"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-end="opacity-0 translate-y-2"
            :class="{
                'bg-background border text-foreground': toast.variant === 'default',
                'bg-destructive text-destructive-foreground border-destructive': toast.variant === 'destructive',
                'bg-green-600 text-white border-green-700': toast.variant === 'success',
            }"
            class="pointer-events-auto group relative flex w-full items-center justify-between gap-4 overflow-hidden rounded-lg border p-4 shadow-lg"
            role="alert"
        >
            <p class="text-sm font-medium" x-text="toast.message"></p>
            <button
                type="button"
                @click="remove(toast.id)"
                class="shrink-0 rounded-sm opacity-60 hover:opacity-100 focus:outline-none"
                aria-label="Cerrar"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
    </template>
</div>
