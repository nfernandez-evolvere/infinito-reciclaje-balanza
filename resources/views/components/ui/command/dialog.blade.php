@props(['open' => false])

{{-- Command palette modal (Cmd+K style) --}}
<div x-data="{ open: @js((bool) $open) }">
    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            @keydown.escape.window="open = false"
            @keydown.meta.k.window="open = !open"
            @keydown.ctrl.k.window="open = !open"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-start justify-center pt-[20vh] bg-black/50 backdrop-blur-sm"
            @click.self="open = false"
        >
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                class="w-full max-w-lg"
            >
                {{ $slot }}
            </div>
        </div>
    </template>
</div>
