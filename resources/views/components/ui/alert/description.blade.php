<x-ui.typography as="p" {{ $attributes->twMerge('[&_p]:leading-relaxed [&:not(:first-child)]:mt-0') }}>
    {{ $slot }}
</x-ui.typography>
