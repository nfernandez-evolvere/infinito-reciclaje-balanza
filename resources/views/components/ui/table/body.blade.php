<tbody {{ $attributes->twMerge('block space-y-2 sm:table-row-group sm:space-y-0 sm:[&_tr:last-child]:border-0 sm:[&_tr:nth-child(even)]:bg-muted/40') }}>
    {{ $slot }}
</tbody>
