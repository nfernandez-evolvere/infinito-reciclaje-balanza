<div class="flex h-full min-h-[60vh] flex-col items-center justify-center gap-6 text-center">

    <div class="flex size-16 items-center justify-center rounded-full bg-destructive/10">
        <x-lucide-server-crash class="size-8 text-destructive" />
    </div>

    <div class="space-y-2">
        @if(($userRole ?? null) === 'operador')
            <x-ui.typography as="h3" element="h1">El sistema no está disponible</x-ui.typography>
            <x-ui.typography as="muted">Algo salió mal. Intentá de nuevo o contactá a un administrador.</x-ui.typography>
        @else
            <x-ui.typography as="h3" element="h1">El sistema no está disponible</x-ui.typography>
            <x-ui.typography as="muted">Algo salió mal. Intentá de nuevo o contactá a un administrador.</x-ui.typography>
        @endif
    </div>

    <a href="/">
        <x-ui.button>Volver al inicio</x-ui.button>
    </a>

</div>
