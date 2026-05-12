@props([
    'label',
    'value',
    'change',
    'state'      => 'success',
    'chartData'  => null,
    'chartOpts'  => null,
])

<x-ui.card>
    <x-ui.card.content class="p-5 space-y-3">
        <x-ui.typography as="muted" class="text-sm font-medium">{{ $label }}</x-ui.typography>
        <div class="flex items-end justify-between">
            <x-ui.typography as="h3" class="text-2xl font-bold tracking-tight">{{ $value }}</x-ui.typography>
            <x-ui.badge :state="$state" :subtle="true">{{ $change }}</x-ui.badge>
        </div>
        @if($chartData)
            <x-ui.chart
                type="line"
                :data="$chartData"
                :options="$chartOpts ?? []"
                height="48px"
            />
        @endif
    </x-ui.card.content>
</x-ui.card>
