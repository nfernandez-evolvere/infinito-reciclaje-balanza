<x-layouts.app-sidebar title="María García">

    <x-slot:breadcrumb>
        <x-ui.breadcrumb>
            <x-ui.breadcrumb.list>
                <x-ui.breadcrumb.item>
                    <x-ui.breadcrumb.link href="/examples/members">Miembros</x-ui.breadcrumb.link>
                </x-ui.breadcrumb.item>
                <x-ui.breadcrumb.separator />
                <x-ui.breadcrumb.item>
                    <x-ui.breadcrumb.page>María García</x-ui.breadcrumb.page>
                </x-ui.breadcrumb.item>
            </x-ui.breadcrumb.list>
        </x-ui.breadcrumb>
    </x-slot:breadcrumb>

    <div class="max-w-[--container-xl] px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <div class="max-w-3xl space-y-6">

        {{-- Hero --}}
        <div class="flex flex-col sm:flex-row items-start gap-5">
            <x-ui.avatar alt="María García" size="xl" class="shrink-0" />
            <div class="flex-1 min-w-0 space-y-3">
                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-ui.typography as="h1">María García</x-ui.typography>
                        <x-ui.badge state="info" :subtle="true">Administrador</x-ui.badge>
                        <x-ui.badge state="success" :subtle="true">Activo</x-ui.badge>
                    </div>
                    <x-ui.typography as="muted">Diseñadora de producto · maria@ejemplo.com</x-ui.typography>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <x-ui.button-group>
                        <x-ui.button size="sm">
                            <x-lucide-pencil class="size-4" />
                            Editar
                        </x-ui.button>
                        <x-ui.button size="sm" variant="outline">
                            <x-lucide-send class="size-4" />
                            Mensaje
                        </x-ui.button>
                    </x-ui.button-group>
                    <x-ui.dropdown-menu>
                        <x-ui.dropdown-menu.trigger>
                            <x-ui.button size="sm" variant="outline">
                                <x-lucide-more-horizontal class="size-4" />
                            </x-ui.button>
                        </x-ui.dropdown-menu.trigger>
                        <x-ui.dropdown-menu.content align="end">
                            <x-ui.dropdown-menu.item>
                                <x-lucide-copy class="size-4" /> Copiar email
                            </x-ui.dropdown-menu.item>
                            <x-ui.dropdown-menu.item>
                                <x-lucide-download class="size-4" /> Exportar datos
                            </x-ui.dropdown-menu.item>
                            <x-ui.dropdown-menu.separator />
                            <x-ui.dropdown-menu.item variant="destructive">
                                <x-lucide-user-x class="size-4" /> Suspender cuenta
                            </x-ui.dropdown-menu.item>
                        </x-ui.dropdown-menu.content>
                    </x-ui.dropdown-menu>
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-4">
            <x-users.stat-card label="Proyectos activos" value="7" />
            <x-users.stat-card label="Tareas completadas" value="143" />
            <x-users.stat-card label="Días en el equipo" value="142" />
        </div>

        {{-- Tabs --}}
        <x-ui.tabs value="activity">
            <x-ui.tabs.list>
                <x-ui.tabs.trigger value="activity">Actividad</x-ui.tabs.trigger>
                <x-ui.tabs.trigger value="permissions">Permisos</x-ui.tabs.trigger>
                <x-ui.tabs.trigger value="devices">Dispositivos</x-ui.tabs.trigger>
            </x-ui.tabs.list>

            <x-ui.tabs.content value="activity">
                @include('users._tab-activity')
            </x-ui.tabs.content>

            <x-ui.tabs.content value="permissions">
                @include('users._tab-permissions')
            </x-ui.tabs.content>

            <x-ui.tabs.content value="devices">
                @php
                $devices = [
                    ['device' => 'MacBook Pro',  'browser' => 'Chrome 125',  'location' => 'Buenos Aires', 'last' => 'Ahora',       'current' => true],
                    ['device' => 'iPhone 15',    'browser' => 'Safari / iOS','location' => 'Buenos Aires', 'last' => 'hace 5 h',    'current' => false],
                    ['device' => 'iPad Pro',     'browser' => 'Safari / iPadOS','location' => 'Rosario',   'last' => 'hace 2 días', 'current' => false],
                ];
                @endphp
                <div class="pt-4">
                    <x-ui.card>
                        <x-ui.card.content class="p-0">
                            <x-ui.table>
                                <x-ui.table.body>
                                    @foreach($devices as $d)
                                    <x-ui.table.row>
                                        <x-ui.table.cell class="pl-6">
                                            <div class="flex items-center gap-3">
                                                <div class="size-8 rounded-md bg-muted flex items-center justify-center shrink-0">
                                                    @if(str_contains($d['device'], 'iPhone') || str_contains($d['device'], 'iPad'))
                                                        <x-lucide-smartphone class="size-4 text-muted-foreground" />
                                                    @else
                                                        <x-lucide-monitor class="size-4 text-muted-foreground" />
                                                    @endif
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-sm font-medium text-foreground">{{ $d['device'] }}</p>
                                                    <p class="text-xs text-muted-foreground">{{ $d['browser'] }} · {{ $d['location'] }}</p>
                                                </div>
                                            </div>
                                        </x-ui.table.cell>
                                        <x-ui.table.cell class="text-right">
                                            @if($d['current'])
                                                <x-ui.badge state="success" :subtle="true">Sesión actual</x-ui.badge>
                                            @else
                                                <x-ui.typography as="muted" class="text-xs">{{ $d['last'] }}</x-ui.typography>
                                            @endif
                                        </x-ui.table.cell>
                                        <x-ui.table.cell class="pr-6 text-right w-24">
                                            @if(!$d['current'])
                                                <x-ui.button variant="ghost" size="sm" state="destructive"
                                                    @click="$store.toast.success('Sesión revocada')">
                                                    Revocar
                                                </x-ui.button>
                                            @endif
                                        </x-ui.table.cell>
                                    </x-ui.table.row>
                                    @endforeach
                                </x-ui.table.body>
                            </x-ui.table>
                        </x-ui.card.content>
                    </x-ui.card>
                </div>
            </x-ui.tabs.content>
        </x-ui.tabs>

        </div>{{-- /max-w-3xl --}}

    </div>

</x-layouts.app-sidebar>
