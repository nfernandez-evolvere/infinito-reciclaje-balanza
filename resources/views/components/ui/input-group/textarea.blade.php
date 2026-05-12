@props(['disabled' => false])

<textarea
    data-slot="input-group-control"
    @if($disabled) disabled @endif
    {{ $attributes->twMerge(
        'flex-1 min-w-0 resize-none bg-transparent px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground',
        'focus-visible:outline-none',
        'disabled:cursor-not-allowed'
    ) }}
>{{ $slot }}</textarea>
