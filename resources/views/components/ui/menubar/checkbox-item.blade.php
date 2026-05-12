@props([
    'checked'      => false,
    'disabled'     => false,
    'closeOnClick' => true,
])

@php
$initialState = $checked === 'indeterminate'
    ? 'indeterminate'
    : ((bool) $checked ? 'checked' : 'unchecked');
@endphp

<div
    role="menuitemcheckbox"
    tabindex="-1"
    x-data="{ state: @js($initialState) }"
    :aria-checked="state === 'indeterminate' ? 'mixed' : (state === 'checked' ? 'true' : 'false')"
    :data-state="state"
    @if($disabled)
        aria-disabled="true"
        data-disabled
    @else
        @click="state = state === 'checked' ? 'unchecked' : 'checked'; @js($closeOnClick) && _close()"
        @keydown.enter.prevent="state = state === 'checked' ? 'unchecked' : 'checked'; @js($closeOnClick) && _close()"
    @endif
    @focus="$el.setAttribute('data-highlighted', '')"
    @blur="$el.removeAttribute('data-highlighted')"
    {{ $attributes->twMerge(
        'relative flex cursor-pointer items-center gap-1.5 rounded-sm py-1.5 pl-2 pr-8 text-sm outline-none select-none transition-colors [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*=size-])]:size-4',
        'hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground data-[highlighted]:bg-accent data-[highlighted]:text-accent-foreground',
        $disabled ? 'pointer-events-none opacity-50' : ''
    ) }}
>
    {{ $slot }}
    <span class="pointer-events-none absolute right-2 flex size-4 items-center justify-center">
        <x-lucide-check x-show="state === 'checked'"       class="size-3.5" />
        <x-lucide-minus x-show="state === 'indeterminate'"  class="size-3.5" />
    </span>
</div>
