<x-layouts.app-sidebar title="Notificaciones">

    <x-slot:breadcrumb>
        <x-ui.breadcrumb>
            <x-ui.breadcrumb.list>
                <x-ui.breadcrumb.item>
                    <x-ui.breadcrumb.page>Notificaciones</x-ui.breadcrumb.page>
                </x-ui.breadcrumb.item>
            </x-ui.breadcrumb.list>
        </x-ui.breadcrumb>
    </x-slot:breadcrumb>

    @php
    $all = [
        // Hoy
        ['group' => 'Hoy',          'sender' => 'Carlos López',   'icon' => null,         'text' => '<strong>Carlos López</strong> te mencionó en un comentario.',         'time' => 'hace 5 min',  'read' => false, 'type' => 'mention'],
        ['group' => 'Hoy',          'sender' => 'Ana Rodríguez',  'icon' => null,         'text' => '<strong>Ana Rodríguez</strong> completó la tarea que te asignó.',      'time' => 'hace 1 h',   'read' => false, 'type' => 'activity'],
        ['group' => 'Hoy',          'sender' => null,             'icon' => 'credit-card','text' => 'Pago de <strong>$12.00</strong> procesado correctamente para junio.',    'time' => 'hace 3 h',   'read' => false, 'type' => 'system'],
        // Ayer
        ['group' => 'Ayer',         'sender' => 'Sofía González', 'icon' => null,         'text' => '<strong>Sofía González</strong> te invitó a colaborar en un proyecto.', 'time' => 'ayer 16:42', 'read' => true,  'type' => 'mention'],
        ['group' => 'Ayer',         'sender' => null,             'icon' => 'shield',     'text' => 'Nueva sesión iniciada desde <strong>iPhone 15</strong> en Buenos Aires.','time' => 'ayer 09:15', 'read' => true,  'type' => 'system'],
        // Esta semana
        ['group' => 'Esta semana',  'sender' => 'Martín Torres',  'icon' => null,         'text' => '<strong>Martín Torres</strong> te mencionó en el tablero de diseño.',   'time' => 'lun 11:00',  'read' => true,  'type' => 'mention'],
        ['group' => 'Esta semana',  'sender' => null,             'icon' => 'zap',        'text' => 'Tu plan Pro se renovará automáticamente el <strong>1 de julio</strong>.','time' => 'dom 08:00',  'read' => true,  'type' => 'system'],
    ];

    $unread   = array_filter($all, fn($n) => !$n['read']);
    $mentions = array_filter($all, fn($n) => $n['type'] === 'mention');
    $system   = array_filter($all, fn($n) => $n['type'] === 'system');

    $groups = fn(array $items) => collect($items)->groupBy('group');
    @endphp

    <div class="max-w-[--container-xl] px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <div class="max-w-2xl space-y-6">

        {{-- Page header --}}
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <x-ui.typography as="h1">Notificaciones</x-ui.typography>
                <x-ui.badge state="destructive">{{ count($unread) }}</x-ui.badge>
            </div>
            <div class="flex gap-2">
                <x-ui.button variant="outline" size="sm"
                    @click="$store.toast.success('Todas marcadas como leídas')">
                    Marcar todo leído
                </x-ui.button>
                <x-ui.dropdown-menu align="end">
                    <x-ui.dropdown-menu.trigger>
                        <x-ui.button variant="ghost" size="icon" class="size-9">
                            <x-lucide-more-horizontal class="size-4" />
                        </x-ui.button>
                    </x-ui.dropdown-menu.trigger>
                    <x-ui.dropdown-menu.content>
                        <x-ui.dropdown-menu.item>
                            <x-lucide-archive class="size-4" /> Archivar todas
                        </x-ui.dropdown-menu.item>
                        <x-ui.dropdown-menu.item>
                            <x-lucide-settings class="size-4" /> Preferencias
                        </x-ui.dropdown-menu.item>
                    </x-ui.dropdown-menu.content>
                </x-ui.dropdown-menu>
            </div>
        </div>

        {{-- Tabs --}}
        <x-ui.tabs value="all">
            <x-ui.tabs.list>
                <x-ui.tabs.trigger value="all">
                    Todas
                    <x-ui.badge variant="secondary" class="ml-1">{{ count($all) }}</x-ui.badge>
                </x-ui.tabs.trigger>
                <x-ui.tabs.trigger value="unread">
                    Sin leer
                    <x-ui.badge state="destructive" class="ml-1">{{ count($unread) }}</x-ui.badge>
                </x-ui.tabs.trigger>
                <x-ui.tabs.trigger value="mentions">Menciones</x-ui.tabs.trigger>
                <x-ui.tabs.trigger value="system">Sistema</x-ui.tabs.trigger>
            </x-ui.tabs.list>

            @foreach(['all' => $all, 'unread' => $unread, 'mentions' => $mentions, 'system' => $system] as $tab => $items)
            <x-ui.tabs.content value="{{ $tab }}">
                <x-ui.card class="mt-0 overflow-hidden">
                    @if(count($items) === 0)
                        <div class="p-8">
                            <x-ui.empty-state
                                title="Sin notificaciones"
                                description="No tenés notificaciones en esta categoría."
                                icon="bell"
                            />
                        </div>
                    @else
                        @foreach($groups($items) as $groupName => $groupItems)
                            <div class="px-6 py-2 bg-muted/30 border-b border-border">
                                <x-ui.typography as="section-label" element="p">{{ $groupName }}</x-ui.typography>
                            </div>
                            @foreach($groupItems as $notif)
                                <x-notifications.item
                                    :sender="$notif['sender']"
                                    :icon="$notif['icon'] ?? 'bell'"
                                    :text="$notif['text']"
                                    :time="$notif['time']"
                                    :read="$notif['read']"
                                />
                            @endforeach
                        @endforeach
                    @endif
                </x-ui.card>
            </x-ui.tabs.content>
            @endforeach

        </x-ui.tabs>

        </div>{{-- /max-w-2xl --}}

    </div>

</x-layouts.app-sidebar>
