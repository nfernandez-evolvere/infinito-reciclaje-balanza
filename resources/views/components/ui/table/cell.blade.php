@props(['stack' => false, 'actions' => false])

<td {{ $attributes->twMerge(
    $stack
        ? 'flex flex-col items-start gap-0.5 px-4 py-2.5 text-sm bg-card'
        : 'flex items-center justify-between gap-4 px-4 py-2.5 text-sm bg-card',
    $actions ? 'order-first justify-end sm:order-0 top' : '',
    'before:content-[attr(data-label)] before:shrink-0 before:text-xs before:font-semibold before:uppercase before:tracking-wide before:text-muted-foreground',
    'sm:table-cell sm:bg-transparent sm:px-3 sm:py-2 sm:text-center sm:align-middle sm:border-r sm:border-border sm:last:border-r-0 sm:before:content-none sm:[&:has([role=checkbox])]:pr-0'
) }}>
    {{ $slot }}
</td>
