@props([
    'variant' => 'default',
    'size'    => 'default',
])

{{-- Botón de confirmación: NO cierra automáticamente; el caller decide via @click --}}
<x-ui.button :variant="$variant" :size="$size" {{ $attributes }}>
    {{ $slot }}
</x-ui.button>
