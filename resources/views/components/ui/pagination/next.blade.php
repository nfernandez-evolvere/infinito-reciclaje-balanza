@props(['href' => null, 'disabled' => false])

@if($href && !$disabled)
    <x-ui.button variant="ghost" size="sm" :href="$href" aria-label="Página siguiente">
        <x-lucide-chevron-right class="size-4" />
    </x-ui.button>
@else
    <x-ui.button variant="ghost" size="sm" disabled aria-label="Página siguiente">
        <x-lucide-chevron-right class="size-4" />
    </x-ui.button>
@endif
