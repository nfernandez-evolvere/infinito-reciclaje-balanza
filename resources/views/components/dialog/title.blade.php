@props([])

<h2 :id="dialogId" {{ $attributes->merge(['class' => 'text-lg font-semibold leading-none tracking-tight']) }}>
    {{ $slot }}
</h2>
