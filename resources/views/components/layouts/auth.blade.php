@props(['title' => 'Ingresar'])

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
        {{ $slot }}
    </div>

    <x-ui.sonner />
</body>
</html>
