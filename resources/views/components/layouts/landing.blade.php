@props(['title' => 'Balanza'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data>
<head>
    <x-layouts.head :title="$title" />
    {{ $head ?? '' }}
</head>
<body class="min-h-svh bg-background text-foreground antialiased">
    {{ $slot }}
    <x-ui.sonner />
    {{ $scripts ?? '' }}
</body>
</html>
