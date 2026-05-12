<div {{ $attributes->twMerge(
    'grid place-items-center gap-1.5 text-center',
    'has-[[data-slot=alert-dialog-media]]:gap-x-4',
    'sm:group-data-[size=default]/alert-dialog-content:place-items-start sm:group-data-[size=default]/alert-dialog-content:text-left'
) }}>
    {{ $slot }}
</div>
