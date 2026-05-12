@props(['type' => 'single'])

<div x-data="{ active: null }" {{ $attributes->merge(['class' => 'w-full']) }}>
    {{ $slot }}
</div>
