@props(['variant' => 'default'])

@php
    $variants = [
        'default'     => 'bg-background text-foreground [&>svg]:text-foreground',
        'destructive' => 'border-destructive/50 text-destructive [&>svg]:text-destructive',
    ];
@endphp

<div
    role="alert"
    {{ $attributes->merge(['class' => "relative w-full rounded-lg border px-4 py-3 text-sm [&>svg+div]:translate-y-[-3px] [&>svg]:absolute [&>svg]:left-4 [&>svg]:top-4 [&>svg~*]:pl-7 [&:has(svg)]:pl-11 {$variants[$variant]}"]) }}
>
    {{ $slot }}
</div>
