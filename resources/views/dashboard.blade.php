<x-layouts.dashboard title="Overview">

    <x-slot name="breadcrumb">
        <x-breadcrumb>
            <x-breadcrumb.item><x-breadcrumb.link href="/dashboard">Dashboard</x-breadcrumb.link></x-breadcrumb.item>
            <x-breadcrumb.separator />
            <x-breadcrumb.item><x-breadcrumb.page>Overview</x-breadcrumb.page></x-breadcrumb.item>
        </x-breadcrumb>
    </x-slot>

    @php
        $stats = [
            ['label' => 'Ingresos totales',   'value' => '$45,231.89', 'change' => '+20.1%', 'trend' => 'up',   'sub' => 'vs mes anterior', 'color' => 'success',     'icon' => 'circle-dollar-sign'],
            ['label' => 'Pedidos',            'value' => '+2,350',     'change' => '+180.1%','trend' => 'up',   'sub' => 'vs mes anterior', 'color' => 'success',     'icon' => 'list'],
            ['label' => 'Clientes activos',   'value' => '+12,234',    'change' => '+19%',   'trend' => 'up',   'sub' => 'vs mes anterior', 'color' => 'success',     'icon' => 'users'],
            ['label' => 'Tasa de conversión', 'value' => '3.24%',      'change' => '-4.5%',  'trend' => 'down', 'sub' => 'vs mes anterior', 'color' => 'destructive', 'icon' => 'activity'],
        ];

        $chartData = [
            ['month' => 'Ene', 'amount' => 18400],
            ['month' => 'Feb', 'amount' => 14200],
            ['month' => 'Mar', 'amount' => 22100],
            ['month' => 'Abr', 'amount' => 16300],
            ['month' => 'May', 'amount' => 24600],
            ['month' => 'Jun', 'amount' => 27300],
            ['month' => 'Jul', 'amount' => 20400],
            ['month' => 'Ago', 'amount' => 23100],
            ['month' => 'Sep', 'amount' => 25500],
            ['month' => 'Oct', 'amount' => 27900],
            ['month' => 'Nov', 'amount' => 30000],
            ['month' => 'Dic', 'amount' => 13500],
        ];

        $orders = [
            ['id' => '#ORD-1042', 'customer' => 'Ana García',    'avatar' => 'AG', 'product' => 'Auriculares Pro Max',  'date' => '2026-05-07', 'amount' => '$240.00', 'status' => 'completado'],
            ['id' => '#ORD-1041', 'customer' => 'Carlos López',  'avatar' => 'CL', 'product' => 'Teclado Mecánico',    'date' => '2026-05-07', 'amount' => '$180.00', 'status' => 'pendiente'],
            ['id' => '#ORD-1040', 'customer' => 'María Torres',  'avatar' => 'MT', 'product' => 'Monitor 4K 27"',       'date' => '2026-05-06', 'amount' => '$899.00', 'status' => 'completado'],
            ['id' => '#ORD-1039', 'customer' => 'Luis Ramírez',  'avatar' => 'LR', 'product' => 'Mouse Inalámbrico',   'date' => '2026-05-06', 'amount' => '$65.00',  'status' => 'cancelado'],
            ['id' => '#ORD-1038', 'customer' => 'Sofía Martín',  'avatar' => 'SM', 'product' => 'Webcam HD 1080p',     'date' => '2026-05-05', 'amount' => '$120.00', 'status' => 'enviado'],
            ['id' => '#ORD-1037', 'customer' => 'Diego Herrera', 'avatar' => 'DH', 'product' => 'Hub USB-C 7 puertos', 'date' => '2026-05-05', 'amount' => '$55.00',  'status' => 'completado'],
            ['id' => '#ORD-1036', 'customer' => 'Laura Gómez',   'avatar' => 'LG', 'product' => 'SSD Externo 1TB',     'date' => '2026-05-04', 'amount' => '$110.00', 'status' => 'pendiente'],
            ['id' => '#ORD-1035', 'customer' => 'Martín Silva',  'avatar' => 'MS', 'product' => 'Auriculares Pro Max',  'date' => '2026-05-04', 'amount' => '$240.00', 'status' => 'enviado'],
        ];

        $products = [
            ['name' => 'Auriculares Pro Max', 'sales' => 342, 'revenue' => '$82,080',  'pct' => 92, 'trend' => 'up'],
            ['name' => 'Monitor 4K 27"',       'sales' => 180, 'revenue' => '$161,820', 'pct' => 78, 'trend' => 'up'],
            ['name' => 'Teclado Mecánico',     'sales' => 290, 'revenue' => '$52,200',  'pct' => 65, 'trend' => 'up'],
            ['name' => 'Webcam HD 1080p',      'sales' => 210, 'revenue' => '$25,200',  'pct' => 48, 'trend' => 'down'],
            ['name' => 'SSD Externo 1TB',      'sales' => 154, 'revenue' => '$16,940',  'pct' => 35, 'trend' => 'down'],
        ];

        $activity = [
            ['icon' => 'shopping-bag',  'color' => 'text-primary',          'text' => 'Ana García realizó el pedido #1042',       'time' => 'Hace 5 min'],
            ['icon' => 'circle-check',  'color' => 'text-success',          'text' => 'Pago confirmado pedido #1041 — $180.00',    'time' => 'Hace 23 min'],
            ['icon' => 'triangle-alert','color' => 'text-warning',          'text' => 'Stock bajo: Auriculares Pro Max (3 uds.)',  'time' => 'Hace 1 h'],
            ['icon' => 'user-plus',     'color' => 'text-muted-foreground', 'text' => 'Nuevo cliente registrado: Diego Herrera',  'time' => 'Hace 2 h'],
            ['icon' => 'truck',         'color' => 'text-primary',          'text' => 'Pedido #1040 enviado a domicilio',         'time' => 'Hace 3 h'],
        ];

        $statusVariant = [
            'completado' => 'success',
            'pendiente'  => 'warning',
            'cancelado'  => 'destructive',
            'enviado'    => 'secondary',
        ];
    @endphp

    <div class="space-y-6">

        {{-- Page header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Overview</h1>
                <p class="text-muted-foreground text-sm">Bienvenido de vuelta, Nicolás. Aquí está el resumen de hoy.</p>
            </div>
            <div class="flex items-center gap-2">
                <x-button variant="outline" size="sm"
                    @click="$dispatch('toast', { message: 'Exportando datos...', variant: 'default' })">
                    <x-lucide-download class="size-3.5" />
                    Exportar
                </x-button>
                <x-button size="sm"
                    @click="$dispatch('toast', { message: 'Reporte generado correctamente', variant: 'success' })">
                    <x-lucide-plus class="size-3.5" />
                    Nuevo reporte
                </x-button>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach($stats as $stat)
            <x-card>
                <x-card.header class="pb-2 flex-row items-center justify-between space-y-0">
                    <x-card.title class="text-sm font-medium text-muted-foreground">{{ $stat['label'] }}</x-card.title>
                    <x-dynamic-component :component="'lucide-'.$stat['icon']" class="size-4 text-muted-foreground" />
                </x-card.header>
                <x-card.content>
                    <div class="text-2xl font-bold">{{ $stat['value'] }}</div>
                    <div class="flex items-center gap-1.5 mt-1">
                        <x-badge variant="{{ $stat['color'] }}" class="text-xs px-1.5 py-0">
                            {{ $stat['trend'] === 'up' ? '↑' : '↓' }} {{ $stat['change'] }}
                        </x-badge>
                        <span class="text-xs text-muted-foreground">{{ $stat['sub'] }}</span>
                    </div>
                </x-card.content>
            </x-card>
            @endforeach
        </div>

        {{-- Chart + Activity --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            <x-card class="lg:col-span-2">
                <x-card.header>
                    <div class="flex items-center justify-between">
                        <div>
                            <x-card.title>Ingresos mensuales</x-card.title>
                            <x-card.description>Enero — Diciembre 2026</x-card.description>
                        </div>
                        <x-badge variant="outline">$45,231.89 total</x-badge>
                    </div>
                </x-card.header>
                <x-card.content class="pt-0">
                    <x-chart
                        type="bar"
                        :series="[['name' => 'Ingresos', 'data' => array_column($chartData, 'amount')]]"
                        :categories="array_column($chartData, 'month')"
                        :height="220"
                        yformat="currency"
                    />
                </x-card.content>
            </x-card>

            {{-- Activity feed --}}
            <x-card>
                <x-card.header>
                    <x-card.title>Actividad reciente</x-card.title>
                    <x-card.description>Últimas 5 acciones</x-card.description>
                </x-card.header>
                <x-card.content class="space-y-4">
                    @foreach($activity as $event)
                    <div class="flex gap-3">
                        <div class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-muted">
                            <x-dynamic-component :component="'lucide-'.$event['icon']" class="size-3.5 {{ $event['color'] }}" />
                        </div>
                        <div class="flex-1 space-y-0.5">
                            <p class="text-sm leading-snug">{{ $event['text'] }}</p>
                            <p class="text-xs text-muted-foreground">{{ $event['time'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </x-card.content>
                <x-card.footer>
                    <x-button variant="ghost" size="sm" class="w-full text-muted-foreground"
                        @click="window.location.href='/pedidos'">
                        Ver toda la actividad
                    </x-button>
                </x-card.footer>
            </x-card>

        </div>

        {{-- Orders Table + Top Products --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            <div class="lg:col-span-2">
                <x-card>
                    <x-card.header>
                        <div class="flex items-center justify-between">
                            <div>
                                <x-card.title>Pedidos recientes</x-card.title>
                                <x-card.description>{{ count($orders) }} pedidos este período</x-card.description>
                            </div>
                            <x-button variant="outline" size="sm" @click="window.location.href='/pedidos'">
                                Ver todos
                            </x-button>
                        </div>
                    </x-card.header>
                    <x-card.content class="p-0">

                        <x-tabs default="todos" class="px-6 pt-2">
                            <x-tabs.list class="h-8">
                                <x-tabs.trigger value="todos"      class="text-xs h-7">Todos</x-tabs.trigger>
                                <x-tabs.trigger value="pendiente"  class="text-xs h-7">Pendientes</x-tabs.trigger>
                                <x-tabs.trigger value="enviado"    class="text-xs h-7">Enviados</x-tabs.trigger>
                                <x-tabs.trigger value="completado" class="text-xs h-7">Completados</x-tabs.trigger>
                            </x-tabs.list>

                            @foreach(['todos', 'pendiente', 'enviado', 'completado'] as $tab)
                            <x-tabs.content value="{{ $tab }}" class="mt-3">
                                <x-table>
                                    <x-table.header>
                                        <x-table.row>
                                            <x-table.head>Cliente</x-table.head>
                                            <x-table.head class="hidden md:table-cell">Producto</x-table.head>
                                            <x-table.head>Monto</x-table.head>
                                            <x-table.head>Estado</x-table.head>
                                            <x-table.head class="text-right">Acciones</x-table.head>
                                        </x-table.row>
                                    </x-table.header>
                                    <x-table.body>
                                        @foreach($orders as $order)
                                        @if($tab === 'todos' || $order['status'] === $tab)
                                        <x-table.row>
                                            <x-table.cell>
                                                <div class="flex items-center gap-2">
                                                    <x-avatar fallback="{{ $order['avatar'] }}" class="h-7 w-7 text-xs" />
                                                    <div>
                                                        <p class="text-sm font-medium leading-none">{{ $order['customer'] }}</p>
                                                        <p class="text-xs text-muted-foreground">{{ $order['id'] }}</p>
                                                    </div>
                                                </div>
                                            </x-table.cell>
                                            <x-table.cell class="hidden md:table-cell text-sm text-muted-foreground">
                                                {{ $order['product'] }}
                                            </x-table.cell>
                                            <x-table.cell class="font-medium text-sm">{{ $order['amount'] }}</x-table.cell>
                                            <x-table.cell>
                                                <x-badge variant="{{ $statusVariant[$order['status']] }}" class="capitalize">
                                                    {{ $order['status'] }}
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
                                                                <div>
                                                                    <p class="font-medium">{{ $order['customer'] }}</p>
                                                                    <p class="text-sm text-muted-foreground">cliente@ejemplo.com</p>
                                                                </div>
                                                                <x-badge variant="{{ $statusVariant[$order['status']] }}" class="ml-auto capitalize">
                                                                    {{ $order['status'] }}
                                                                </x-badge>
                                                            </div>
                                                            <x-separator />
                                                            <div class="grid grid-cols-2 gap-3 text-sm">
                                                                <div>
                                                                    <p class="text-muted-foreground">Producto</p>
                                                                    <p class="font-medium mt-0.5">{{ $order['product'] }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-muted-foreground">Monto</p>
                                                                    <p class="font-medium mt-0.5">{{ $order['amount'] }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-muted-foreground">Fecha</p>
                                                                    <p class="font-medium mt-0.5">{{ $order['date'] }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-muted-foreground">Método de pago</p>
                                                                    <p class="font-medium mt-0.5">Tarjeta •••• 4242</p>
                                                                </div>
                                                            </div>
                                                            <x-alert>
                                                                <x-alert.title>Dirección de entrega</x-alert.title>
                                                                <x-alert.description>Av. Rivadavia 1234, CABA, Buenos Aires</x-alert.description>
                                                            </x-alert>
                                                        </div>
                                                        <x-dialog.footer>
                                                            <x-button variant="outline" @click="open = false">Cerrar</x-button>
                                                            <x-button @click="open = false; $dispatch('toast', { message: 'Pedido marcado como enviado', variant: 'success' })">
                                                                Marcar como enviado
                                                            </x-button>
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
                </x-card>
            </div>

            {{-- Top products --}}
            <div class="space-y-4">

                <x-card>
                    <x-card.header>
                        <x-card.title>Top productos</x-card.title>
                        <x-card.description>Por ingresos generados</x-card.description>
                    </x-card.header>
                    <x-card.content class="space-y-5">
                        @foreach($products as $product)
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium truncate flex-1 mr-2">{{ $product['name'] }}</span>
                                <span class="text-muted-foreground shrink-0">{{ $product['revenue'] }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-progress :value="$product['pct']" class="flex-1 h-1.5" />
                                <span class="text-xs text-muted-foreground w-8 text-right">{{ $product['pct'] }}%</span>
                            </div>
                            <p class="text-xs text-muted-foreground">{{ $product['sales'] }} unidades vendidas</p>
                        </div>
                        @endforeach
                    </x-card.content>
                </x-card>

                {{-- Quick actions --}}
                <x-card>
                    <x-card.header>
                        <x-card.title>Acciones rápidas</x-card.title>
                    </x-card.header>
                    <x-card.content class="space-y-2">
                        <x-button variant="outline" class="w-full justify-start gap-2" size="sm"
                            @click="window.location.href='/productos'">
                            <x-lucide-package class="size-3.5" />
                            Agregar producto
                        </x-button>
                        <x-button variant="outline" class="w-full justify-start gap-2" size="sm"
                            @click="window.location.href='/clientes'">
                            <x-lucide-user-plus class="size-3.5" />
                            Nuevo cliente
                        </x-button>
                        <x-button variant="outline" class="w-full justify-start gap-2" size="sm"
                            @click="$dispatch('toast', { message: 'Reporte exportado correctamente', variant: 'success' })">
                            <x-lucide-download class="size-3.5" />
                            Exportar CSV
                        </x-button>
                        <x-separator />
                        <x-dialog>
                            <x-dialog.trigger>
                                <x-button variant="destructive" class="w-full justify-start gap-2" size="sm">
                                    <x-lucide-trash-2 class="size-3.5" />
                                    Limpiar caché
                                </x-button>
                            </x-dialog.trigger>
                            <x-dialog.content>
                                <x-dialog.header>
                                    <x-dialog.title>¿Limpiar caché del sistema?</x-dialog.title>
                                    <x-dialog.description>
                                        Esta acción eliminará todos los archivos de caché. La aplicación puede tardar en responder durante unos segundos.
                                    </x-dialog.description>
                                </x-dialog.header>
                                <x-dialog.footer>
                                    <x-button variant="outline" @click="open = false">Cancelar</x-button>
                                    <x-button variant="destructive"
                                        @click="open = false; $dispatch('toast', { message: 'Caché limpiado correctamente', variant: 'default' })">
                                        Sí, limpiar
                                    </x-button>
                                </x-dialog.footer>
                            </x-dialog.content>
                        </x-dialog>
                    </x-card.content>
                </x-card>

            </div>
        </div>

        {{-- Bottom row: Form + Metrics --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            <x-card>
                <x-card.header>
                    <x-card.title>Filtrar reportes</x-card.title>
                    <x-card.description>Generá un reporte personalizado</x-card.description>
                </x-card.header>
                <x-card.content class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <x-label for="date-from">Fecha desde</x-label>
                            <x-input id="date-from" type="date" value="2026-01-01" />
                        </div>
                        <div class="space-y-1.5">
                            <x-label for="date-to">Fecha hasta</x-label>
                            <x-input id="date-to" type="date" value="2026-05-07" />
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <x-label for="report-type">Tipo de reporte</x-label>
                        <x-select id="report-type">
                            <option>Ventas por producto</option>
                            <option>Ingresos por período</option>
                            <option>Clientes nuevos</option>
                            <option>Tasa de conversión</option>
                        </x-select>
                    </div>
                    <div class="space-y-1.5">
                        <x-label>Formato</x-label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 text-sm">
                                <x-radio name="format" value="csv" :checked="true" /> CSV
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <x-radio name="format" value="pdf" /> PDF
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <x-radio name="format" value="xlsx" /> Excel
                            </label>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-2 text-sm">
                            <x-switch :checked="true" /> Incluir gráficos
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <x-switch /> Solo activos
                        </label>
                    </div>
                </x-card.content>
                <x-card.footer class="gap-2">
                    <x-button variant="outline" class="flex-1">Limpiar</x-button>
                    <x-button class="flex-1"
                        @click="$dispatch('toast', { message: 'Reporte en camino a tu email', variant: 'success' })">
                        Generar reporte
                    </x-button>
                </x-card.footer>
            </x-card>

            <div class="space-y-4">
                <x-card>
                    <x-card.header>
                        <x-card.title>Estado del sistema</x-card.title>
                        <x-card.description>Métricas de infraestructura en tiempo real</x-card.description>
                    </x-card.header>
                    <x-card.content class="space-y-4">
                        @foreach([
                            ['label' => 'CPU',         'value' => 34],
                            ['label' => 'Memoria RAM', 'value' => 67],
                            ['label' => 'Disco',       'value' => 81],
                            ['label' => 'Red',         'value' => 22],
                        ] as $metric)
                        <div class="space-y-1.5">
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">{{ $metric['label'] }}</span>
                                <span class="font-medium">{{ $metric['value'] }}%</span>
                            </div>
                            <x-progress :value="$metric['value']" class="h-1.5" />
                        </div>
                        @endforeach
                    </x-card.content>
                </x-card>

                <x-card>
                    <x-card.header>
                        <x-card.title>FAQ del sistema</x-card.title>
                    </x-card.header>
                    <x-card.content class="p-0 px-6 pb-2">
                        <x-accordion>
                            <x-accordion.item value="q1">
                                <x-accordion.trigger class="text-sm">¿Cada cuánto se actualizan los datos?</x-accordion.trigger>
                                <x-accordion.content class="text-muted-foreground text-sm">Los datos del dashboard se actualizan cada 5 minutos automáticamente.</x-accordion.content>
                            </x-accordion.item>
                            <x-accordion.item value="q2">
                                <x-accordion.trigger class="text-sm">¿Cómo exporto un reporte?</x-accordion.trigger>
                                <x-accordion.content class="text-muted-foreground text-sm">Usá el formulario de filtros a la izquierda o el botón "Exportar" en el encabezado.</x-accordion.content>
                            </x-accordion.item>
                            <x-accordion.item value="q3">
                                <x-accordion.trigger class="text-sm">¿Qué significa "tasa de conversión"?</x-accordion.trigger>
                                <x-accordion.content class="text-muted-foreground text-sm">Es el porcentaje de visitantes que completaron una compra sobre el total de visitas.</x-accordion.content>
                            </x-accordion.item>
                        </x-accordion>
                    </x-card.content>
                </x-card>
            </div>

        </div>

    </div>

</x-layouts.dashboard>
