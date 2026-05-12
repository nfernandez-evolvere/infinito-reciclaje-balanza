@props([])

<div
    x-show="active === value"
    x-collapse
    {{ $attributes->merge(['class' => 'overflow-hidden text-sm']) }}
>
    <div class="pb-4 pt-0">{{ $slot }}</div>
</div>
