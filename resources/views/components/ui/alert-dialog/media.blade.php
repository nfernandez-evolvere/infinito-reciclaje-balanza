<div
    data-slot="alert-dialog-media"
    {{ $attributes->twMerge(
        'mb-2 inline-flex size-10 items-center justify-center rounded-md bg-muted',
        '[&_svg:not([class*=size-])]:size-6',
        'sm:group-data-[size=default]/alert-dialog-content:row-span-2'
    ) }}
>
    {{ $slot }}
</div>
