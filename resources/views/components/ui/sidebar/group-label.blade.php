<div
    data-sidebar-group-label
    {{ $attributes->twMerge(
        'flex h-8 shrink-0 items-center rounded-md px-2 text-xs font-medium text-sidebar-foreground/70',
        'transition-[margin,opacity] duration-200 overflow-hidden'
    ) }}
>
    {{ $slot }}
</div>
