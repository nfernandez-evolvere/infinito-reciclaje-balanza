@php
$activities = [
    ['icon' => 'git-commit-horizontal', 'text' => 'Creó el proyecto <strong>Rediseño UI v2</strong>',                  'time' => 'hace 20 min', 'type' => 'create'],
    ['icon' => 'message-square',        'text' => 'Comentó en <strong>Dashboard — Sprint 3</strong>',                  'time' => 'hace 1 h',   'type' => 'comment'],
    ['icon' => 'at-sign',               'text' => 'Mencionó a <mention>Carlos López</mention> en un comentario',       'time' => 'hace 3 h',   'type' => 'mention'],
    ['icon' => 'check-circle',          'text' => 'Completó la tarea <strong>Migrar tokens de color</strong>',         'time' => 'ayer',        'type' => 'complete'],
    ['icon' => 'user-plus',             'text' => 'Agregó a <mention>Ana Rodríguez</mention> al equipo',               'time' => 'hace 2 días', 'type' => 'team'],
    ['icon' => 'settings',              'text' => 'Actualizó la configuración de notificaciones',                      'time' => 'hace 3 días', 'type' => 'settings'],
];
@endphp

<div class="space-y-1 pt-4">
    @foreach($activities as $activity)
    <div class="flex items-start gap-4 py-3 border-b border-border last:border-0">
        <div class="size-8 rounded-full bg-muted flex items-center justify-center shrink-0 mt-0.5">
            <x-dynamic-component :component="'lucide-' . $activity['icon']" class="size-3.5 text-muted-foreground" />
        </div>
        <div class="flex-1 min-w-0 space-y-1">
            <p class="text-sm text-foreground leading-relaxed">
                @php
                    // Reemplaza <mention>Nombre</mention> por el hover-card
                    $text = $activity['text'];
                    preg_match_all('/<mention>(.*?)<\/mention>/', $text, $matches);
                @endphp
                @if(!empty($matches[1]))
                    @php
                        $parts = preg_split('/(<mention>.*?<\/mention>)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
                    @endphp
                    @foreach($parts as $part)
                        @if(preg_match('/<mention>(.*?)<\/mention>/', $part, $m))
                            <x-ui.hover-card>
                                <x-ui.hover-card.trigger>
                                    <span class="font-medium text-primary cursor-pointer hover:underline underline-offset-2">{{ $m[1] }}</span>
                                </x-ui.hover-card.trigger>
                                <x-ui.hover-card.content>
                                    <div class="flex items-center gap-3">
                                        <x-ui.avatar :alt="$m[1]" size="sm" class="shrink-0" />
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-foreground">{{ $m[1] }}</p>
                                            <p class="text-xs text-muted-foreground">Editor · equipo@ejemplo.com</p>
                                        </div>
                                    </div>
                                    <x-ui.separator class="my-2" />
                                    <div class="flex gap-4 text-xs text-muted-foreground">
                                        <span><span class="font-medium text-foreground">12</span> proyectos</span>
                                        <span><span class="font-medium text-foreground">84</span> tareas</span>
                                    </div>
                                </x-ui.hover-card.content>
                            </x-ui.hover-card>
                        @else
                            {!! $part !!}
                        @endif
                    @endforeach
                @else
                    {!! $text !!}
                @endif
            </p>
            <x-ui.typography as="muted" class="text-xs">{{ $activity['time'] }}</x-ui.typography>
        </div>
    </div>
    @endforeach
</div>
