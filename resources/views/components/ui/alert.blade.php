@props([
    'state' => null,        // null | destructive | success | warning | info
    'title' => null,        // string | slot — título del alert
    'description' => null,  // string | slot — descripción / cuerpo
    'icon' => null,         // string | null — nombre lucide; si es null se deriva del state
    'hideIcon' => false,    // bool — ocultar el ícono
    'dismissible' => false, // bool — mostrar botón para cerrar el alert
])

@php
// Fondo suave + borde izquierdo acentuado en el color fuerte del estado.
$wrapperClass = match($state) {
    'destructive' => 'border-destructive-border border-l-destructive bg-destructive-subtle text-destructive-subtle-foreground',
    'success'     => 'border-success-border border-l-success bg-success-subtle text-success-subtle-foreground',
    'warning'     => 'border-warning-border border-l-warning bg-warning-subtle text-warning-subtle-foreground',
    'info'        => 'border-info-border border-l-info bg-info-subtle text-info-subtle-foreground',
    default       => 'border-border border-l-foreground bg-card text-foreground',
};

// Círculo del ícono: tono sólido (más fuerte que el fondo) + ícono en su foreground.
$iconWrapClass = match($state) {
    'destructive' => 'bg-destructive/15 text-destructive',
    'success'     => 'bg-success/15 text-success',
    'warning'     => 'bg-warning/20 text-warning',
    'info'        => 'bg-info/15 text-info',
    default       => 'bg-foreground/10 text-foreground',
};

// Botón de cierre: hover sutil tintado con el color del estado.
$closeClass = match($state) {
    'destructive' => 'text-destructive/70 hover:bg-destructive/10 hover:text-destructive focus-visible:ring-destructive/40',
    'success'     => 'text-success/70 hover:bg-success/10 hover:text-success focus-visible:ring-success/40',
    'warning'     => 'text-warning/80 hover:bg-warning/10 hover:text-warning focus-visible:ring-warning/40',
    'info'        => 'text-info/70 hover:bg-info/10 hover:text-info focus-visible:ring-info/40',
    default       => 'text-muted-foreground hover:bg-muted hover:text-foreground focus-visible:ring-ring',
};

$iconName = $icon ?? match($state) {
    'destructive' => 'circle-alert',
    'success'     => 'circle-check',
    'warning'     => 'triangle-alert',
    'info'        => 'info',
    default       => 'info',
};
@endphp

<div role="alert"
    @if ($dismissible) x-data="{ show: true }" x-show="show" x-collapse @endif
    {{ $attributes->twMerge('flex w-full items-start gap-3 rounded-lg border border-l-4 p-4', $wrapperClass) }}>
    @unless ($hideIcon)
        <span class="flex size-10 shrink-0 items-center justify-center rounded-full {{ $iconWrapClass }}">
            <x-dynamic-component :component="'lucide-' . $iconName" class="size-6" />
        </span>
    @endunless

    <div class="min-w-0 flex-1 self-center">
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

    @if ($dismissible)
        <button type="button"
            @click="show = false"
            aria-label="Cerrar alerta"
            class="-mr-1 -mt-1 flex size-7 shrink-0 items-center justify-center rounded-md outline-none transition-colors focus-visible:ring-2 {{ $closeClass }}">
            <x-lucide-x class="size-4" />
        </button>
    @endif
</div>
