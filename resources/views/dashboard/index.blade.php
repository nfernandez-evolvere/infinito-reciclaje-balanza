<x-layouts.app-sidebar title="Dashboard">

    <x-slot:breadcrumb>
        <x-ui.breadcrumb>
            <x-ui.breadcrumb.list>
                <x-ui.breadcrumb.item>
                    <x-ui.breadcrumb.page>Dashboard</x-ui.breadcrumb.page>
                </x-ui.breadcrumb.item>
            </x-ui.breadcrumb.list>
        </x-ui.breadcrumb>
    </x-slot:breadcrumb>

    @php
    $metrics = [
        ['label' => 'Ingresos totales',   'value' => '$48.250', 'change' => '+12.5%', 'state' => 'success',     'progress' => 68, 'icon' => 'dollar-sign'],
        ['label' => 'Usuarios activos',   'value' => '2.847',   'change' => '+8.2%',  'state' => 'success',     'progress' => 84, 'icon' => 'users'],
        ['label' => 'Nuevos registros',   'value' => '312',     'change' => '-3.1%',  'state' => 'destructive', 'progress' => 45, 'icon' => 'user-plus'],
        ['label' => 'Tasa de conversión', 'value' => '3.24%',   'change' => '+0.8%',  'state' => 'success',     'progress' => 32, 'icon' => 'trending-up'],
    ];

    $chartData = [
        'labels'   => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago'],
        'datasets' => [
            [
                'label'           => 'Ingresos',
                'data'            => [12400, 14800, 13200, 16500, 15800, 19200, 21300, 18900],
                'backgroundColor' => 'var(--chart-1)',
                'borderRadius'    => 4,
                'borderSkipped'   => false,
            ],
            [
                'label'           => 'Gastos',
                'data'            => [8200, 9100, 8600, 10200, 9800, 11500, 13100, 11200],
                'backgroundColor' => 'var(--chart-2)',
                'borderRadius'    => 4,
                'borderSkipped'   => false,
            ],
        ],
    ];

    $chartOptions = [
        'plugins' => [
            'legend'  => ['display' => true, 'position' => 'top', 'labels' => ['color' => 'var(--foreground)', 'padding' => 16, 'usePointStyle' => true]],
            'tooltip' => ['backgroundColor' => 'var(--popover)', 'titleColor' => 'var(--popover-foreground)', 'bodyColor' => 'var(--muted-foreground)', 'borderColor' => 'var(--border)', 'borderWidth' => 1, 'padding' => 10],
        ],
        'scales' => [
            'x' => ['grid' => ['display' => false], 'ticks' => ['color' => 'var(--muted-foreground)'], 'border' => ['display' => false]],
            'y' => ['beginAtZero' => true, 'grid' => ['color' => 'var(--border)'], 'ticks' => ['color' => 'var(--muted-foreground)'], 'border' => ['display' => false]],
        ],
    ];

    $taskGroups = [
        'Urgente' => [
            ['title' => 'Revisar pagos vencidos del mes',    'priority' => 'destructive', 'priority_label' => 'Urgente'],
            ['title' => 'Actualizar política de privacidad', 'priority' => 'warning',     'priority_label' => 'Alta'],
        ],
        'Pendiente' => [
            ['title' => 'Configurar integración Stripe',     'priority' => 'info',        'priority_label' => 'Normal'],
            ['title' => 'Migrar base de datos a v2',         'priority' => 'info',        'priority_label' => 'Normal'],
            ['title' => 'Documentar API de webhooks',        'priority' => 'secondary',   'priority_label' => 'Baja'],
        ],
    ];

    $tableColumns = [
        ['key' => 'name',   'label' => 'Nombre',  'sortable' => true],
        ['key' => 'email',  'label' => 'Email',   'sortable' => true],
        ['key' => 'action', 'label' => 'Acción',  'sortable' => true],
        ['key' => 'status', 'label' => 'Estado',  'sortable' => true],
        ['key' => 'time',   'label' => 'Tiempo',  'sortable' => false, 'hideable' => true],
    ];

    $tableData = [
        ['name' => 'María García',     'email' => 'maria@ejemplo.com',     'action' => 'Nuevo registro',      'status' => 'Activo',    'time' => 'hace 2 min'],
        ['name' => 'Carlos López',     'email' => 'carlos@ejemplo.com',    'action' => 'Pago procesado',      'status' => 'Pagado',    'time' => 'hace 15 min'],
        ['name' => 'Ana Rodríguez',    'email' => 'ana@ejemplo.com',       'action' => 'Solicitud pendiente', 'status' => 'Pendiente', 'time' => 'hace 1 h'],
        ['name' => 'Pedro Martínez',   'email' => 'pedro@ejemplo.com',     'action' => 'Cuenta cancelada',    'status' => 'Inactivo',  'time' => 'hace 3 h'],
        ['name' => 'Laura Sánchez',    'email' => 'laura@ejemplo.com',     'action' => 'Nuevo registro',      'status' => 'Activo',    'time' => 'hace 5 h'],
        ['name' => 'Diego Fernández',  'email' => 'diego@ejemplo.com',     'action' => 'Actualización',       'status' => 'Activo',    'time' => 'hace 6 h'],
        ['name' => 'Sofía González',   'email' => 'sofia@ejemplo.com',     'action' => 'Pago procesado',      'status' => 'Pagado',    'time' => 'ayer'],
        ['name' => 'Martín Torres',    'email' => 'martin@ejemplo.com',    'action' => 'Solicitud pendiente', 'status' => 'Pendiente', 'time' => 'ayer'],
        ['name' => 'Valentina Ruiz',   'email' => 'valentina@ejemplo.com', 'action' => 'Nuevo registro',      'status' => 'Activo',    'time' => 'ayer'],
        ['name' => 'Nicolás Herrera',  'email' => 'nicolas@ejemplo.com',   'action' => 'Pago procesado',      'status' => 'Pagado',    'time' => 'hace 2 días'],
        ['name' => 'Camila Morales',   'email' => 'camila@ejemplo.com',    'action' => 'Cuenta cancelada',    'status' => 'Inactivo',  'time' => 'hace 2 días'],
        ['name' => 'Sebastián Castro', 'email' => 'sebastian@ejemplo.com', 'action' => 'Actualización',       'status' => 'Activo',    'time' => 'hace 3 días'],
    ];
    @endphp

    <div data-density="compact" class="max-w-[--container-xl] px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- ── Page header ──────────────────────────────────────────────── --}}
        <div class="flex items-start justify-between gap-4">
            <div class="space-y-1">
                <x-ui.typography as="h1">Dashboard</x-ui.typography>
                <x-ui.typography as="muted" class="max-w-prose">
                    Resumen general de tu negocio. Últimos 30 días.
                </x-ui.typography>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <x-ui.button variant="outline" size="sm">
                    <x-lucide-calendar class="size-4" />
                    30 días
                </x-ui.button>
                <x-ui.button size="sm">
                    <x-lucide-download class="size-4" />
                    Exportar
                </x-ui.button>
            </div>
        </div>

        {{-- ── Metric cards ──────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach($metrics as $metric)
            <x-ui.card>
                <x-ui.card.content class="p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <x-ui.typography as="muted" class="text-sm font-medium">{{ $metric['label'] }}</x-ui.typography>
                        <div class="size-8 rounded-md bg-muted flex items-center justify-center text-muted-foreground">
                            <x-dynamic-component :component="'lucide-' . $metric['icon']" class="size-4" />
                        </div>
                    </div>
                    <div class="space-y-1">
                        <x-ui.typography as="h3" class="text-2xl font-bold tracking-tight">{{ $metric['value'] }}</x-ui.typography>
                        <div class="flex items-center gap-2">
                            <x-ui.badge :state="$metric['state']" :subtle="true">{{ $metric['change'] }}</x-ui.badge>
                            <x-ui.typography as="muted" class="text-xs">vs mes anterior</x-ui.typography>
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <x-ui.progress :value="$metric['progress']" size="sm" />
                        <x-ui.typography as="muted" class="text-xs">{{ $metric['progress'] }}% del objetivo mensual</x-ui.typography>
                    </div>
                </x-ui.card.content>
            </x-ui.card>
            @endforeach
        </div>

        {{-- ── Chart + Tasks ─────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Chart --}}
            <x-ui.card class="lg:col-span-2">
                <x-ui.card.header>
                    <div class="flex items-start justify-between gap-4">
                        <div class="space-y-1">
                            <x-ui.card.title>Ingresos vs Gastos</x-ui.card.title>
                            <x-ui.card.description>Comparativa mensual del año actual</x-ui.card.description>
                        </div>
                        <x-ui.button-group>
                            <x-ui.button size="sm" variant="outline">Sem</x-ui.button>
                            <x-ui.button size="sm">Mes</x-ui.button>
                            <x-ui.button size="sm" variant="outline">Año</x-ui.button>
                        </x-ui.button-group>
                    </div>
                </x-ui.card.header>
                <x-ui.card.content>
                    <x-ui.chart type="bar" :data="$chartData" :options="$chartOptions" height="260px" />
                </x-ui.card.content>
            </x-ui.card>

            {{-- Tasks --}}
            <x-ui.card class="flex flex-col">
                <x-ui.card.header>
                    <div class="flex items-center justify-between">
                        <x-ui.card.title>Tareas pendientes</x-ui.card.title>
                        <x-ui.badge state="warning" :subtle="true">5</x-ui.badge>
                    </div>
                </x-ui.card.header>
                <x-ui.card.content class="flex-1 space-y-3">
                    @foreach($taskGroups as $groupLabel => $tasks)
                    <x-ui.collapsible :open="true">
                        <div class="flex items-center justify-between py-0.5">
                            <x-ui.typography as="section-label" element="p">{{ $groupLabel }}</x-ui.typography>
                            <x-ui.collapsible.trigger class="text-muted-foreground hover:text-foreground rounded p-0.5 transition-colors">
                                <x-lucide-chevron-down class="size-3.5 transition-transform" x-bind:class="open ? '' : '-rotate-90'" />
                            </x-ui.collapsible.trigger>
                        </div>
                        <x-ui.collapsible.content>
                            <div class="space-y-1 pt-1">
                                @foreach($tasks as $task)
                                <label class="flex items-start gap-2.5 rounded-md px-1 py-1.5 cursor-pointer hover:bg-accent transition-colors">
                                    <x-ui.checkbox class="mt-0.5 shrink-0" />
                                    <div class="flex-1 min-w-0 space-y-1">
                                        <span class="text-sm text-foreground leading-snug">{{ $task['title'] }}</span>
                                        <div>
                                            <x-ui.badge :state="$task['priority']" :subtle="true" class="text-[10px] py-0">
                                                {{ $task['priority_label'] }}
                                            </x-ui.badge>
                                        </div>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </x-ui.collapsible.content>
                    </x-ui.collapsible>
                    @if(!$loop->last)
                        <x-ui.separator />
                    @endif
                    @endforeach
                </x-ui.card.content>
                <x-ui.card.footer>
                    <x-ui.button variant="ghost" size="sm" class="w-full text-muted-foreground">
                        Ver todas las tareas
                        <x-lucide-arrow-right class="size-4" />
                    </x-ui.button>
                </x-ui.card.footer>
            </x-ui.card>

        </div>

        {{-- ── Data Table ─────────────────────────────────────────────────── --}}
        <x-ui.card>
            <x-ui.card.header>
                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <x-ui.card.title>Actividad reciente</x-ui.card.title>
                        <x-ui.card.description>Todas las acciones registradas en la plataforma.</x-ui.card.description>
                    </div>
                    <x-ui.button variant="outline" size="sm">
                        <x-lucide-download class="size-4" />
                        Exportar CSV
                    </x-ui.button>
                </div>
            </x-ui.card.header>
            <x-ui.card.content>
                <x-ui.data-table
                    :columns="$tableColumns"
                    :data="$tableData"
                    :selectable="true"
                    :per-page="10"
                    search-placeholder="Buscar por nombre, email o acción..."
                />
            </x-ui.card.content>
        </x-ui.card>

    </div>

</x-layouts.app-sidebar>
