<x-layouts.dashboard title="Clientes">

    <x-slot name="breadcrumb">
        <x-breadcrumb>
            <x-breadcrumb.item><x-breadcrumb.link href="/dashboard">Dashboard</x-breadcrumb.link></x-breadcrumb.item>
            <x-breadcrumb.separator />
            <x-breadcrumb.item><x-breadcrumb.page>Clientes</x-breadcrumb.page></x-breadcrumb.item>
        </x-breadcrumb>
    </x-slot>

    @php
        $clients = [
            ['name'=>'Ana García',      'avatar'=>'AG','email'=>'ana.garcia@gmail.com',     'phone'=>'+54 11 4523-8791','city'=>'CABA',          'orders'=>18,'spent'=>3840.00,'status'=>'activo',   'since'=>'Mar 2024'],
            ['name'=>'Carlos López',    'avatar'=>'CL','email'=>'carlos.lopez@hotmail.com', 'phone'=>'+54 11 3891-4562','city'=>'Buenos Aires',  'orders'=>7, 'spent'=>1260.00,'status'=>'activo',   'since'=>'Jul 2024'],
            ['name'=>'María Torres',    'avatar'=>'MT','email'=>'maria.torres@yahoo.com',   'phone'=>'+54 351 4782-390','city'=>'Córdoba',       'orders'=>24,'spent'=>8920.00,'status'=>'activo',   'since'=>'Nov 2023'],
            ['name'=>'Luis Ramírez',    'avatar'=>'LR','email'=>'luis.ramirez@gmail.com',   'phone'=>'+54 11 2345-6789','city'=>'Rosario',       'orders'=>3, 'spent'=>195.00, 'status'=>'inactivo', 'since'=>'Ene 2025'],
            ['name'=>'Sofía Martín',    'avatar'=>'SM','email'=>'sofia.martin@gmail.com',   'phone'=>'+54 261 5678-901','city'=>'Mendoza',       'orders'=>12,'spent'=>2340.00,'status'=>'activo',   'since'=>'May 2024'],
            ['name'=>'Diego Herrera',   'avatar'=>'DH','email'=>'diego.herrera@outlook.com','phone'=>'+54 11 8901-2345','city'=>'CABA',          'orders'=>9, 'spent'=>1785.00,'status'=>'activo',   'since'=>'Sep 2024'],
            ['name'=>'Laura Gómez',     'avatar'=>'LG','email'=>'laura.gomez@gmail.com',    'phone'=>'+54 11 1234-5678','city'=>'Buenos Aires',  'orders'=>5, 'spent'=>660.00, 'status'=>'activo',   'since'=>'Feb 2025'],
            ['name'=>'Martín Silva',    'avatar'=>'MS','email'=>'martin.silva@yahoo.com',   'phone'=>'+54 341 9012-345','city'=>'Rosario',       'orders'=>31,'spent'=>7440.00,'status'=>'activo',   'since'=>'Jun 2023'],
            ['name'=>'Paula Ruiz',      'avatar'=>'PR','email'=>'paula.ruiz@gmail.com',     'phone'=>'+54 11 4567-8901','city'=>'CABA',          'orders'=>2, 'spent'=>134.00, 'status'=>'inactivo', 'since'=>'Abr 2025'],
            ['name'=>'Roberto Díaz',    'avatar'=>'RD','email'=>'roberto.diaz@hotmail.com', 'phone'=>'+54 351 2345-678','city'=>'Córdoba',       'orders'=>15,'spent'=>3120.00,'status'=>'activo',   'since'=>'Ene 2024'],
        ];

        $statusVariant = ['activo'=>'success','inactivo'=>'secondary'];
        $tabs = ['todos','activo','inactivo'];
        $tabLabel = ['todos'=>'Todos','activo'=>'Activos','inactivo'=>'Inactivos'];
        $tabCount = [
            'todos'    => count($clients),
            'activo'   => count(array_filter($clients, fn($c) => $c['status'] === 'activo')),
            'inactivo' => count(array_filter($clients, fn($c) => $c['status'] === 'inactivo')),
        ];
        $totalSpent = array_sum(array_column($clients, 'spent'));
        $avgSpent   = $totalSpent / count($clients);
    @endphp

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Clientes</h1>
                <p class="text-muted-foreground text-sm">Base de clientes registrados y su actividad.</p>
            </div>
            <div class="flex items-center gap-2">
                <x-button variant="outline" size="sm"
                    @click="$dispatch('toast', { message: 'Exportando clientes...', variant: 'default' })">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                    Exportar
                </x-button>
                <x-dialog>
                    <x-dialog.trigger>
                        <x-button size="sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
                            Nuevo cliente
                        </x-button>
                    </x-dialog.trigger>
                    <x-dialog.content>
                        <x-dialog.header>
                            <x-dialog.title>Nuevo cliente</x-dialog.title>
                            <x-dialog.description>Registrá un cliente manualmente en la base de datos.</x-dialog.description>
                        </x-dialog.header>
                        <div class="space-y-4 py-2">
                            <div class="space-y-1.5">
                                <x-label>Nombre completo</x-label>
                                <x-input placeholder="Ej: Juan Pérez" />
                            </div>
                            <div class="space-y-1.5">
                                <x-label>Email</x-label>
                                <x-input type="email" placeholder="juan@ejemplo.com" />
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1.5">
                                    <x-label>Teléfono</x-label>
                                    <x-input type="tel" placeholder="+54 11 0000-0000" />
                                </div>
                                <div class="space-y-1.5">
                                    <x-label>Ciudad</x-label>
                                    <x-input placeholder="Ej: Buenos Aires" />
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <x-label>Notas</x-label>
                                <x-textarea placeholder="Información adicional..." rows="2" />
                            </div>
                        </div>
                        <x-dialog.footer>
                            <x-button variant="outline" @click="open = false">Cancelar</x-button>
                            <x-button @click="open = false; $dispatch('toast', { message: 'Cliente registrado correctamente', variant: 'success' })">
                                Crear cliente
                            </x-button>
                        </x-dialog.footer>
                    </x-dialog.content>
                </x-dialog>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
            <x-card>
                <x-card.content class="pt-6">
                    <p class="text-sm font-medium text-muted-foreground">Total clientes</p>
                    <p class="text-3xl font-bold mt-1">1,284</p>
                    <div class="flex items-center gap-1.5 mt-1">
                        <x-badge variant="success" class="text-xs px-1.5">↑ +48</x-badge>
                        <span class="text-xs text-muted-foreground">este mes</span>
                    </div>
                </x-card.content>
            </x-card>
            <x-card>
                <x-card.content class="pt-6">
                    <p class="text-sm font-medium text-muted-foreground">Clientes activos</p>
                    <p class="text-3xl font-bold mt-1">892</p>
                    <p class="text-xs text-muted-foreground mt-1">69.5% del total</p>
                </x-card.content>
            </x-card>
            <x-card>
                <x-card.content class="pt-6">
                    <p class="text-sm font-medium text-muted-foreground">Nuevos este mes</p>
                    <p class="text-3xl font-bold mt-1">48</p>
                    <div class="flex items-center gap-1.5 mt-1">
                        <x-badge variant="success" class="text-xs px-1.5">↑ +12%</x-badge>
                        <span class="text-xs text-muted-foreground">vs mes anterior</span>
                    </div>
                </x-card.content>
            </x-card>
            <x-card>
                <x-card.content class="pt-6">
                    <p class="text-sm font-medium text-muted-foreground">Valor promedio</p>
                    <p class="text-3xl font-bold mt-1">${{ number_format($avgSpent, 0, ',', '.') }}</p>
                    <p class="text-xs text-muted-foreground mt-1">gasto promedio por cliente</p>
                </x-card.content>
            </x-card>
        </div>

        {{-- Tabla --}}
        <x-card>
            <x-card.header class="pb-3">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div>
                        <x-card.title>Base de clientes</x-card.title>
                        <x-card.description>{{ count($clients) }} clientes · página 1 de 129</x-card.description>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-input placeholder="Buscar nombre o email..." class="h-8 w-52 text-xs" />
                        <x-select class="h-8 text-xs w-36">
                            <option>Todas las ciudades</option>
                            <option>CABA</option>
                            <option>Buenos Aires</option>
                            <option>Córdoba</option>
                            <option>Rosario</option>
                            <option>Mendoza</option>
                        </x-select>
                    </div>
                </div>
            </x-card.header>
            <x-card.content class="p-0">
                <x-tabs default="todos">
                    <div class="px-6 pt-1 pb-2 border-b">
                        <x-tabs.list class="h-9">
                            @foreach($tabs as $tab)
                            <x-tabs.trigger value="{{ $tab }}" class="text-xs h-8 gap-1.5">
                                {{ $tabLabel[$tab] }}
                                <span class="inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-muted px-1 text-[10px] font-medium">{{ $tabCount[$tab] }}</span>
                            </x-tabs.trigger>
                            @endforeach
                        </x-tabs.list>
                    </div>

                    @foreach($tabs as $tab)
                    <x-tabs.content value="{{ $tab }}">
                        <x-table>
                            <x-table.header>
                                <x-table.row>
                                    <x-table.head>Cliente</x-table.head>
                                    <x-table.head class="hidden md:table-cell">Ciudad</x-table.head>
                                    <x-table.head class="hidden lg:table-cell">Teléfono</x-table.head>
                                    <x-table.head>Pedidos</x-table.head>
                                    <x-table.head>Total gastado</x-table.head>
                                    <x-table.head>Estado</x-table.head>
                                    <x-table.head class="hidden xl:table-cell">Registro</x-table.head>
                                    <x-table.head class="text-right">Acciones</x-table.head>
                                </x-table.row>
                            </x-table.header>
                            <x-table.body>
                                @foreach($clients as $client)
                                @if($tab === 'todos' || $client['status'] === $tab)
                                <x-table.row>
                                    <x-table.cell>
                                        <div class="flex items-center gap-3">
                                            <x-avatar fallback="{{ $client['avatar'] }}" class="h-8 w-8 text-xs" />
                                            <div>
                                                <p class="text-sm font-medium">{{ $client['name'] }}</p>
                                                <p class="text-xs text-muted-foreground">{{ $client['email'] }}</p>
                                            </div>
                                        </div>
                                    </x-table.cell>
                                    <x-table.cell class="hidden md:table-cell text-sm text-muted-foreground">{{ $client['city'] }}</x-table.cell>
                                    <x-table.cell class="hidden lg:table-cell text-sm text-muted-foreground">{{ $client['phone'] }}</x-table.cell>
                                    <x-table.cell class="text-sm font-medium">{{ $client['orders'] }}</x-table.cell>
                                    <x-table.cell class="font-semibold text-sm">${{ number_format($client['spent'], 0, ',', '.') }}</x-table.cell>
                                    <x-table.cell>
                                        <x-badge variant="{{ $statusVariant[$client['status']] }}" class="capitalize">{{ $client['status'] }}</x-badge>
                                    </x-table.cell>
                                    <x-table.cell class="hidden xl:table-cell text-sm text-muted-foreground">{{ $client['since'] }}</x-table.cell>
                                    <x-table.cell class="text-right">
                                        <x-dropdown-menu>
                                            <x-dropdown-menu.trigger>
                                                <x-button variant="ghost" size="icon" class="h-8 w-8">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
                                                </x-button>
                                            </x-dropdown-menu.trigger>
                                            <x-dropdown-menu.content align="end">
                                                <x-dropdown-menu.item @click="$dispatch('toast', { message: 'Viendo perfil de {{ $client['name'] }}', variant: 'default' })">Ver perfil</x-dropdown-menu.item>
                                                <x-dropdown-menu.item @click="$dispatch('toast', { message: 'Ver pedidos de {{ $client['name'] }}', variant: 'default' })">Ver pedidos</x-dropdown-menu.item>
                                                <x-dropdown-menu.item @click="$dispatch('toast', { message: 'Email enviado a {{ $client['name'] }}', variant: 'success' })">Enviar email</x-dropdown-menu.item>
                                                <x-dropdown-menu.separator />
                                                <x-dropdown-menu.item :destructive="true">Eliminar cliente</x-dropdown-menu.item>
                                            </x-dropdown-menu.content>
                                        </x-dropdown-menu>
                                    </x-table.cell>
                                </x-table.row>
                                @endif
                                @endforeach
                            </x-table.body>
                        </x-table>
                    </x-tabs.content>
                    @endforeach
                </x-tabs>
            </x-card.content>
            <x-card.footer class="flex items-center justify-between text-sm text-muted-foreground py-3">
                <span>Mostrando {{ count($clients) }} de 1,284 clientes</span>
                <x-pagination>
                    <x-pagination.content>
                        <x-pagination.item><x-pagination.link :disabled="true">« Anterior</x-pagination.link></x-pagination.item>
                        <x-pagination.item><x-pagination.link :active="true" href="#">1</x-pagination.link></x-pagination.item>
                        <x-pagination.item><x-pagination.link href="#">2</x-pagination.link></x-pagination.item>
                        <x-pagination.item><x-pagination.link href="#">3</x-pagination.link></x-pagination.item>
                        <x-pagination.item><x-pagination.link href="#">···</x-pagination.link></x-pagination.item>
                        <x-pagination.item><x-pagination.link href="#">129</x-pagination.link></x-pagination.item>
                        <x-pagination.item><x-pagination.link href="#">Siguiente »</x-pagination.link></x-pagination.item>
                    </x-pagination.content>
                </x-pagination>
            </x-card.footer>
        </x-card>

    </div>
</x-layouts.dashboard>
