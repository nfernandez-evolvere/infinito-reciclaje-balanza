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
    x-data="{ get visible() { return $store.toast.toasts.filter(t => t.visible); } }"
    class="fixed {{ $posClass }} z-(--z-toast) flex flex-col-reverse gap-2 w-full max-w-[420px] pointer-events-none px-4 sm:px-0
           max-sm:!top-4 max-sm:!bottom-auto max-sm:!left-1/2 max-sm:!right-auto max-sm:!-translate-x-1/2"
    aria-live="polite"
    aria-atomic="false"
>
    <template x-for="toast in visible" :key="toast.id">
        <div
            x-show="toast.visible"
            :class="[toast.toastClass, toast.accentClass]"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="pointer-events-auto relative flex w-full items-start gap-3 rounded-lg border border-l-4 p-4 shadow-lg transition-all duration-300"
            role="status"
        >
            {{-- Ícono con badge circular de color --}}
            <span
                class="shrink-0 flex items-center justify-center size-8 rounded-full mt-0.5"
                :class="toast.iconClass"
                aria-hidden="true"
            >
                <x-lucide-check          x-show="toast.variant === 'success'"     class="size-4" stroke-width="2.5" />
                <x-lucide-circle-x       x-show="toast.variant === 'destructive'" class="size-4" stroke-width="2" />
                <x-lucide-triangle-alert x-show="toast.variant === 'warning'"     class="size-4" stroke-width="2" />
                <x-lucide-info           x-show="toast.variant === 'info'"        class="size-4" stroke-width="2" />
                <x-lucide-bell           x-show="toast.variant === 'default'"     class="size-4" stroke-width="2" />
                <svg x-show="toast.variant === 'loading'"
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                    class="size-4 animate-spin"
                    fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                </svg>
            </span>

            <div class="flex-1 min-w-0 space-y-0.5 pt-1">
                <p class="text-sm font-semibold leading-snug" x-text="toast.message"></p>
                <p class="text-sm text-foreground/70 leading-snug" x-show="toast.description" x-text="toast.description"></p>
                <button
                    x-show="toast.action"
                    @click="toast.action?.onClick?.(); $store.toast.dismiss(toast.id)"
                    x-text="toast.action?.label"
                    class="text-sm font-medium underline underline-offset-2 hover:no-underline mt-1 block"
                ></button>
            </div>

            <div x-show="toast.variant !== 'loading'" class="shrink-0 mt-0.5">
                <template x-if="toast.variant === 'success'">
                    <x-ui.button variant="ghost" state="success" size="icon" class="h-7! w-7! [&_svg]:size-3.5" @click="$store.toast.dismiss(toast.id)" aria-label="Cerrar">
                        <x-lucide-x />
                    </x-ui.button>
                </template>
                <template x-if="toast.variant === 'destructive'">
                    <x-ui.button variant="ghost" state="destructive" size="icon" class="h-7! w-7! [&_svg]:size-3.5" @click="$store.toast.dismiss(toast.id)" aria-label="Cerrar">
                        <x-lucide-x />
                    </x-ui.button>
                </template>
                <template x-if="toast.variant === 'warning'">
                    <x-ui.button variant="ghost" state="warning" size="icon" class="h-7! w-7! [&_svg]:size-3.5" @click="$store.toast.dismiss(toast.id)" aria-label="Cerrar">
                        <x-lucide-x />
                    </x-ui.button>
                </template>
                <template x-if="toast.variant === 'info'">
                    <x-ui.button variant="ghost" state="info" size="icon" class="h-7! w-7! [&_svg]:size-3.5" @click="$store.toast.dismiss(toast.id)" aria-label="Cerrar">
                        <x-lucide-x />
                    </x-ui.button>
                </template>
                <template x-if="!['success','destructive','warning','info'].includes(toast.variant)">
                    <x-ui.button variant="ghost" size="icon" class="h-7! w-7! [&_svg]:size-3.5" @click="$store.toast.dismiss(toast.id)" aria-label="Cerrar">
                        <x-lucide-x />
                    </x-ui.button>
                </template>
            </div>
        </div>
    </template>
</div>
