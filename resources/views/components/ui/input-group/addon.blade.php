@props(['align' => 'inline-start'])
{{-- align: inline-start | inline-end | block-start | block-end --}}

@php
$alignClass = match($align) {
    'inline-end'   => 'order-last pr-2.5 has-[>button]:mr-[-0.2rem]',
    'block-start'  => 'order-first w-full justify-start px-3 pt-2 pb-1 text-xs',
    'block-end'    => 'order-last w-full justify-start px-3 pt-1 pb-2 text-xs',
    default        => 'order-first pl-2.5 has-[>button]:ml-[-0.2rem]',
};
@endphp

<div
    data-align="{{ $align }}"
    @click="$el.closest('[role=group]')?.querySelector('[data-slot=input-group-control]')?.focus()"
    {{ $attributes->twMerge(
        'flex shrink-0 cursor-text items-center gap-1.5 text-sm text-muted-foreground select-none',
        '[&_svg:not([class*=size-])]:size-4',
        $alignClass
    ) }}
>
    {{ $slot }}
</div>
