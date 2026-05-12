@props(['showOnHover' => false])

<button
    type="button"
    {{ $attributes->twMerge(
        'absolute right-1 top-1.5 flex aspect-square w-5 items-center justify-center rounded-md p-0 text-sidebar-foreground outline-none ring-sidebar-ring transition-transform',
        'hover:bg-sidebar-accent hover:text-sidebar-accent-foreground',
        'focus-visible:ring-2 peer-hover/menu-button:text-sidebar-accent-foreground',
        '[&>svg]:size-4 [&>svg]:shrink-0',
        $showOnHover ? 'opacity-0 group-focus-within/menu-item:opacity-100 group-hover/menu-item:opacity-100 data-[state=open]:opacity-100 peer-data-[active=true]/menu-button:text-sidebar-accent-foreground' : ''
    ) }}
>
    {{ $slot }}
</button>
