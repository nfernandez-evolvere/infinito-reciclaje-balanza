@props([
    'model'       => null,   // expresión Alpine a la que enlazar (ej: form._intencion_tara) — requerida
    'value'       => '',     // valor que representa esta tarjeta
    'name'        => null,   // name del radio nativo (envío de formulario / fallback sin JS)
    'state'       => null,   // null | destructive | success | warning | info
    'title'       => null,   // título de la opción
    'description' => null,   // texto explicativo
])

@php
$selectedCard = match($state) {
    'destructive' => 'border-destructive ring-1 ring-destructive',
    'success'     => 'border-success ring-1 ring-success',
    'warning'     => 'border-warning ring-1 ring-warning',
    'info'        => 'border-info ring-1 ring-info',
    default       => 'border-primary ring-1 ring-primary',
};
$focusRing = match($state) {
    'destructive' => 'focus-within:ring-destructive',
    'success'     => 'focus-within:ring-success',
    'warning'     => 'focus-within:ring-warning',
    'info'        => 'focus-within:ring-info',
    default       => 'focus-within:ring-ring',
};
@endphp

<label
    {{ $attributes->twMerge('flex gap-3 rounded-xl border p-3 cursor-pointer transition-colors bg-background focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 ' . $focusRing) }}
    x-bind:class="{{ $model }} === @js($value) ? '{{ $selectedCard }}' : 'border-input hover:bg-accent'"
>
    {{-- El anillo de foco lo dibuja la tarjeta (focus-within); se suprime el del radio para no duplicarlo. --}}
    <x-ui.radio
        :model="$model"
        :name="$name"
        :value="$value"
        :state="$state"
        class="mt-0.5 focus-visible:ring-0 focus-visible:ring-offset-0"
    />

    {{-- Contenido --}}
    <span class="space-y-0.5">
        @if($title)
            <span class="block text-sm font-medium">{{ $title }}</span>
        @endif
        @if($description)
            <span class="block text-xs text-muted-foreground">{{ $description }}</span>
        @endif
        {{ $slot }}
    </span>
</label>
