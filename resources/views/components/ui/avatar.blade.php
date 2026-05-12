@props([
    'src'      => null,
    'alt'      => '',
    'fallback' => null,   // Texto de fallback (ej: "JD"). Si no, se infiere del alt.
    'size'     => 'md',   // sm | md | lg | xl
])

@php
$sizeClass = match($size) {
    'sm'  => 'size-8 text-xs',
    'lg'  => 'size-14 text-base',
    'xl'  => 'size-20 text-xl',
    default => 'size-10 text-sm',
};

// Genera iniciales desde el alt si no se provee fallback
$initials = $fallback ?? collect(explode(' ', trim($alt)))
    ->filter()
    ->take(2)
    ->map(fn($word) => strtoupper($word[0]))
    ->implode('');
@endphp

{{--
    Uso todo-en-uno (API original):
        <x-ui.avatar src="..." alt="Juan Doe" size="sm" />

    Uso compositivo (API shadcn):
        <x-ui.avatar size="sm">
            <x-ui.avatar.image src="..." alt="..." />
            <x-ui.avatar.fallback>JD</x-ui.avatar.fallback>
        </x-ui.avatar>
--}}
<span {{ $attributes->twMerge('relative flex shrink-0 overflow-hidden rounded-full', $sizeClass) }}>
    @if($slot->isNotEmpty())
        {{-- API compositiva --}}
        {{ $slot }}
    @else
        {{-- API todo-en-uno (backward compatible) --}}
        @if($src)
            <img
                src="{{ $src }}"
                alt="{{ $alt }}"
                class="aspect-square h-full w-full object-cover"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
            />
        @endif
        <span
            class="flex h-full w-full items-center justify-center rounded-full bg-muted font-medium text-muted-foreground {{ $src ? 'hidden' : '' }}"
            aria-hidden="{{ $src ? 'false' : 'true' }}"
        >
            {{ $initials ?: '?' }}
        </span>
    @endif
</span>
