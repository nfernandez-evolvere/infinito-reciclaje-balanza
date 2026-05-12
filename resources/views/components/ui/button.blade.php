@props([
    'variant' => 'default',   // default | secondary | outline | ghost | link
    'size'    => 'md',        // sm | md | lg | icon
    'state'   => null,        // null | destructive | success | warning | info
    'as'      => 'button',    // button | a
])

@php
$base = 'inline-flex cursor-pointer items-center justify-center gap-2 whitespace-nowrap rounded-md font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:shrink-0';

$sizeClass = match($size) {
    'sm'   => 'h-8 px-3 text-[13px] gap-1.5 [&_svg]:size-3.5',
    'lg'   => 'h-12 px-5 text-base [&_svg]:size-[18px]',
    'icon' => 'h-10 w-10 [&_svg]:size-4',
    default => 'h-10 px-4 text-sm [&_svg]:size-4',
};

$ringClass = match($state ?? $variant) {
    'destructive' => 'focus-visible:ring-destructive',
    'success'     => 'focus-visible:ring-success',
    'warning'     => 'focus-visible:ring-warning',
    'info'        => 'focus-visible:ring-info',
    default       => 'focus-visible:ring-ring',
};

$colorClass = $state
    ? match("$variant|$state") {
        'outline|destructive' => 'border border-destructive-border bg-transparent text-destructive hover:bg-destructive-subtle hover:text-destructive-subtle-foreground',
        'outline|success'     => 'border border-success-border bg-transparent text-success hover:bg-success-subtle hover:text-success-subtle-foreground',
        'outline|warning'     => 'border border-warning-border bg-transparent text-warning hover:bg-warning-subtle hover:text-warning-subtle-foreground',
        'outline|info'        => 'border border-info-border bg-transparent text-info hover:bg-info-subtle hover:text-info-subtle-foreground',
        'ghost|destructive'   => 'text-destructive hover:bg-destructive-subtle hover:text-destructive-subtle-foreground',
        'ghost|success'       => 'text-success hover:bg-success-subtle hover:text-success-subtle-foreground',
        'ghost|warning'       => 'text-warning hover:bg-warning-subtle hover:text-warning-subtle-foreground',
        'ghost|info'          => 'text-info hover:bg-info-subtle hover:text-info-subtle-foreground',
        'link|destructive'    => 'text-destructive underline-offset-4 hover:underline',
        'link|success'        => 'text-success underline-offset-4 hover:underline',
        'link|warning'        => 'text-warning underline-offset-4 hover:underline',
        'link|info'           => 'text-info underline-offset-4 hover:underline',
        default               => match($state) {
            'success' => 'bg-success text-success-foreground hover:bg-success/90',
            'warning' => 'bg-warning text-warning-foreground hover:bg-warning/90',
            'info'    => 'bg-info text-info-foreground hover:bg-info/90',
            default   => 'bg-destructive text-destructive-foreground hover:bg-destructive/90',
        },
    }
    : match($variant) {
        'secondary' => 'bg-secondary text-secondary-foreground hover:bg-secondary/80',
        'outline'   => 'border border-input bg-background hover:bg-accent hover:text-accent-foreground',
        'ghost'     => 'hover:bg-accent hover:text-accent-foreground',
        'link'      => 'text-primary underline-offset-4 hover:underline p-0 h-auto',
        default     => 'bg-primary text-primary-foreground hover:bg-primary/90',
    };
@endphp

@if($as === 'a')
    <a {{ $attributes->twMerge($base, $ringClass, $sizeClass, $colorClass) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->twMerge($base, $ringClass, $sizeClass, $colorClass)->merge(['type' => 'button']) }}>
        {{ $slot }}
    </button>
@endif
