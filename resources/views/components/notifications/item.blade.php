@props([
    'sender'   => null,    // nombre del emisor (null = sistema)
    'icon'     => 'bell',  // ícono para notificaciones de sistema
    'text',                // texto de la notificación
    'time',
    'read'     => false,
])

<div class="flex items-start gap-3 px-6 py-4 border-b border-border last:border-0 hover:bg-muted/30 transition-colors group">

    {{-- Avatar o ícono --}}
    @if($sender)
        <x-ui.avatar :alt="$sender" size="sm" class="shrink-0 mt-0.5" />
    @else
        <div class="size-8 rounded-full bg-muted flex items-center justify-center shrink-0 mt-0.5">
            <x-dynamic-component :component="'lucide-' . $icon" class="size-3.5 text-muted-foreground" />
        </div>
    @endif

    {{-- Contenido --}}
    <div class="flex-1 min-w-0 space-y-1">
        <p class="text-sm text-foreground leading-relaxed">{!! $text !!}</p>
        <x-ui.typography as="muted" class="text-xs">{{ $time }}</x-ui.typography>
    </div>

    {{-- Indicador de no leído + acciones en hover --}}
    <div class="flex items-center gap-2 shrink-0">
        <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <x-ui.button variant="ghost" size="icon" class="size-7 text-muted-foreground">
                <x-lucide-check class="size-3.5" />
            </x-ui.button>
            <x-ui.button variant="ghost" size="icon" class="size-7 text-muted-foreground">
                <x-lucide-archive class="size-3.5" />
            </x-ui.button>
        </div>
        @if(!$read)
            <div class="size-2 rounded-full bg-primary shrink-0"></div>
        @endif
    </div>

</div>
