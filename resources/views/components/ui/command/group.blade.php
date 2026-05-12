@props([
    'heading'  => null,
    'keywords' => [],    // extra search terms for the group
])

@php
// Build the filter expression: hide group when no items inside match the search
// The group visibility is handled by Alpine via data-group filtering on items.
@endphp

<div
    data-command-group
    {{ $attributes->twMerge('overflow-hidden') }}
>
    @if($heading)
        <div class="px-2 py-1.5 text-xs font-medium text-muted-foreground select-none">
            {{ $heading }}
        </div>
    @endif
    {{ $slot }}
</div>
