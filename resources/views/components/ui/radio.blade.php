@props([
    'name'    => null,
    'value'   => 'on',
    'checked' => false,
    'model'   => null,   // expresión Alpine para enlazar con x-model (ej: form.campo)
    'state'   => null,   // null | destructive | success | warning | info
])

@php
// El punto se dibuja con CSS puro: appearance-none + bg-clip-content + padding.
// El fondo (color del punto) solo se ve dentro del content-box → aparece como dot al estar :checked.
$stateClasses = match($state) {
    'destructive' => 'checked:border-destructive checked:bg-destructive focus-visible:ring-destructive',
    'success'     => 'checked:border-success checked:bg-success focus-visible:ring-success',
    'warning'     => 'checked:border-warning checked:bg-warning focus-visible:ring-warning',
    'info'        => 'checked:border-info checked:bg-info focus-visible:ring-info',
    default       => 'checked:border-primary checked:bg-primary focus-visible:ring-ring',
};
@endphp

<input
    type="radio"
    @if($name) name="{{ $name }}" @endif
    value="{{ $value }}"
    @if($model) x-model="{{ $model }}" @endif
    @if($checked && ! $model) checked @endif
    {{ $attributes->twMerge('size-4 shrink-0 appearance-none rounded-full border-2 border-input bg-clip-content p-[2px] cursor-pointer transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 ' . $stateClasses) }}
/>
