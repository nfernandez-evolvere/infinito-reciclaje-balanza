@props([])

<ul {{ $attributes->merge(['class' => 'flex flex-row items-center gap-1']) }}>
    {{ $slot }}
</ul>
