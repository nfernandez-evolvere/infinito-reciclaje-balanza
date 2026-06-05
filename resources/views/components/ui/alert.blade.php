@props([
    'state' => null,        // null | destructive | success | warning | info
    'title' => null,        // string | slot — título del alert
    'description' => null,  // string | slot — descripción / cuerpo
    'icon' => null,         // string | null — nombre lucide; si es null se deriva del state
    'hideIcon' => false,    // bool — ocultar el ícono
])

@php
$wrapperClass = match($state) {
    'destructive' => 'border-destructive-border bg-destructive-subtle text-destructive-subtle-foreground',
    'success'     => 'border-success-border bg-success-subtle text-success-subtle-foreground',
    'warning'     => 'border-warning-border bg-warning-subtle text-warning-subtle-foreground',
    'info'        => 'border-info-border bg-info-subtle text-info-subtle-foreground',
    default       => 'border-border bg-background text-foreground',
};

$iconColor = match($state) {
    'destructive' => 'text-destructive',
    'success'     => 'text-success',
    'warning'     => 'text-warning',
    'info'        => 'text-info',
    default       => 'text-foreground',
};

$iconName = $icon ?? match($state) {
    'destructive' => 'circle-alert',
    'success'     => 'circle-check',
    'warning'     => 'triangle-alert',
    'info'        => 'info',
    default       => 'info',
};
@endphp

<div role="alert" {{ $attributes->twMerge('flex w-full items-start gap-3 rounded-lg border p-4', $wrapperClass) }}>
    @unless ($hideIcon)
        <x-dynamic-component :component="'lucide-' . $iconName" class="mt-0.5 size-4 shrink-0 {{ $iconColor }}" />
    @endunless

    <div class="min-w-0 flex-1">
        @if (filled($title))
            <x-ui.alert.title>{{ $title }}</x-ui.alert.title>
        @endif
        @if (filled($description))
            <x-ui.alert.description>{{ $description }}</x-ui.alert.description>
        @endif
        {{ $slot }}
    </div>

    @isset($action)
        <div class="shrink-0 self-center">
            {{ $action }}
        </div>
    @endisset
</div>
