<x-layouts.dashboard title="Pedidos">

    <x-slot name="breadcrumb">
        <x-breadcrumb>
            <x-breadcrumb.item><x-breadcrumb.link href="/dashboard">Dashboard</x-breadcrumb.link></x-breadcrumb.item>
            <x-breadcrumb.separator />
            <x-breadcrumb.item><x-breadcrumb.page>Pedidos</x-breadcrumb.page></x-breadcrumb.item>
        </x-breadcrumb>
    </x-slot>

    @php
        $orders = [
            ['id'=>'#ORD-1042','customer'=>'Ana García',     'avatar'=>'AG','product'=>'Auriculares Pro Max',  'category'=>'Electrónica','date'=>'07/05/2026','amount'=>240.00, 'status'=>'completado','address'=>'Av. Corrientes 1234, CABA'],
            ['id'=>'#ORD-1041','customer'=>'Carlos López',   'avatar'=>'CL','product'=>'Teclado Mecánico',    'category'=>'Electrónica','date'=>'07/05/2026','amount'=>180.00, 'status'=>'pendiente', 'address'=>'Av. Rivadavia 5678, CABA'],
            ['id'=>'#ORD-1040','customer'=>'María Torres',   'avatar'=>'MT','product'=>'Monitor 4K 27"',       'category'=>'Electrónica','date'=>'06/05/2026','amount'=>899.00, 'status'=>'completado','address'=>'Bv. San Juan 900, Córdoba'],
            ['id'=>'#ORD-1039','customer'=>'Luis Ramírez',   'avatar'=>'LR','product'=>'Mouse Inalámbrico',   'category'=>'Electrónica','date'=>'06/05/2026','amount'=>65.00,  'status'=>'cancelado', 'address'=>'Pellegrini 340, Rosario'],
            ['id'=>'#ORD-1038','customer'=>'Sofía Martín',   'avatar'=>'SM','product'=>'Webcam HD 1080p',     'category'=>'Electrónica','date'=>'05/05/2026','amount'=>120.00, 'status'=>'enviado',   'address'=>'San Martín 412, Mendoza'],
            ['id'=>'#ORD-1037','customer'=>'Diego Herrera',  'avatar'=>'DH','product'=>'Hub USB-C 7 puertos', 'category'=>'Electrónica','date'=>'05/05/2026','amount'=>55.00,  'status'=>'completado','address'=>'Av. Santa Fe 2100, CABA'],
            ['id'=>'#ORD-1036','customer'=>'Laura Gómez',    'avatar'=>'LG','product'=>'SSD Externo 1TB',     'category'=>'Electrónica','date'=>'04/05/2026','amount'=>110.00, 'status'=>'pendiente', 'address'=>'Av. Boedo 880, CABA'],
            ['id'=>'#ORD-1035','customer'=>'Martín Silva',   'avatar'=>'MS','product'=>'Auriculares Pro Max', 'category'=>'Electrónica','date'=>'04/05/2026','amount'=>240.00, 'status'=>'enviado',   'address'=>'Córdoba 1800, Rosario'],
            ['id'=>'#ORD-1034','customer'=>'Paula Ruiz',     'avatar'=>'PR','product'=>'Zapatillas Running',  'category'=>'Ropa',       'date'=>'03/05/2026','amount'=>89.00,  'status'=>'completado','address'=>'Las Heras 320, CABA'],
            ['id'=>'#ORD-1033','customer'=>'Roberto Díaz',   'avatar'=>'RD','product'=>'Remera Deportiva',    'category'=>'Ropa',       'date'=>'03/05/2026','amount'=>45.00,  'status'=>'completado','address'=>'Colón 750, Córdoba'],
            ['id'=>'#ORD-1032','customer'=>'Valentina Cruz', 'avatar'=>'VC','product'=>'Lámpara LED',         'category'=>'Hogar',      'date'=>'02/05/2026','amount'=>75.00,  'status'=>'pendiente', 'address'=>'Arcos 2450, CABA'],
            ['id'=>'#ORD-1031','customer'=>'Fernando Pérez', 'avatar'=>'FP','product'=>'Cafetera Automática', 'category'=>'Hogar',      'date'=>'02/05/2026','amount'=>220.00, 'status'=>'enviado',   'address'=>'Av. Libertad 1100, Mendoza'],
            ['id'=>'#ORD-1030','customer'=>'Camila Torres',  'avatar'=>'CT','product'=>'Mouse Inalámbrico',   'category'=>'Electrónica','date'=>'01/05/2026','amount'=>65.00,  'status'=>'cancelado', 'address'=>'Salta 210, Buenos Aires'],
            ['id'=>'#ORD-1029','customer'=>'Héctor Vega',    'avatar'=>'HV','product'=>'Teclado Mecánico',    'category'=>'Electrónica','date'=>'01/05/2026','amount'=>180.00, 'status'=>'completado','address'=>'Mitre 690, Rosario'],
            ['id'=>'#ORD-1028','customer'=>'Natalia Castro', 'avatar'=>'NC','product'=>'Webcam HD 1080p',     'category'=>'Electrónica','date'=>'30/04/2026','amount'=>120.00, 'status'=>'completado','address'=>'9 de Julio 540, Tucumán'],
        ];

        $counts = [
            'total'      => count($orders),
            'pendiente'  => count(array_filter($orders, fn($o) => $o['status'] === 'pendiente')),
            'enviado'    => count(array_filter($orders, fn($o) => $o['status'] === 'enviado')),
            'completado' => count(array_filter($orders, fn($o) => $o['status'] === 'completado')),
            'cancelado'  => count(array_filter($orders, fn($o) => $o['status'] === 'cancelado')),
        ];
        $totalAmount = array_sum(array_column($orders, 'amount'));

        $statusVariant = ['completado'=>'success','pendiente'=>'warning','cancelado'=>'destructive','enviado'=>'secondary'];
        $statusLabel   = ['completado'=>'Completado','pendiente'=>'Pendiente','cancelado'=>'Cancelado','enviado'=>'Enviado'];
        $tabs = ['todos','pendiente','enviado','completado','cancelado'];
        $tabLabel = ['todos'=>'Todos','pendiente'=>'Pendientes','enviado'=>'Enviados','completado'=>'Completados','cancelado'=>'Cancelados'];
    @endphp

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Pedidos</h1>
                <p class="text-muted-foreground text-sm">Gestión y seguimiento de todos los pedidos.</p>
            </div>
            <div class="flex items-center gap-2">
                <x-button variant="outline" size="sm"
                    @click="$dispatch('toast', { message: 'Exportando pedidos...', variant: 'default' })">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                    Exportar
                </x-button>
                <x-button size="sm"
                    @click="$dispatch('toast', { message: 'Funcionalidad próximamente', variant: 'default' })">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
                    Nuevo pedido
                </x-button>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
            <x-card>
                <x-card.content class="pt-6">
                    <p class="text-sm font-medium text-muted-foreground">Total este período</p>
                    <p class="text-3xl font-bold mt-1">{{ $counts['total'] }}</p>
                    <p class="text-xs text-muted-foreground mt-1">de 156 pedidos históricos</p>
                </x-card.content>
            </x-card>
            <x-card>
                <x-card.content class="pt-6">
                    <p class="text-sm font-medium text-muted-foreground">Ingresos del período</p>
                    <p class="text-3xl font-bold mt-1">${{ number_format($totalAmount, 0, ',', '.') }}</p>
                    <p class="text-xs text-muted-foreground mt-1">ticket promedio ${{ number_format($totalAmount / $counts['total'], 0) }}</p>
                </x-card.content>
            </x-card>
            <x-card>
                <x-card.content class="pt-6">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-muted-foreground">Pendientes</p>
                        <x-badge variant="warning">Acción requerida</x-badge>
                    </div>
                    <p class="text-3xl font-bold mt-1">{{ $counts['pendiente'] }}</p>
                    <p class="text-xs text-muted-foreground mt-1">esperando procesamiento</p>
                </x-card.content>
            </x-card>
            <x-card>
                <x-card.content class="pt-6">
                    <p class="text-sm font-medium text-muted-foreground">Completados</p>
                    <p class="text-3xl font-bold mt-1">{{ $counts['completado'] }}</p>
                    <p class="text-xs text-muted-foreground mt-1">{{ round($counts['completado'] / $counts['total'] * 100) }}% tasa de completado</p>
                </x-card.content>
            </x-card>
        </div>

        {{-- Tabla principal --}}
        <x-card>
            <x-card.header class="pb-3">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div>
                        <x-card.title>Lista de pedidos</x-card.title>
                        <x-card.description>Mayo 2026 · {{ $counts['total'] }} pedidos</x-card.description>
                    </div>
                    {{-- Filtros --}}
                    <div class="flex items-center gap-2 flex-wrap">
                        <x-input placeholder="Buscar pedido o cliente..." class="h-8 w-52 text-xs" />
                        <x-select class="h-8 text-xs w-36">
                            <option>Todas las categorías</option>
                            <option>Electrónica</option>
                            <option>Ropa</option>
                            <option>Hogar</option>
                        </x-select>
                        <x-button variant="outline" size="sm" class="h-8 text-xs"
                            @click="$dispatch('toast', { message: 'Filtros aplicados', variant: 'default' })">
                            Filtrar
                        </x-button>
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
                                <span class="inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-muted px-1 text-[10px] font-medium">
                                    {{ $tab === 'todos' ? $counts['total'] : $counts[$tab] }}
                                </span>
                            </x-tabs.trigger>
                            @endforeach
                        </x-tabs.list>
                    </div>

                    @foreach($tabs as $tab)
                    <x-tabs.content value="{{ $tab }}">
                        <x-table>
                            <x-table.header>
                                <x-table.row>
                                    <x-table.head>Pedido</x-table.head>
                                    <x-table.head>Cliente</x-table.head>
                                    <x-table.head class="hidden md:table-cell">Producto</x-table.head>
                                    <x-table.head class="hidden lg:table-cell">Fecha</x-table.head>
                                    <x-table.head>Monto</x-table.head>
                                    <x-table.head>Estado</x-table.head>
                                    <x-table.head class="text-right">Acciones</x-table.head>
                                </x-table.row>
                            </x-table.header>
                            <x-table.body>
                                @foreach($orders as $order)
                                @if($tab === 'todos' || $order['status'] === $tab)
                                <x-table.row>
                                    <x-table.cell class="font-mono text-xs text-muted-foreground">{{ $order['id'] }}</x-table.cell>
                                    <x-table.cell>
                                        <div class="flex items-center gap-2">
                                            <x-avatar fallback="{{ $order['avatar'] }}" class="h-7 w-7 text-xs" />
                                            <span class="text-sm font-medium">{{ $order['customer'] }}</span>
                                        </div>
                                    </x-table.cell>
                                    <x-table.cell class="hidden md:table-cell text-sm text-muted-foreground">{{ $order['product'] }}</x-table.cell>
                                    <x-table.cell class="hidden lg:table-cell text-sm text-muted-foreground">{{ $order['date'] }}</x-table.cell>
                                    <x-table.cell class="font-semibold text-sm">${{ number_format($order['amount'], 2, ',', '.') }}</x-table.cell>
                                    <x-table.cell>
                                        <x-badge variant="{{ $statusVariant[$order['status']] }}">
                                            {{ $statusLabel[$order['status']] }}
                                        </x-badge>
                                    </x-table.cell>
                                    <x-table.cell class="text-right">
                                        <x-dialog>
                                            <x-dialog.trigger>
                                                <x-button variant="ghost" size="sm" class="h-7 px-2 text-xs">Ver</x-button>
                                            </x-dialog.trigger>
                                            <x-dialog.content>
                                                <x-dialog.header>
                                                    <x-dialog.title>Pedido {{ $order['id'] }}</x-dialog.title>
                                                    <x-dialog.description>Detalles completos del pedido.</x-dialog.description>
                                                </x-dialog.header>
                                                <div class="space-y-4 py-2">
                                                    <div class="flex items-center gap-3">
                                                        <x-avatar fallback="{{ $order['avatar'] }}" class="h-10 w-10" />
                                                        <div class="flex-1">
                                                            <p class="font-semibold">{{ $order['customer'] }}</p>
                                                            <p class="text-sm text-muted-foreground">cliente@ejemplo.com</p>
                                                        </div>
                                                        <x-badge variant="{{ $statusVariant[$order['status']] }}">{{ $statusLabel[$order['status']] }}</x-badge>
                                                    </div>
                                                    <x-separator />
                                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                                        <div><p class="text-muted-foreground text-xs">Producto</p><p class="font-medium mt-0.5">{{ $order['product'] }}</p></div>
                                                        <div><p class="text-muted-foreground text-xs">Categoría</p><p class="font-medium mt-0.5">{{ $order['category'] }}</p></div>
                                                        <div><p class="text-muted-foreground text-xs">Monto</p><p class="font-semibold text-base mt-0.5">${{ number_format($order['amount'], 2, ',', '.') }}</p></div>
                                                        <div><p class="text-muted-foreground text-xs">Fecha</p><p class="font-medium mt-0.5">{{ $order['date'] }}</p></div>
                                                        <div><p class="text-muted-foreground text-xs">Método de pago</p><p class="font-medium mt-0.5">Tarjeta •••• 4242</p></div>
                                                        <div><p class="text-muted-foreground text-xs">N° seguimiento</p><p class="font-medium mt-0.5 font-mono text-xs">TRK-{{ rand(100000, 999999) }}</p></div>
                                                    </div>
                                                    <x-alert>
                                                        <x-alert.title>Dirección de entrega</x-alert.title>
                                                        <x-alert.description>{{ $order['address'] }}</x-alert.description>
                                                    </x-alert>
                                                </div>
                                                <x-dialog.footer>
                                                    <x-button variant="outline" @click="open = false">Cerrar</x-button>
                                                    @if($order['status'] === 'pendiente')
                                                    <x-button @click="open = false; $dispatch('toast', { message: 'Pedido marcado como enviado', variant: 'success' })">
                                                        Marcar como enviado
                                                    </x-button>
                                                    @elseif($order['status'] === 'enviado')
                                                    <x-button @click="open = false; $dispatch('toast', { message: 'Pedido marcado como completado', variant: 'success' })">
                                                        Confirmar entrega
                                                    </x-button>
                                                    @else
                                                    <x-button variant="outline" @click="open = false">Imprimir</x-button>
                                                    @endif
                                                </x-dialog.footer>
                                            </x-dialog.content>
                                        </x-dialog>
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
                <span>Mostrando {{ $counts['total'] }} de 156 pedidos</span>
                <x-pagination>
                    <x-pagination.content>
                        <x-pagination.item><x-pagination.link :disabled="true">« Anterior</x-pagination.link></x-pagination.item>
                        <x-pagination.item><x-pagination.link :active="true" href="#">1</x-pagination.link></x-pagination.item>
                        <x-pagination.item><x-pagination.link href="#">2</x-pagination.link></x-pagination.item>
                        <x-pagination.item><x-pagination.link href="#">3</x-pagination.link></x-pagination.item>
                        <x-pagination.item><x-pagination.link href="#">···</x-pagination.link></x-pagination.item>
                        <x-pagination.item><x-pagination.link href="#">11</x-pagination.link></x-pagination.item>
                        <x-pagination.item><x-pagination.link href="#">Siguiente »</x-pagination.link></x-pagination.item>
                    </x-pagination.content>
                </x-pagination>
            </x-card.footer>
        </x-card>

    </div>
</x-layouts.dashboard>
