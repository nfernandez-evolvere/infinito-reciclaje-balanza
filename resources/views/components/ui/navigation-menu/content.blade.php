<template x-teleport="body">
    <div
        :id="uid"
        x-show="open"
        x-cloak
        :style="{ top: top + 'px', left: left + 'px' }"
        :data-state="open ? 'open' : 'closed'"
        x-transition:enter="transition ease-out duration-200 origin-top"
        x-transition:enter-start="opacity-0 scale-y-95 translate-y-1"
        x-transition:enter-end="opacity-100 scale-y-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150 origin-top"
        x-transition:leave-start="opacity-100 scale-y-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-y-95 translate-y-1"
        {{ $attributes->twMerge(
            'fixed z-50 w-auto min-w-[220px]',
            'rounded-xl border border-border bg-popover p-1 text-popover-foreground shadow-lg'
        ) }}
    >
        {{ $slot }}
    </div>
</template>
