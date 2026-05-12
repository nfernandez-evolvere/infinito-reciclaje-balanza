<x-layouts.dashboard title="Equipo">

    <x-slot name="breadcrumb">
        <x-breadcrumb>
            <x-breadcrumb.item><x-breadcrumb.link href="/dashboard">Dashboard</x-breadcrumb.link></x-breadcrumb.item>
            <x-breadcrumb.separator />
            <x-breadcrumb.item><x-breadcrumb.page>Equipo</x-breadcrumb.page></x-breadcrumb.item>
        </x-breadcrumb>
    </x-slot>

    @php
        $team = [
            ['name'=>'Nicolás Ramírez', 'avatar'=>'NR','role'=>'Administrador','email'=>'nicolas@techstore.com',  'status'=>'activo',   'last'=>'Hace 5 min',  'since'=>'Ene 2022','you'=>true],
            ['name'=>'Valentina Cruz',  'avatar'=>'VC','role'=>'Administrador','email'=>'vale@techstore.com',     'status'=>'activo',   'last'=>'Hace 1 h',    'since'=>'Mar 2022','you'=>false],
            ['name'=>'Mateo González',  'avatar'=>'MG','role'=>'Editor',       'email'=>'mateo@techstore.com',    'status'=>'activo',   'last'=>'Hace 2 h',    'since'=>'Jun 2023','you'=>false],
            ['name'=>'Lucía Fernández', 'avatar'=>'LF','role'=>'Editor',       'email'=>'lucia@techstore.com',    'status'=>'activo',   'last'=>'Ayer',        'since'=>'Ago 2023','you'=>false],
            ['name'=>'Sebastián Moreno','avatar'=>'SM','role'=>'Soporte',      'email'=>'sebas@techstore.com',    'status'=>'activo',   'last'=>'Hace 3 días', 'since'=>'Feb 2024','you'=>false],
            ['name'=>'Agustina Pérez',  'avatar'=>'AP','role'=>'Editor',       'email'=>'agus@techstore.com',     'status'=>'inactivo', 'last'=>'Hace 2 sem',  'since'=>'Abr 2024','you'=>false],
            ['name'=>'Tomás Acosta',    'avatar'=>'TA','role'=>'Soporte',      'email'=>'tomas@techstore.com',    'status'=>'activo',   'last'=>'Hoy',         'since'=>'Nov 2024','you'=>false],
            ['name'=>'Camila Ramos',    'avatar'=>'CR','role'=>'Viewer',       'email'=>'camila@techstore.com',   'status'=>'pendiente','last'=>'—',           'since'=>'May 2026','you'=>false],
        ];

        $roleBadge   = ['Administrador'=>'default','Editor'=>'secondary','Soporte'=>'outline','Viewer'=>'secondary'];
        $statusBadge = ['activo'=>'success','inactivo'=>'secondary','pendiente'=>'warning'];
        $statusLabel = ['activo'=>'Activo','inactivo'=>'Inactivo','pendiente'=>'Invitación pendiente'];

        $counts = [
            'total'     => count($team),
            'activos'   => count(array_filter($team, fn($m) => $m['status'] === 'activo')),
            'admins'    => count(array_filter($team, fn($m) => $m['role'] === 'Administrador')),
            'pendientes'=> count(array_filter($team, fn($m) => $m['status'] === 'pendiente')),
        ];
    @endphp

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Equipo</h1>
                <p class="text-muted-foreground text-sm">Gestioná los miembros y sus permisos de acceso.</p>
            </div>
            <x-dialog>
                <x-dialog.trigger>
                    <x-button size="sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Invitar miembro
                    </x-button>
                </x-dialog.trigger>
                <x-dialog.content>
                    <x-dialog.header>
                        <x-dialog.title>Invitar nuevo miembro</x-dialog.title>
                        <x-dialog.description>El usuario recibirá un email para crear su cuenta.</x-dialog.description>
                    </x-dialog.header>
                    <div class="space-y-4 py-2">
                        <div class="space-y-1.5">
                            <x-label>Email</x-label>
                            <x-input type="email" placeholder="colaborador@empresa.com" />
                        </div>
                        <div class="space-y-1.5">
                            <x-label>Rol</x-label>
                            <x-select>
                                <option>Editor</option>
                                <option>Soporte</option>
                                <option>Viewer</option>
                                <option>Administrador</option>
                            </x-select>
                            <p class="text-xs text-muted-foreground">
                                <strong>Editor:</strong> puede crear y editar contenido.
                                <strong>Soporte:</strong> solo gestión de pedidos y clientes.
                                <strong>Viewer:</strong> acceso de solo lectura.
                            </p>
                        </div>
                        <div class="space-y-1.5">
                            <x-label>Mensaje personalizado <span class="text-muted-foreground font-normal">(opcional)</span></x-label>
                            <x-textarea placeholder="Hola, te invito a unirte a nuestro equipo en TechStore..." rows="3" />
                        </div>
                    </div>
                    <x-dialog.footer>
                        <x-button variant="outline" @click="open = false">Cancelar</x-button>
                        <x-button @click="open = false; $dispatch('toast', { message: 'Invitación enviada correctamente', variant: 'success' })">
                            Enviar invitación
                        </x-button>
                    </x-dialog.footer>
                </x-dialog.content>
            </x-dialog>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
            <x-card>
                <x-card.content class="pt-6">
                    <p class="text-sm font-medium text-muted-foreground">Total miembros</p>
                    <p class="text-3xl font-bold mt-1">{{ $counts['total'] }}</p>
                    <p class="text-xs text-muted-foreground mt-1">en el equipo</p>
                </x-card.content>
            </x-card>
            <x-card>
                <x-card.content class="pt-6">
                    <p class="text-sm font-medium text-muted-foreground">Activos</p>
                    <p class="text-3xl font-bold mt-1">{{ $counts['activos'] }}</p>
                    <p class="text-xs text-muted-foreground mt-1">con acceso activo</p>
                </x-card.content>
            </x-card>
            <x-card>
                <x-card.content class="pt-6">
                    <p class="text-sm font-medium text-muted-foreground">Administradores</p>
                    <p class="text-3xl font-bold mt-1">{{ $counts['admins'] }}</p>
                    <p class="text-xs text-muted-foreground mt-1">acceso completo</p>
                </x-card.content>
            </x-card>
            <x-card>
                <x-card.content class="pt-6">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-muted-foreground">Invitaciones</p>
                        @if($counts['pendientes'] > 0)<x-badge variant="warning">Pendiente</x-badge>@endif
                    </div>
                    <p class="text-3xl font-bold mt-1">{{ $counts['pendientes'] }}</p>
                    <p class="text-xs text-muted-foreground mt-1">sin aceptar</p>
                </x-card.content>
            </x-card>
        </div>

        {{-- Tabla de miembros --}}
        <x-card>
            <x-card.header>
                <div class="flex items-center justify-between">
                    <div>
                        <x-card.title>Miembros del equipo</x-card.title>
                        <x-card.description>{{ $counts['total'] }} miembros · roles y permisos</x-card.description>
                    </div>
                    <x-input placeholder="Buscar miembro..." class="h-8 w-48 text-xs" />
                </div>
            </x-card.header>
            <x-card.content class="p-0">
                <x-table>
                    <x-table.header>
                        <x-table.row>
                            <x-table.head>Miembro</x-table.head>
                            <x-table.head>Rol</x-table.head>
                            <x-table.head>Estado</x-table.head>
                            <x-table.head class="hidden md:table-cell">Última actividad</x-table.head>
                            <x-table.head class="hidden lg:table-cell">Miembro desde</x-table.head>
                            <x-table.head class="text-right">Acciones</x-table.head>
                        </x-table.row>
                    </x-table.header>
                    <x-table.body>
                        @foreach($team as $member)
                        <x-table.row>
                            <x-table.cell>
                                <div class="flex items-center gap-3">
                                    <x-avatar fallback="{{ $member['avatar'] }}" class="h-9 w-9 text-xs" />
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-medium">{{ $member['name'] }}</p>
                                            @if($member['you'])<x-badge variant="outline" class="text-xs px-1.5">Vos</x-badge>@endif
                                        </div>
                                        <p class="text-xs text-muted-foreground">{{ $member['email'] }}</p>
                                    </div>
                                </div>
                            </x-table.cell>
                            <x-table.cell>
                                <x-badge variant="{{ $roleBadge[$member['role']] }}">{{ $member['role'] }}</x-badge>
                            </x-table.cell>
                            <x-table.cell>
                                <div class="flex items-center gap-1.5">
                                    <span class="h-2 w-2 rounded-full {{ $member['status'] === 'activo' ? 'bg-success' : ($member['status'] === 'pendiente' ? 'bg-warning' : 'bg-muted-foreground') }}"></span>
                                    <span class="text-sm">{{ $statusLabel[$member['status']] }}</span>
                                </div>
                            </x-table.cell>
                            <x-table.cell class="hidden md:table-cell text-sm text-muted-foreground">{{ $member['last'] }}</x-table.cell>
                            <x-table.cell class="hidden lg:table-cell text-sm text-muted-foreground">{{ $member['since'] }}</x-table.cell>
                            <x-table.cell class="text-right">
                                @if(!$member['you'])
                                <x-dropdown-menu>
                                    <x-dropdown-menu.trigger>
                                        <x-button variant="ghost" size="icon" class="h-8 w-8">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
                                        </x-button>
                                    </x-dropdown-menu.trigger>
                                    <x-dropdown-menu.content align="end">
                                        <x-dropdown-menu.label>{{ $member['name'] }}</x-dropdown-menu.label>
                                        <x-dropdown-menu.separator />
                                        <x-dropdown-menu.item @click="$dispatch('toast', { message: 'Editar rol de {{ $member['name'] }}', variant: 'default' })">Cambiar rol</x-dropdown-menu.item>
                                        @if($member['status'] === 'pendiente')
                                        <x-dropdown-menu.item @click="$dispatch('toast', { message: 'Invitación reenviada', variant: 'success' })">Reenviar invitación</x-dropdown-menu.item>
                                        @endif
                                        @if($member['status'] === 'activo')
                                        <x-dropdown-menu.item @click="$dispatch('toast', { message: 'Acceso suspendido', variant: 'default' })">Suspender acceso</x-dropdown-menu.item>
                                        @endif
                                        <x-dropdown-menu.separator />
                                        <x-dropdown-menu.item :destructive="true" @click="$dispatch('toast', { message: '{{ $member['name'] }} eliminado del equipo', variant: 'destructive' })">Eliminar del equipo</x-dropdown-menu.item>
                                    </x-dropdown-menu.content>
                                </x-dropdown-menu>
                                @else
                                <span class="text-xs text-muted-foreground pr-2">—</span>
                                @endif
                            </x-table.cell>
                        </x-table.row>
                        @endforeach
                    </x-table.body>
                </x-table>
            </x-card.content>
        </x-card>

        {{-- Roles y permisos --}}
        <x-card>
            <x-card.header>
                <x-card.title>Descripción de roles</x-card.title>
                <x-card.description>Permisos asociados a cada nivel de acceso.</x-card.description>
            </x-card.header>
            <x-card.content class="p-0">
                <x-table>
                    <x-table.header>
                        <x-table.row>
                            <x-table.head>Rol</x-table.head>
                            <x-table.head>Dashboard</x-table.head>
                            <x-table.head>Pedidos</x-table.head>
                            <x-table.head>Productos</x-table.head>
                            <x-table.head>Clientes</x-table.head>
                            <x-table.head>Config.</x-table.head>
                            <x-table.head>Equipo</x-table.head>
                        </x-table.row>
                    </x-table.header>
                    <x-table.body>
                        @foreach([
                            ['role'=>'Administrador','variant'=>'default',   'perms'=>[true,true,true,true,true,true]],
                            ['role'=>'Editor',       'variant'=>'secondary', 'perms'=>[true,true,true,true,false,false]],
                            ['role'=>'Soporte',      'variant'=>'outline',   'perms'=>[true,true,false,true,false,false]],
                            ['role'=>'Viewer',       'variant'=>'secondary', 'perms'=>[true,false,false,false,false,false]],
                        ] as $r)
                        <x-table.row>
                            <x-table.cell><x-badge variant="{{ $r['variant'] }}">{{ $r['role'] }}</x-badge></x-table.cell>
                            @foreach($r['perms'] as $allowed)
                            <x-table.cell>
                                @if($allowed)
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-success"><polyline points="20 6 9 17 4 12"/></svg>
                                @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                @endif
                            </x-table.cell>
                            @endforeach
                        </x-table.row>
                        @endforeach
                    </x-table.body>
                </x-table>
            </x-card.content>
        </x-card>

    </div>
</x-layouts.dashboard>
