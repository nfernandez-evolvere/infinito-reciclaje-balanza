@props(['label', 'value', 'icon' => null])

<x-ui.card>
    <x-ui.card.content class="p-5 text-center space-y-1">
        <x-ui.typography as="h3" class="text-2xl font-bold tracking-tight">{{ $value }}</x-ui.typography>
        <x-ui.typography as="muted" class="text-xs">{{ $label }}</x-ui.typography>
    </x-ui.card.content>
</x-ui.card>
