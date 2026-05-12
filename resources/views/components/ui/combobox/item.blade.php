@props([
    'value',
    'label'    => null,   // used for search filtering; falls back to slot text via data-label
    'disabled' => false,
])

@php
$val      = (string) $value;
$labelStr = strtolower((string) ($label ?? $value));
@endphp

<div
    role="option"
    :id="uid + ':' + $el.dataset.value"
    data-value="{{ $val }}"
    data-label="{{ $labelStr }}"
    :aria-selected="value !== null && String(value) === $el.dataset.value"
    @if($disabled) aria-disabled="true" @endif
    x-show="!search || $el.dataset.label.includes(search.toLowerCase())"
    @click="!$el.hasAttribute('aria-disabled') && select($el.dataset.value)"
    @mouseenter="!$el.hasAttribute('aria-disabled') && (highlighted = $el.dataset.value)"
    :class="[
        _itemCls(),
        highlighted !== null && String(highlighted) === $el.dataset.value ? 'bg-accent text-accent-foreground' : '',
        @js($disabled) ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
    ]"
    {{ $attributes->twMerge('relative flex items-center rounded-sm pl-8 pr-2 select-none outline-none') }}
>
    <span
        class="absolute left-2 flex size-4 items-center justify-center"
        x-show="value !== null && String(value) === $el.parentElement.dataset.value"
    >
        <x-lucide-check class="size-3.5" stroke-width="2.5" />
    </span>

    {{ $slot }}
</div>
