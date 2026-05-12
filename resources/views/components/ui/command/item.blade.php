@props([
    'value'    => null,    // unique value for this item (used as data-value)
    'keywords' => [],      // extra search terms (in addition to text content)
    'disabled' => false,
    'href'     => null,
])

@php
// Build searchable string: value + keywords joined
$searchTerms = collect([$value, ...(array) $keywords])->filter()->implode(' ');
@endphp

<div
    role="option"
    data-value="{{ $value }}"
    data-keywords="{{ strtolower($searchTerms) }}"
    :aria-selected="activeItem === @js($value) ? 'true' : 'false'"
    @if($disabled) aria-disabled="true" data-disabled @endif
    x-show="!search || $el.dataset.keywords?.includes(search.toLowerCase()) || $el.textContent.toLowerCase().includes(search.toLowerCase())"
    @if(!$disabled) @click="_updateActive($el)" @endif
    @mouseenter="!@js($disabled) && _updateActive($el)"
    {{ $attributes->twMerge(
        'relative flex cursor-default select-none items-center gap-2 rounded-sm px-2 py-1.5 text-sm outline-none transition-colors',
        '[&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*=size-])]:size-4',
        'data-[selected]:bg-accent data-[selected]:text-accent-foreground',
        $disabled ? 'pointer-events-none opacity-50' : 'cursor-pointer',
        $href ? '' : ''
    ) }}
>
    @if($href)
        <x-ui.button as="a" href="{{ $href }}" class="contents">{{ $slot }}</x-ui.button>
    @else
        {{ $slot }}
    @endif
</div>
