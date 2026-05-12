@props(['value'])

<div
    id="panel-{{ $value }}"
    role="tabpanel"
    aria-labelledby="tab-{{ $value }}"
    tabindex="0"
    x-show="active === '{{ $value }}'"
    {{ $attributes->merge(['class' => 'mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2']) }}
>
    {{ $slot }}
</div>
