@props([
    'variant' => 'default',  // default | secondary | outline
    'state'   => null,       // null | destructive | success | warning | info
    'subtle'  => false,      // true = usa subtle bg en vez de sólido
])

@php
$base = 'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2';

$colorClass = $state
    ? ($subtle
        ? match($state) {
            'success' => 'border-success-border bg-success-subtle text-success-subtle-foreground',
            'warning' => 'border-warning-border bg-warning-subtle text-warning-subtle-foreground',
            'info'    => 'border-info-border bg-info-subtle text-info-subtle-foreground',
            default   => 'border-destructive-border bg-destructive-subtle text-destructive-subtle-foreground',
        }
        : match($state) {
            'success' => 'border-transparent bg-success text-success-foreground',
            'warning' => 'border-transparent bg-warning text-warning-foreground',
            'info'    => 'border-transparent bg-info text-info-foreground',
            default   => 'border-transparent bg-destructive text-destructive-foreground',
        })
    : match($variant) {
        'secondary' => 'border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80',
        'outline'   => 'border-border text-foreground',
        default     => 'border-transparent bg-primary text-primary-foreground hover:bg-primary/80',
    };
@endphp

<span {{ $attributes->twMerge($base, $colorClass) }}>
    {{ $slot }}
</span>
