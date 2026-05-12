@props(['position' => 'bottom-right'])

@php
$posClass = match($position) {
    'top-left'     => 'top-4 left-4',
    'top-center'   => 'top-4 left-1/2 -translate-x-1/2',
    'top-right'    => 'top-4 right-4',
    'bottom-left'  => 'bottom-4 left-4',
    'bottom-center'=> 'bottom-4 left-1/2 -translate-x-1/2',
    default        => 'bottom-4 right-4',
};
@endphp

<div
    x-data="{
        hovered: false,
        get visible() { return $store.toast.toasts.filter(t => t.visible); },

        toastStyle(index) {
            const toasts = this.visible;
            const total  = toasts.length;
            const depth  = total - 1 - index;  // 0 = más reciente
            const max    = 3;

            if (this.hovered || depth === 0) {
                return { position: 'relative', transform: 'none', opacity: '1', pointerEvents: 'auto' };
            }
            if (depth >= max) {
                return { position: 'absolute', inset: '0 0 0 0', opacity: '0', pointerEvents: 'none',
                         transform: `scale(${1 - depth * 0.04}) translateY(${depth * -8}px)`, zIndex: String(total - depth) };
            }
            return {
                position: 'absolute', bottom: '0', left: '0', right: '0',
                transform: `scale(${1 - depth * 0.04}) translateY(${depth * -8}px)`,
                opacity:   String(1 - depth * 0.15),
                zIndex:    String(total - depth),
                pointerEvents: 'none',
            };
        }
    }"
    @mouseenter="hovered = true"
    @mouseleave="hovered = false"
    class="fixed {{ $posClass }} z-(--z-toast) flex flex-col-reverse gap-2 w-full max-w-[420px] pointer-events-none px-4 sm:px-0"
    aria-live="polite"
    aria-atomic="false"
>
    <template x-for="(toast, i) in visible" :key="toast.id">
        <div
            x-show="toast.visible"
            :style="toastStyle(i)"
            :class="toast.variantClass"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="pointer-events-auto flex w-full items-start gap-3 rounded-lg border p-4 shadow-lg transition-all duration-300"
            role="status"
        >
            <span class="mt-0.5 shrink-0 size-4" aria-hidden="true">
                <x-lucide-check x-show="toast.variant === 'success'"     class="size-4 text-success-foreground"     stroke-width="2.5" />
                <x-lucide-x x-show="toast.variant === 'destructive'" class="size-4 text-destructive-foreground" stroke-width="2.5" />
                <x-lucide-triangle-alert x-show="toast.variant === 'warning'"     class="size-4 text-warning-foreground" />
                <x-lucide-info x-show="toast.variant === 'info'"        class="size-4 text-info-foreground" />
                <svg x-show="toast.variant === 'loading'"
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                    class="size-4 animate-spin text-muted-foreground"
                    fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                </svg>
            </span>

            <div class="flex-1 min-w-0 space-y-1">
                <x-ui.typography as="small" class="font-semibold" x-text="toast.message"></x-ui.typography>
                <x-ui.typography as="muted" x-show="toast.description" x-text="toast.description"></x-ui.typography>
                <button
                    x-show="toast.action"
                    @click="toast.action?.onClick?.(); $store.toast.dismiss(toast.id)"
                    x-text="toast.action?.label"
                    class="text-sm font-medium underline underline-offset-2 hover:no-underline mt-1 block"
                ></button>
            </div>

            <button
                x-show="toast.variant !== 'loading'"
                @click="$store.toast.dismiss(toast.id)"
                class="shrink-0 rounded-md p-0.5 opacity-60 hover:opacity-100 transition-opacity focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                aria-label="Cerrar"
            >
                <x-lucide-x class="size-4" />
            </button>
        </div>
    </template>
</div>
