@props([
    'title'           => 'Ingresar',
    'cardTitle'       => '',
    'cardDescription' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data>
<head>
    <x-layouts.head :title="$title" />
</head>
<body class="flex min-h-svh flex-col bg-muted text-foreground antialiased">

    {{-- Navbar --}}
    <header class="sticky top-0 z-40 border-b border-border bg-background/90 backdrop-blur-sm">
        <div class="mx-auto flex h-14 max-w-6xl items-center justify-between px-4 sm:px-6">
            <a href="{{ route('landing') }}" class="flex items-center gap-2.5">
                <div class="flex size-7 shrink-0 items-center justify-center rounded-md bg-primary">
                    <span class="text-[11px] font-bold leading-none text-primary-foreground">IR</span>
                </div>
                <span class="text-sm font-semibold">Infinito Reciclaje</span>
            </a>
            <x-ui.button variant="ghost" size="icon" @click="$store.theme.toggle()" aria-label="Cambiar tema">
                <x-lucide-sun x-show="!$store.theme.dark" class="size-4" />
                <x-lucide-moon x-show="$store.theme.dark" x-cloak class="size-4" />
            </x-ui.button>
        </div>
    </header>

    <div class="flex flex-1 items-center justify-center p-6 md:p-10">
        <div class="w-full max-w-sm md:max-w-4xl">

            {{-- Two-column card --}}
            <div class="overflow-hidden rounded-xl border border-border bg-card text-card-foreground shadow-sm">
                <div class="grid md:grid-cols-2">

                    {{-- Left: form panel --}}
                    <div class="flex flex-col gap-6 p-8 md:p-10">

                        {{-- Brand --}}
{{--                         <div class="flex items-center gap-3">
                            <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary">
                                <span class="text-sm font-bold leading-none text-primary-foreground">IR</span>
                            </div>
                            <span class="font-semibold text-sm">Infinito Reciclaje</span>
                        </div> --}}

                        {{-- Title & description --}}
                        <div>
                            <x-ui.typography as="h3" element="h1">{{ $cardTitle }}</x-ui.typography>
                            @if ($cardDescription)
                                <x-ui.typography as="muted" class="mt-1">{{ $cardDescription }}</x-ui.typography>
                            @endif
                        </div>

                        {{-- Form slot --}}
                        <div>
                            {{ $slot }}
                        </div>

                        {{-- Footer link (back to login, etc.) --}}
                        @isset($footerLink)
                            <div class="flex justify-center">
                                {{ $footerLink }}
                            </div>
                        @endisset

                    </div>

                    {{-- Right: branded panel (hidden on mobile) --}}
                    <div class="relative hidden flex-col items-center justify-center overflow-hidden bg-primary p-10 text-primary-foreground md:flex">

                        {{-- Decorative circles --}}
                        <div class="absolute -top-24 -right-24 size-72 rounded-full bg-white/5"></div>
                        <div class="absolute -bottom-24 -left-24 size-80 rounded-full bg-white/5"></div>
                        <div class="absolute top-1/2 right-0 size-40 -translate-y-1/2 translate-x-1/2 rounded-full bg-white/5"></div>

                        {{-- Brand mark --}}
                        <div class="relative z-10 flex flex-col items-center gap-6 text-center">
                            <div class="flex size-16 items-center justify-center rounded-2xl bg-white/15 backdrop-blur-sm">
                                <span class="text-2xl font-bold">IR</span>
                            </div>
                            <div class="space-y-1.5">
                                <p class="text-lg font-semibold">Infinito Reciclaje</p>
                                <p class="text-sm opacity-60">Sistema de gestión de balanza</p>
                            </div>
                            <div class="mt-2 max-w-55 space-y-1">
                                <p class="text-sm opacity-50 leading-relaxed">
                                    Control preciso del pesaje.<br>
                                    Trazabilidad completa del reciclaje.
                                </p>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

            {{-- Footer --}}
            <p class="mt-4 text-center text-xs text-muted-foreground">
                Balanza v1 · Gestión de pesajes
            </p>

        </div>
    </div>

    <x-ui.sonner />
</body>
</html>
