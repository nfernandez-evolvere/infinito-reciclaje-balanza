<div
    x-show="subOpen"
    x-cloak
    x-effect="subOpen && $nextTick(() => subFocusFirst())"
    :data-state="subOpen ? 'open' : 'closed'"
    x-transition:enter="transition ease-out duration-100 origin-left"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-75 origin-left"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    role="menu"
    aria-orientation="vertical"
    @keydown.escape.stop="subOpen = false; $refs.subTrigger?.focus()"
    @keydown.arrow-left.prevent="subOpen = false; $refs.subTrigger?.focus()"
    @keydown.arrow-down.prevent="subFocusNext()"
    @keydown.arrow-up.prevent="subFocusPrev()"
    {{ $attributes->twMerge(
        'absolute left-full top-0 z-50 ml-1 min-w-40 w-max',
        'rounded-lg bg-popover p-1 text-popover-foreground shadow-lg ring-1 ring-foreground/10'
    ) }}
>
    {{ $slot }}
</div>
