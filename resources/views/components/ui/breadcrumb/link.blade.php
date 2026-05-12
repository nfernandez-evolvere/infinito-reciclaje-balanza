@props(['href' => '#'])

<a href="{{ $href }}" {{ $attributes->twMerge('transition-colors hover:text-foreground') }}>
    {{ $slot }}
</a>
