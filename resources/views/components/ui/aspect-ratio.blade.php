@props(['ratio' => '16/9'])

<div
    style="--ratio: {{ $ratio }}; aspect-ratio: var(--ratio);"
    {{ $attributes->twMerge('relative w-full overflow-hidden') }}
>
    {{ $slot }}
</div>
