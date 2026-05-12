@props([
    'name'        => null,
    'solid'       => false,   // true = fill="currentColor" (dots-horizontal, dots-vertical)
    'strokeWidth' => '2',
    'ariaLabel'   => null,
])

@php
$paths     = $name ? config("icons.{$name}", '') : $slot->toHtml();
$ariaAttrs = $ariaLabel
    ? 'aria-hidden="false" role="img" aria-label="' . e($ariaLabel) . '"'
    : 'aria-hidden="true"';
@endphp

<svg
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 24 24"
    fill="{{ $solid ? 'currentColor' : 'none' }}"
    @unless($solid)
    stroke="currentColor"
    stroke-width="{{ $strokeWidth }}"
    stroke-linecap="round"
    stroke-linejoin="round"
    @endunless
    {!! $ariaAttrs !!}
    {{ $attributes }}
>{!! $paths !!}</svg>
