@props(['value' => ''])

<div
    role="tabpanel"
    id="tab-panel-{{ $value }}"
    :data-state="active === @js($value) ? 'active' : 'inactive'"
    :data-orientation="orientation"
    x-show="active === @js($value)"
    x-cloak
    x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0 translate-y-1"
    x-transition:enter-end="opacity-100 translate-y-0"
    :tabindex="active === @js($value) ? '0' : '-1'"
    {{ $attributes->twMerge('mt-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2') }}
>
    {{ $slot }}
</div>
