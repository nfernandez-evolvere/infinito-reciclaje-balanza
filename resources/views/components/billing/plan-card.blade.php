@props([
    'name',
    'price',
    'period'    => 'mes',
    'features'  => [],
    'popular'   => false,
    'current'   => false,
])

<x-ui.card class="{{ $popular ? 'border-primary ring-1 ring-primary' : '' }}">
    <x-ui.card.header>
        <div class="flex items-center justify-between">
            <x-ui.card.title>{{ $name }}</x-ui.card.title>
            @if($popular)
                <x-ui.badge>Popular</x-ui.badge>
            @endif
            @if($current)
                <x-ui.badge state="success" :subtle="true">Plan actual</x-ui.badge>
            @endif
        </div>
        <div class="flex items-baseline gap-1 pt-1">
            <span class="text-3xl font-bold text-foreground">{{ $price }}</span>
            <x-ui.typography as="muted" class="text-sm">/ {{ $period }}</x-ui.typography>
        </div>
    </x-ui.card.header>
    <x-ui.card.content class="space-y-3">
        <ul class="space-y-2">
            @foreach($features as $feature)
            <li class="flex items-start gap-2 text-sm">
                <x-lucide-check class="size-4 shrink-0 text-success mt-0.5" />
                <span class="text-foreground">{{ $feature }}</span>
            </li>
            @endforeach
        </ul>
    </x-ui.card.content>
    <x-ui.card.footer>
        @if($current)
            <x-ui.button variant="outline" class="w-full" disabled>Plan actual</x-ui.button>
        @else
            <x-ui.button class="w-full" variant="{{ $popular ? 'default' : 'outline' }}">
                Seleccionar {{ $name }}
            </x-ui.button>
        @endif
    </x-ui.card.footer>
</x-ui.card>
