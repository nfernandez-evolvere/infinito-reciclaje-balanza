@props(['disabled' => false])

<button
    type="button"
    role="menuitem"
    x-ref="trigger"
    :aria-expanded="open.toString()"
    aria-haspopup="menu"
    :data-state="open ? 'open' : 'closed'"
    @click="toggle()"
    @mouseenter="_anyOpen() && !open && _open()"
    @if($disabled) disabled data-disabled @endif
    {{ $attributes->twMerge(
        'flex cursor-default select-none items-center rounded-sm px-3 py-1.5 text-sm font-medium outline-none transition-colors',
        'hover:bg-accent hover:text-accent-foreground',
        'focus:bg-accent focus:text-accent-foreground',
        'data-[state=open]:bg-accent data-[state=open]:text-accent-foreground',
        $disabled ? 'pointer-events-none opacity-50' : ''
    ) }}
>
    {{ $slot }}
</button>
