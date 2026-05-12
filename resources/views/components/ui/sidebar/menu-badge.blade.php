<div
    {{ $attributes->twMerge(
        'pointer-events-none absolute right-1 flex h-5 min-w-5 select-none items-center justify-center rounded-md px-1 text-xs font-medium tabular-nums text-sidebar-foreground',
        'peer-hover/menu-button:text-sidebar-accent-foreground peer-data-[active=true]/menu-button:text-sidebar-accent-foreground',
        'top-1/2 -translate-y-1/2',
        'group-data-[collapsed=true]/sidebar:hidden'
    ) }}
>
    {{ $slot }}
</div>
