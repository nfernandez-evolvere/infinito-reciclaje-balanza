@props([
    'icon'        => 'construction',
    'title'       => 'Pantalla en desarrollo',
    'description' => 'Esta sección estará disponible próximamente.',
])

<div class="flex flex-col items-center justify-center min-h-[60vh] gap-5 text-center px-4">
    <div class="rounded-full bg-muted p-5">
        <x-dynamic-component :component="'lucide-' . $icon" class="size-10 text-muted-foreground" />
    </div>
    <div class="space-y-2 max-w-xs">
        <x-ui.badge variant="secondary">En desarrollo</x-ui.badge>
        <h2 class="text-h3 text-foreground">{{ $title }}</h2>
        <p class="text-body-sm text-muted-foreground">{{ $description }}</p>
    </div>
</div>
