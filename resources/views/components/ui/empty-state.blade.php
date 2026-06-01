@props([
    'title'       => 'Sin resultados',
    'description' => null,
    'icon'        => null,    // nombre de ícono de x-ui.icon (opcional)
])

<x-ui.card {{ $attributes->twMerge('flex flex-col items-center justify-center gap-3 p-10 text-center') }}>
    @if($icon)
        <div class="flex size-12 items-center justify-center rounded-full bg-primary/10 text-primary">
            <x-dynamic-component :component="'lucide-' . $icon" class="size-6" />
        </div>
    @endif

    <div class="space-y-1">
        <x-ui.typography as="small" element="p">{{ $title }}</x-ui.typography>
        @if($description)
            <x-ui.typography as="muted" class="max-w-96">{{ $description }}</x-ui.typography>
        @endif
    </div>

    @if($slot->isNotEmpty())
        <div class="mt-1">{{ $slot }}</div>
    @endif
</x-ui.card>
