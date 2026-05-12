@props(['inset' => false])

<div {{ $attributes->twMerge(
    'px-2 py-1.5 text-xs font-medium text-muted-foreground select-none',
    $inset ? 'pl-8' : ''
) }}>
    {{ $slot }}
</div>
