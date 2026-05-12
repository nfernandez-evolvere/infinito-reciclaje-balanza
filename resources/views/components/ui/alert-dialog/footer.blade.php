<div {{ $attributes->twMerge(
    '-mx-4 -mb-4 flex flex-col-reverse gap-2 rounded-b-xl border-t bg-muted/50 p-4',
    'sm:flex-row sm:justify-end',
    'group-data-[size=sm]/alert-dialog-content:grid group-data-[size=sm]/alert-dialog-content:grid-cols-2'
) }}>
    {{ $slot }}
</div>
