{{-- Main content area alongside the sidebar --}}
<main
    {{ $attributes->twMerge('relative flex min-h-svh flex-1 flex-col overflow-auto bg-background') }}
>
    {{ $slot }}
</main>
