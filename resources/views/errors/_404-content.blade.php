<div class="flex h-full min-h-[60vh] flex-col items-center justify-center gap-6 text-center">

    <div class="flex size-16 items-center justify-center rounded-full bg-primary/10">
        <x-lucide-file-search class="size-8 text-primary" />
    </div>

    <div class="space-y-2">
        <x-ui.typography as="h3" element="h1">Página no encontrada</x-ui.typography>
        <x-ui.typography as="muted">La página que buscás no existe o fue movida.</x-ui.typography>
    </div>

    <a href="{{ $home }}">
        <x-ui.button>Volver al inicio</x-ui.button>
    </a>
</div>
