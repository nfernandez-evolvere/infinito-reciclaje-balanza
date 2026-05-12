<span {{ $attributes->twMerge(
    'flex items-center gap-1.5 px-2.5 text-sm text-muted-foreground',
    '[&_svg:not([class*=size-])]:size-4'
) }}>
    {{ $slot }}
</span>
