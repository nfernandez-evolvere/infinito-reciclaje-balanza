@props(['position' => 'bottom-right'])

@php
$positionClass = match($position) {
    'top-left'     => 'top-4 left-4',
    'top-right'    => 'top-4 right-4',
    'top-center'   => 'top-4 left-1/2 -translate-x-1/2',
    'bottom-left'  => 'bottom-4 left-4',
    'bottom-center'=> 'bottom-4 left-1/2 -translate-x-1/2',
    default        => 'bottom-4 right-4',
};
@endphp

{{--
    Toaster: colocar una sola vez en el layout, antes de </body>.
    Los toasts se crean via Alpine store:
        $store.toast.add('Mensaje')
        $store.toast.success('Guardado')
        $store.toast.error('Error', { description: 'Detalle...' })
        $store.toast.warning('Atención', { duration: 6000 })
        $store.toast.info('Info', { duration: Infinity })
--}}
<div
    x-data
    aria-live="polite"
    aria-atomic="false"
    class="fixed {{ $positionClass }} z-(--z-toast) flex flex-col gap-2 w-full max-w-[420px] pointer-events-none px-4 sm:px-0"
>
    <template x-for="toast in $store.toast.toasts" :key="toast.id">
        <div
            x-show="toast.visible"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-4 scale-95"
            x-transition:enter-end="opacity-100 translate-x-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0 scale-100"
            x-transition:leave-end="opacity-0 translate-x-4 scale-95"
            :class="toast.variantClass"
            class="pointer-events-auto relative flex w-full items-start gap-3 rounded-lg border p-4 shadow-lg"
            role="status"
            aria-live="polite"
        >
            {{-- Ícono de variante con color semántico --}}
            {{-- En fondos sólidos todos usan el *-foreground del variant --}}
            <span
                x-show="toast.variant !== 'default'"
                class="mt-0.5 shrink-0 size-4"
                aria-hidden="true"
            >
                <x-lucide-check x-show="toast.variant === 'success'"     class="size-4" stroke-width="2.5" />
                <x-lucide-x x-show="toast.variant === 'destructive'" class="size-4" stroke-width="2.5" />
                <x-lucide-triangle-alert x-show="toast.variant === 'warning'"     class="size-4" />
                <x-lucide-info x-show="toast.variant === 'info'"        class="size-4" />
            </span>

            {{-- Contenido --}}
            <div class="flex-1 min-w-0 space-y-1">
                <x-ui.typography as="small" class="font-semibold" x-text="toast.message"></x-ui.typography>
                <x-ui.typography as="muted"
                    x-show="toast.description"
                    x-text="toast.description"
                ></x-ui.typography>
                <button
                    x-show="toast.action"
                    @click="toast.action?.onClick?.(); $store.toast.dismiss(toast.id)"
                    x-text="toast.action?.label"
                    class="text-sm font-medium underline underline-offset-2 hover:no-underline mt-1"
                ></button>
            </div>

            {{-- Botón cerrar --}}
            <button
                @click="$store.toast.dismiss(toast.id)"
                class="shrink-0 rounded-md p-0.5 opacity-60 hover:opacity-100 transition-opacity focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                aria-label="Cerrar"
            >
                <x-lucide-x class="size-4" />
            </button>
        </div>
    </template>
</div>
