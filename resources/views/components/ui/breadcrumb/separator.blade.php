<li role="presentation" aria-hidden="true" {{ $attributes->twMerge('[&>svg]:size-3.5') }}>
    @if($slot->isNotEmpty())
        {{ $slot }}
    @else
        <x-lucide-chevron-right class="size-3.5" />
    @endif
</li>
