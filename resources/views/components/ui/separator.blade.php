@props([
    'orientation' => 'horizontal',  // horizontal | vertical
    'decorative'  => true,
])

@php
$class = $orientation === 'vertical'
    ? 'shrink-0 bg-border w-px self-stretch'
    : 'shrink-0 bg-border h-px w-full';
@endphp

<div
    role="{{ $decorative ? 'none' : 'separator' }}"
    @if(!$decorative) aria-orientation="{{ $orientation }}" @endif
    {{ $attributes->twMerge($class) }}
></div>
