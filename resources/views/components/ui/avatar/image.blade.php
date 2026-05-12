@props(['src' => '', 'alt' => ''])

<img
    src="{{ $src }}"
    alt="{{ $alt }}"
    {{ $attributes->twMerge('aspect-square h-full w-full object-cover') }}
    onerror="this.style.display='none'; this.nextElementSibling && (this.nextElementSibling.style.display='flex');"
/>
