@props(['disabled' => false])

<input
    data-slot="input-group-control"
    @if($disabled) disabled @endif
    {{ $attributes->twMerge(
        'flex-1 min-w-0 bg-transparent px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground',
        'focus-visible:outline-none',
        'disabled:cursor-not-allowed',
        'file:border-0 file:bg-transparent file:text-sm file:font-medium'
    ) }}
/>
