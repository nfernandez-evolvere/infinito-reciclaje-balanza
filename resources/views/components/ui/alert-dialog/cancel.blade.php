{{-- Botón cancelar: siempre cierra el diálogo --}}
<x-ui.button variant="outline" @click="open = false" {{ $attributes }}>
    {{ $slot }}
</x-ui.button>
