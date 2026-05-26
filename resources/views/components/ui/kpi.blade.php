@props([
    'title',
    'icon'  => null,
    'help'  => null,
])

<x-ui.card variant="elevated" class="flex-row items-center gap-3 sm:gap-4 p-3 sm:p-5">
    @if($icon)
        <x-dynamic-component :component="'lucide-' . $icon" class="size-10 sm:size-14 text-primary p-2 bg-primary/10 rounded-lg" />
    @endif
    <div class="flex flex-col gap-1.5 flex-1 w-full">
        <x-ui.card.header class="w-full justify-between items-center">
            <div class="flex items-center gap-1.5">
                <x-ui.card.title class="text-overline">{{ $title }}</x-ui.card.title>
            </div>
            @if($help)
                <x-slot:actions>
                    <x-ui.popover side="bottom" align="end">
                        <x-slot:trigger>
                            <x-ui.button variant="ghost" size="icon" class="size-6 bg-transparent hover:bg-primary/20 transition-colors">
                                <x-lucide-circle-help class="size-3" />
                            </x-ui.button>
                        </x-slot:trigger>
                        <p class="text-body-sm text-muted-foreground max-w-48">{{ $help }}</p>
                    </x-ui.popover>
                </x-slot:actions>
            @endif
        </x-ui.card.header>
        <x-ui.card.content class="pt-0">
            <div class="text-2xl sm:text-3xl font-bold">{{ $slot }}</div>
        </x-ui.card.content>
    </div>
</x-ui.card>
