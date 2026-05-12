<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>

    <script>
        (function() {
            const saved = localStorage.getItem('theme')
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
            if (saved === 'dark' || (!saved && prefersDark)) {
                document.documentElement.classList.add('dark')
            }
        })()
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-background text-foreground antialiased" data-density="comfortable">

    {{-- Theme toggle --}}
    <div class="fixed top-4 right-4 z-50">
        <button
            @click="$store.theme.toggle()"
            class="inline-flex items-center justify-center size-9 rounded-md text-muted-foreground hover:text-foreground hover:bg-accent transition-colors"
            aria-label="Cambiar tema"
        >
            <svg x-show="!$store.theme.dark" xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
            </svg>
            <svg x-show="$store.theme.dark" x-cloak xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
        </button>
    </div>

    {{-- Contenido centrado --}}
    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-16 sm:px-6">

        {{-- Logo --}}
        <div class="mb-8">
            <a href="/showcase" class="flex items-center gap-2.5 group">
                <div class="size-8 rounded-lg bg-primary flex items-center justify-center shrink-0">
                    <span class="text-sm font-bold text-primary-foreground leading-none">DS</span>
                </div>
                <span class="text-lg font-semibold text-foreground group-hover:text-primary transition-colors">
                    Design System
                </span>
            </a>
        </div>

        {{-- Contenido de la página --}}
        <div class="w-full max-w-sm">
            {{ $slot }}
        </div>

        {{-- Footer --}}
        <div class="mt-8 flex items-center gap-4">
            <a href="#" class="text-xs text-muted-foreground hover:text-foreground transition-colors">Términos</a>
            <x-ui.separator orientation="vertical" class="h-3" />
            <a href="#" class="text-xs text-muted-foreground hover:text-foreground transition-colors">Privacidad</a>
            <x-ui.separator orientation="vertical" class="h-3" />
            <a href="/showcase" class="text-xs text-muted-foreground hover:text-foreground transition-colors">← Showcase</a>
        </div>

    </div>

    <x-ui.sonner />

</body>
</html>
