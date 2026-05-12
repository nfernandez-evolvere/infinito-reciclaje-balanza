@props([
    'as' => 'div',
])

<!-- <{{ $as }} {{ $attributes->twMerge('rounded-xl border border-border bg-card text-card-foreground shadow-sm') }}> -->
<{{ $as }} {{ $attributes->twMerge('rounded-xl bg-card text-card-foreground shadow-lg') }}>
    {{ $slot }}
</{{ $as }}>
