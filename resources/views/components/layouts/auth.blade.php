@props([
    'title'           => 'Ingresar',
    'cardTitle'       => '',
    'cardDescription' => null,
    'bare'            => false,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data>
<head>
    <x-layouts.head :title="$title" />
</head>
<body class="min-h-screen bg-background text-foreground antialiased">

    <div class="absolute top-4 right-4">
        <x-ui.button size="icon" variant="ghost" @click="$store.theme.toggle()"
            class="size-8 text-muted-foreground">
            <x-lucide-sun x-show="!$store.theme.dark" class="size-4" />
            <x-lucide-moon x-show="$store.theme.dark" x-cloak class="size-4" />
        </x-ui.button>
    </div>

    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="w-full max-w-100 space-y-8">

            {{-- Brand --}}
            <div class="flex flex-col items-center gap-4">
                <div class="flex items-center justify-center size-14 rounded-xl bg-primary shrink-0">
                    <span class="text-xl font-bold text-primary-foreground leading-none">IR</span>
                </div>
                <div class="text-center space-y-1">
                    <x-ui.typography as="h3" element="h1">Infinito Reciclaje</x-ui.typography>
                    @if (!empty($organizacion))
                        <x-ui.typography as="muted">{{ $organizacion->nombre }}</x-ui.typography>
                    @elseif (!empty($es_super_admin_context))
                        <x-ui.typography as="muted">Panel de administración</x-ui.typography>
                    @else
                        <x-ui.typography as="muted">Sistema de gestión de balanza</x-ui.typography>
                    @endif
                </div>
            </div>

            {{-- Card --}}
            @if ($bare)
                {{ $slot }}
            @else
                <x-ui.card>
                    <x-ui.card.header class="text-center">
                        <x-ui.card.title>{{ $cardTitle }}</x-ui.card.title>
                        @if ($cardDescription)
                            <x-ui.card.description>{{ $cardDescription }}</x-ui.card.description>
                        @endif
                    </x-ui.card.header>
                    <x-ui.card.content>
                        {{ $slot }}
                    </x-ui.card.content>
                </x-ui.card>
            @endif

            {{-- Footer link (optional named slot) --}}
            @isset($footerLink)
                <div class="flex justify-center">
                    {{ $footerLink }}
                </div>
            @endisset

            <x-ui.typography as="muted" class="text-center text-xs">
                Balanza v1 · Gestión de pesajes
            </x-ui.typography>

        </div>
    </div>

    <x-ui.sonner />
</body>
</html>
