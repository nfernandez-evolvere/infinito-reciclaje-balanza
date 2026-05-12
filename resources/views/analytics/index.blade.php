<x-layouts.app-sidebar title="Analytics">

    <x-slot:breadcrumb>
        <x-ui.breadcrumb>
            <x-ui.breadcrumb.list>
                <x-ui.breadcrumb.item>
                    <x-ui.breadcrumb.page>Analytics</x-ui.breadcrumb.page>
                </x-ui.breadcrumb.item>
            </x-ui.breadcrumb.list>
        </x-ui.breadcrumb>
    </x-slot:breadcrumb>

    @php
    $sparkOpts = [
        'plugins' => ['legend' => ['display' => false], 'tooltip' => ['enabled' => false]],
        'scales'  => ['x' => ['display' => false], 'y' => ['display' => false]],
        'elements'=> ['point' => ['radius' => 0]],
    ];

    $makeSpark = fn(array $data, string $color) => [
        'labels'   => array_fill(0, count($data), ''),
        'datasets' => [[
            'data'              => $data,
            'borderColor'       => $color,
            'backgroundColor'   => $color . '20',
            'fill'              => true,
            'tension'           => 0.4,
            'borderWidth'       => 1.5,
        ]],
    ];

    $kpis = [
        ['label' => 'Usuarios únicos',   'value' => '14.820', 'change' => '+18.2%', 'state' => 'success',     'spark' => $makeSpark([420,380,460,490,440,520,580,610,570,630], 'var(--chart-1)')],
        ['label' => 'Sesiones',          'value' => '28.430', 'change' => '+12.5%', 'state' => 'success',     'spark' => $makeSpark([800,750,820,900,860,950,1020,1080,1010,1120], 'var(--chart-2)')],
        ['label' => 'Tasa de rebote',    'value' => '38.4%',  'change' => '-4.1%',  'state' => 'success',     'spark' => $makeSpark([55,52,50,48,47,45,43,40,39,38], 'var(--chart-3)')],
        ['label' => 'Conversiones',      'value' => '1.243',  'change' => '-2.3%',  'state' => 'destructive', 'spark' => $makeSpark([120,118,125,110,115,108,112,105,108,103], 'var(--chart-4)')],
    ];

    $mainChartData = [
        'labels'   => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago'],
        'datasets' => [
            ['label' => 'Usuarios', 'data' => [9800, 11200, 10500, 13400, 12800, 15600, 17200, 14820], 'backgroundColor' => 'var(--chart-1)', 'borderRadius' => 4, 'borderSkipped' => false],
            ['label' => 'Sesiones', 'data' => [18200, 21500, 20100, 25800, 24200, 29800, 32400, 28430],'backgroundColor' => 'var(--chart-2)', 'borderRadius' => 4, 'borderSkipped' => false],
        ],
    ];

    $mainChartOpts = [
        'plugins' => [
            'legend'  => ['display' => true, 'position' => 'top', 'labels' => ['color' => 'var(--foreground)', 'padding' => 16, 'usePointStyle' => true]],
            'tooltip' => ['backgroundColor' => 'var(--popover)', 'titleColor' => 'var(--popover-foreground)', 'bodyColor' => 'var(--muted-foreground)', 'borderColor' => 'var(--border)', 'borderWidth' => 1],
        ],
        'scales' => [
            'x' => ['grid' => ['display' => false], 'ticks' => ['color' => 'var(--muted-foreground)'], 'border' => ['display' => false]],
            'y' => ['beginAtZero' => true, 'grid' => ['color' => 'var(--border)'], 'ticks' => ['color' => 'var(--muted-foreground)'], 'border' => ['display' => false]],
        ],
    ];

    $tableData = [
        ['source' => 'Búsqueda orgánica', 'sessions' => '12.840', 'bounce' => '34.2%', 'conversion' => '3.8%', 'change' => '+22%'],
        ['source' => 'Directo',           'sessions' => '7.620',  'bounce' => '28.5%', 'conversion' => '5.1%', 'change' => '+8%'],
        ['source' => 'Redes sociales',    'sessions' => '4.310',  'bounce' => '52.1%', 'conversion' => '1.9%', 'change' => '+14%'],
        ['source' => 'Email marketing',   'sessions' => '2.890',  'bounce' => '22.8%', 'conversion' => '7.4%', 'change' => '+31%'],
        ['source' => 'Referidos',         'sessions' => '780',    'bounce' => '41.3%', 'conversion' => '2.6%', 'change' => '-3%'],
    ];
    @endphp

    <div data-density="compact" class="max-w-[--container-xl] px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- Page header --}}
        <div class="flex items-start justify-between gap-4">
            <div class="space-y-1">
                <x-ui.typography as="h1">Analytics</x-ui.typography>
                <x-ui.typography as="muted" class="max-w-prose">Métricas y rendimiento de tu plataforma.</x-ui.typography>
            </div>
            <div class="flex items-center gap-2 shrink-0 flex-wrap justify-end">
                {{-- Selector de rango --}}
                <div class="hidden sm:flex items-center gap-2">
                    <x-ui.date-picker value="2024-06-01" placeholder="Desde" />
                    <x-ui.typography as="muted" class="text-sm">→</x-ui.typography>
                    <x-ui.date-picker value="2024-06-30" placeholder="Hasta" />
                </div>
                <x-ui.dropdown-menu align="end">
                    <x-ui.dropdown-menu.trigger>
                        <x-ui.button variant="outline" size="sm">
                            <x-lucide-download class="size-4" />
                            Exportar
                        </x-ui.button>
                    </x-ui.dropdown-menu.trigger>
                    <x-ui.dropdown-menu.content>
                        <x-ui.dropdown-menu.item><x-lucide-file-spreadsheet class="size-4" /> Exportar CSV</x-ui.dropdown-menu.item>
                        <x-ui.dropdown-menu.item><x-lucide-file-text class="size-4" /> Exportar PDF</x-ui.dropdown-menu.item>
                        <x-ui.dropdown-menu.separator />
                        <x-ui.dropdown-menu.item><x-lucide-image class="size-4" /> Guardar imagen</x-ui.dropdown-menu.item>
                    </x-ui.dropdown-menu.content>
                </x-ui.dropdown-menu>
            </div>
        </div>

        {{-- KPI cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach($kpis as $kpi)
                <x-analytics.kpi-card
                    :label="$kpi['label']"
                    :value="$kpi['value']"
                    :change="$kpi['change']"
                    :state="$kpi['state']"
                    :chart-data="$kpi['spark']"
                    :chart-opts="$sparkOpts"
                />
            @endforeach
        </div>

        {{-- Gráfico principal --}}
        <x-ui.card>
            <x-ui.card.header>
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-1">
                        <x-ui.card.title>Usuarios y Sesiones</x-ui.card.title>
                        <x-ui.card.description>Evolución mensual del año actual</x-ui.card.description>
                    </div>
                    <x-ui.button-group>
                        <x-ui.button size="sm">Mes</x-ui.button>
                        <x-ui.button size="sm" variant="outline">Semana</x-ui.button>
                        <x-ui.button size="sm" variant="outline">Año</x-ui.button>
                    </x-ui.button-group>
                </div>
            </x-ui.card.header>
            <x-ui.card.content>
                <x-ui.chart type="bar" :data="$mainChartData" :options="$mainChartOpts" height="280px" />
            </x-ui.card.content>
        </x-ui.card>

        {{-- Tabs de detalle --}}
        <x-ui.tabs value="sources">
            <x-ui.tabs.list>
                <x-ui.tabs.trigger value="sources">Por fuente</x-ui.tabs.trigger>
                <x-ui.tabs.trigger value="pages">Por página</x-ui.tabs.trigger>
                <x-ui.tabs.trigger value="devices">Por dispositivo</x-ui.tabs.trigger>
            </x-ui.tabs.list>

            <x-ui.tabs.content value="sources">
                <x-ui.card class="mt-0">
                    <x-ui.card.content class="p-0">
                        <x-ui.table>
                            <x-ui.table.header>
                                <x-ui.table.row class="hover:bg-transparent">
                                    <x-ui.table.head class="pl-6">Fuente</x-ui.table.head>
                                    <x-ui.table.head>Sesiones</x-ui.table.head>
                                    <x-ui.table.head>Rebote</x-ui.table.head>
                                    <x-ui.table.head>Conversión</x-ui.table.head>
                                    <x-ui.table.head class="text-right pr-6">Variación</x-ui.table.head>
                                </x-ui.table.row>
                            </x-ui.table.header>
                            <x-ui.table.body>
                                @foreach($tableData as $row)
                                <x-ui.table.row>
                                    <x-ui.table.cell class="pl-6 font-medium">{{ $row['source'] }}</x-ui.table.cell>
                                    <x-ui.table.cell>{{ $row['sessions'] }}</x-ui.table.cell>
                                    <x-ui.table.cell>{{ $row['bounce'] }}</x-ui.table.cell>
                                    <x-ui.table.cell>{{ $row['conversion'] }}</x-ui.table.cell>
                                    <x-ui.table.cell class="text-right pr-6">
                                        <x-ui.badge
                                            :state="str_starts_with($row['change'], '+') ? 'success' : 'destructive'"
                                            :subtle="true"
                                        >{{ $row['change'] }}</x-ui.badge>
                                    </x-ui.table.cell>
                                </x-ui.table.row>
                                @endforeach
                            </x-ui.table.body>
                        </x-ui.table>
                    </x-ui.card.content>
                </x-ui.card>
            </x-ui.tabs.content>

            <x-ui.tabs.content value="pages">
                <div class="mt-0">
                    <x-ui.empty-state title="Sin datos" description="Seleccioná un rango de fechas para ver las páginas más visitadas." icon="file-text" />
                </div>
            </x-ui.tabs.content>

            <x-ui.tabs.content value="devices">
                <div class="mt-0">
                    <x-ui.empty-state title="Sin datos" description="Los datos por dispositivo estarán disponibles próximamente." icon="monitor" />
                </div>
            </x-ui.tabs.content>
        </x-ui.tabs>

    </div>

</x-layouts.app-sidebar>
