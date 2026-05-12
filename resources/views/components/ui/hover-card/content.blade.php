{{-- width puede sobreescribirse: class="w-80" --}}
<template x-teleport="body">
    <div
        :id="uid"
        x-show="open"
        x-cloak
        x-effect="open && $nextTick(() => _place())"
        :style="{ top: top + 'px', left: left + 'px' }"
        :class="_origin()"
        {{-- Mantiene el card abierto cuando el cursor se mueve del trigger al panel --}}
        @mouseenter="clearTimeout(_t)"
        @mouseleave="_close()"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        {{ $attributes->twMerge(
            'fixed z-(--z-popover) w-64 rounded-lg bg-popover p-2.5 text-sm text-popover-foreground shadow-md ring-1 ring-foreground/10'
        ) }}
    >
        {{ $slot }}
    </div>
</template>
