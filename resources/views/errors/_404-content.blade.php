<div class="flex h-full min-h-[60vh] flex-col items-center justify-center gap-6 text-center">

    <div class="flex size-16 items-center justify-center rounded-full bg-primary/10">
        @if (!empty($subdominio_invalido))
            <x-lucide-building-2 class="size-8 text-primary" />
        @else
            <x-lucide-file-search class="size-8 text-primary" />
        @endif
    </div>

    <div class="space-y-2">
        @if (!empty($subdominio_invalido))
            <x-ui.typography as="h3" element="h1">Organización no encontrada</x-ui.typography>
            <x-ui.typography as="muted">
                <span class="font-medium text-foreground">{{ $subdominio_invalido }}</span>
                no está registrada en el sistema.
            </x-ui.typography>
        @else
            <x-ui.typography as="h3" element="h1">Página no encontrada</x-ui.typography>
            <x-ui.typography as="muted">La página que buscás no existe o fue movida.</x-ui.typography>
        @endif
    </div>

    @empty($subdominio_invalido)
        <a href="{{ $home }}">
            <x-ui.button>Volver al inicio</x-ui.button>
        </a>
    @endempty
</div>
