<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>

    {{-- Evita flash de tema incorrecto al cargar --}}
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
<body class="min-h-screen bg-background text-foreground antialiased">

    {{-- Topbar --}}
    <header class="sticky top-0 z-(--z-sticky) border-b border-border bg-background/80 backdrop-blur-sm">
        <div class="mx-auto max-w-[--container-xl] px-4 sm:px-6 lg:px-8">
            <div class="flex h-14 items-center justify-between gap-4">

                {{-- Logo / Brand --}}
                <a href="/" class="flex items-center gap-2 font-semibold text-foreground">
                    {{ config('app.name') }}
                </a>

                {{-- Nav slot --}}
                @isset($nav)
                    <nav class="hidden md:flex items-center gap-1">
                        {{ $nav }}
                    </nav>
                @endisset

                {{-- Actions: tema + slot de acciones --}}
                <div class="flex items-center gap-2">
                    @isset($actions)
                        {{ $actions }}
                    @endisset

                    {{-- Toggle de tema --}}
                    <button
                        @click="$store.theme.toggle()"
                        class="inline-flex items-center justify-center size-9 rounded-md text-muted-foreground hover:text-foreground hover:bg-accent transition-colors"
                        aria-label="Cambiar tema"
                    >
                        {{-- Sol (light mode) --}}
                        <svg x-show="!$store.theme.dark" xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
                        </svg>
                        {{-- Luna (dark mode) --}}
                        <svg x-show="$store.theme.dark" xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        </svg>
                    </button>
                </div>

            </div>
        </div>
    </header>

    {{-- Contenido principal --}}
    <main>
        {{ $slot }}
    </main>

    {{-- Footer slot opcional --}}
    @isset($footer)
        <footer class="border-t border-border mt-16">
            <div class="mx-auto max-w-[--container-xl] px-4 sm:px-6 lg:px-8 py-8">
                {{ $footer }}
            </div>
        </footer>
    @endisset

</body>
</html>
