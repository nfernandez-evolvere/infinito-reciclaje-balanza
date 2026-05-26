@props(['variant' => 'default'])

@php
$base = 'w-full relative rounded-xl sm:overflow-x-auto sm:border sm:border-border sm:rounded-xl [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-border [&::-webkit-scrollbar-thumb]:rounded-full';
$variants = [
    'default' => 'p-4 shadow-lg',
    'flat'    => '',
];
@endphp

<div {{ $attributes->twMerge($base . ' ' . ($variants[$variant] ?? $variants['default'])) }}>
    <table class="w-full text-sm block sm:table">
        {{ $slot }}
    </table>
</div>
