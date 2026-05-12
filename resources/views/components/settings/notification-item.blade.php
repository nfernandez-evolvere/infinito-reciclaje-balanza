@props([
    'label',
    'description' => null,
    'name'        => null,
    'checked'     => false,
])

<div class="flex items-center justify-between gap-4 py-3">
    <div class="space-y-1 min-w-0">
        <x-ui.typography as="small" element="p">{{ $label }}</x-ui.typography>
        @if($description)
            <x-ui.typography as="muted" class="text-xs">{{ $description }}</x-ui.typography>
        @endif
    </div>
    <x-ui.switch :name="$name" :checked="$checked" class="shrink-0" />
</div>
