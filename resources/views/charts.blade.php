<x-layouts.dashboard title="Charts">

    <x-slot name="breadcrumb">
        <x-breadcrumb>
            <x-breadcrumb.item><x-breadcrumb.link href="/dashboard">Dashboard</x-breadcrumb.link></x-breadcrumb.item>
            <x-breadcrumb.separator />
            <x-breadcrumb.item><x-breadcrumb.page>Charts</x-breadcrumb.page></x-breadcrumb.item>
        </x-breadcrumb>
    </x-slot>

    @php
        // ── Area — Tráfico por canal ─────────────────────────────────────────────────
        $trafficSeries = [
            ['name' => 'Orgánico',  'data' => [3200, 4100, 3800, 5200, 4800, 6100, 5500, 6800, 7200, 6500, 7800, 8400]],
            ['name' => 'Directo',   'data' => [1800, 2100, 1950, 2800, 2400, 3100, 2900, 3400, 3600, 3200, 3900, 4200]],
            ['name' => 'Referidos', 'data' => [900,  1100, 980,  1400, 1200, 1550, 1400, 1700, 1800, 1600, 1950, 2100]],
        ];
        $months12 = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

        // ── Line — Tasa de conversión por dispositivo ────────────────────────────────
        $conversionSeries = [
            ['name' => 'Desktop', 'data' => [4.2, 4.5, 4.1, 4.8, 5.2, 4.9, 5.5, 5.1]],
            ['name' => 'Mobile',  'data' => [2.1, 2.3, 2.0, 2.5, 2.8, 2.6, 3.1, 2.9]],
            ['name' => 'Tablet',  'data' => [3.1, 3.3, 3.0, 3.4, 3.7, 3.5, 3.9, 3.7]],
        ];
        $weeks8 = ['Sem 1','Sem 2','Sem 3','Sem 4','Sem 5','Sem 6','Sem 7','Sem 8'];

        // ── Bar agrupado — Presupuesto vs Gasto ─────────────────────────────────────
        $budgetSeries = [
            ['name' => 'Presupuesto', 'data' => [42000, 55000, 48000, 63000, 58000, 71000]],
            ['name' => 'Gasto real',  'data' => [39500, 57200, 45800, 66100, 54300, 68700]],
        ];
        $months6 = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'];

        // ── Bar horizontal — Top países ──────────────────────────────────────────────
        $countriesSeries     = [['name' => 'Ventas', 'data' => [8400, 6200, 4800, 3900, 3100, 2400]]];
        $countriesCategories = ['Argentina', 'México', 'Colombia', 'Chile', 'Perú', 'Uruguay'];
        $countriesColors     = ['#3b82f6', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#06b6d4'];

        // ── Bar apilado — Ingresos por categoría ─────────────────────────────────────
        $stackedSeries = [
            ['name' => 'Electrónica', 'data' => [12000, 15000, 13500, 16000, 14500, 18000]],
            ['name' => 'Ropa',        'data' => [8000,  9500,  8800,  10200, 9600,  11000]],
            ['name' => 'Hogar',       'data' => [5000,  5800,  5300,  6200,  5800,  6800]],
            ['name' => 'Otros',       'data' => [2500,  3000,  2800,  3200,  3000,  3500]],
        ];

        // ── Donut — Distribución de ventas ───────────────────────────────────────────
        $donutSeries     = [42, 28, 18, 12];
        $donutCategories = ['Electrónica', 'Ropa', 'Hogar', 'Otros'];

        // ── Pie — Ventas por región ───────────────────────────────────────────────────
        $pieSeries     = [35, 25, 20, 12, 8];
        $pieCategories = ['Buenos Aires', 'CABA', 'Córdoba', 'Rosario', 'Mendoza'];

        // ── RadialBar simple — Meta mensual ──────────────────────────────────────────
        $radialSingle           = [78];
        $radialSingleCategories = ['Meta mensual'];

        // ── RadialBar múltiple — KPIs de negocio ─────────────────────────────────────
        $radialMulti           = [86, 72, 58, 91];
        $radialMultiCategories = ['Conversión', 'Retención', 'Satisfacción', 'Crecimiento'];

        // ── Sparklines ───────────────────────────────────────────────────────────────
        $sparklines = [
            ['label' => 'Visitas únicas',   'value' => '48,291', 'change' => '+12%',  'up' => true,  'data' => [10,12,8,15,13,17,14,19,16,21,18,24]],
            ['label' => 'Tasa conversión',  'value' => '3.8%',   'change' => '+0.4%', 'up' => true,  'data' => [2.8,2.9,3.0,2.7,3.1,3.2,3.0,3.4,3.3,3.6,3.5,3.8]],
            ['label' => 'Bounce rate',      'value' => '42.1%',  'change' => '-3.2%', 'up' => false, 'data' => [52,50,49,48,51,46,47,44,45,43,42,42]],
            ['label' => 'Sesión promedio',  'value' => '4m 32s',  'change' => '+28s',  'up' => true,  'data' => [180,195,190,210,205,220,215,235,225,245,260,272]],
        ];

        // ── Radar — Comparativa de smartphones ───────────────────────────────────────
        $radarSeries = [
            ['name' => 'iPhone 15',   'data' => [82, 90, 88, 75, 60, 95]],
            ['name' => 'Samsung S24', 'data' => [78, 85, 92, 80, 72, 85]],
            ['name' => 'Pixel 8',     'data' => [70, 88, 95, 70, 85, 78]],
        ];
        $radarCategories = ['Diseño', 'Performance', 'Cámara', 'Batería', 'Precio', 'Soporte'];

        // ── Scatter — Inversión en marketing vs Ventas ────────────────────────────────
        $scatterSeries = [
            ['name' => 'Electrónica', 'data' => [[1200,8400],[1800,12000],[2400,16500],[3000,21000],[3600,25000],[4200,29500]]],
            ['name' => 'Ropa',        'data' => [[800,5200], [1200,7800], [1600,10400],[2000,13000],[2400,15600],[2800,18200]]],
            ['name' => 'Hogar',       'data' => [[600,3800], [900,5700],  [1200,7600], [1500,9500], [1800,11400],[2100,13300]]],
        ];

        // ── Heatmap — Actividad por día y hora ────────────────────────────────────────
        $heatmapData = [
            'Dom' => [5,  15, 30, 40, 35, 20, 10, 4],
            'Lun' => [8,  35, 75, 90, 70, 45, 15, 5],
            'Mar' => [10, 40, 80, 95, 75, 50, 18, 6],
            'Mié' => [8,  38, 78, 88, 72, 48, 16, 5],
            'Jue' => [12, 42, 82, 98, 78, 52, 20, 7],
            'Vie' => [15, 38, 72, 85, 68, 42, 25, 10],
            'Sáb' => [8,  20, 45, 55, 40, 25, 12, 5],
        ];
        $heatmapSeries = [];
        foreach ($heatmapData as $day => $values) {
            $heatmapSeries[] = ['name' => $day, 'data' => $values];
        }
        $heatmapCategories = ['06h', '09h', '12h', '15h', '18h', '21h', '00h', '03h'];

        // ── Mixed — Presupuesto (bar) + Gasto real (line) ────────────────────────────
        $mixedSeries = [
            ['name' => 'Presupuesto', 'type' => 'bar',  'data' => [42000, 55000, 48000, 63000, 58000, 71000]],
            ['name' => 'Gasto real',  'type' => 'line', 'data' => [39500, 57200, 45800, 66100, 54300, 68700]],
            ['name' => 'Objetivo',    'type' => 'line', 'data' => [45000, 52000, 50000, 60000, 62000, 70000]],
        ];
    @endphp

    <div class="space-y-8">

        {{-- Header --}}
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Charts</h1>
            <p class="text-muted-foreground text-sm mt-1">Showcase de todos los tipos de gráficos disponibles con ApexCharts.</p>
        </div>

        {{-- ── Sección: Área y Línea ─────────────────────────────────────────────── --}}
        <div class="space-y-3">
            <div>
                <h2 class="text-base font-semibold">Área y Línea</h2>
                <p class="text-sm text-muted-foreground">Ideales para mostrar tendencias a lo largo del tiempo. El área usa gradiente de relleno; la línea es más limpia para múltiples series.</p>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                <x-card>
                    <x-card.header>
                        <x-card.title>Tráfico por canal</x-card.title>
                        <x-card.description>Area — multi-serie con gradiente</x-card.description>
                    </x-card.header>
                    <x-card.content class="pt-0">
                        <x-chart
                            type="area"
                            :series="$trafficSeries"
                            :categories="$months12"
                            yformat="number"
                        />
                    </x-card.content>
                </x-card>

                <x-card>
                    <x-card.header>
                        <x-card.title>Conversión por dispositivo</x-card.title>
                        <x-card.description>Line — curva smooth, valores decimales</x-card.description>
                    </x-card.header>
                    <x-card.content class="pt-0">
                        <x-chart
                            type="line"
                            :series="$conversionSeries"
                            :categories="$weeks8"
                            yformat="percent"
                        />
                    </x-card.content>
                </x-card>

            </div>
        </div>

        {{-- ── Sección: Barras ─────────────────────────────────────────────────────── --}}
        <div class="space-y-3">
            <div>
                <h2 class="text-base font-semibold">Barras</h2>
                <p class="text-sm text-muted-foreground">Agrupadas para comparar categorías, horizontales para rankings, apiladas para mostrar composición.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                <x-card>
                    <x-card.header>
                        <x-card.title>Presupuesto vs Gasto real</x-card.title>
                        <x-card.description>Bar agrupado — dos series por período</x-card.description>
                    </x-card.header>
                    <x-card.content class="pt-0">
                        <x-chart
                            type="bar"
                            :series="$budgetSeries"
                            :categories="$months6"
                            yformat="currency"
                        />
                    </x-card.content>
                </x-card>

                <x-card>
                    <x-card.header>
                        <x-card.title>Top países por ventas</x-card.title>
                        <x-card.description>Bar horizontal — <code class="text-xs bg-muted px-1 rounded">horizontal distributed</code></x-card.description>
                    </x-card.header>
                    <x-card.content class="pt-0">
                        <x-chart
                            type="bar"
                            :series="$countriesSeries"
                            :categories="$countriesCategories"
                            :colors="$countriesColors"
                            :horizontal="true"
                            :distributed="true"
                            yformat="currency"
                            :height="260"
                        />
                    </x-card.content>
                </x-card>

            </div>

            <x-card>
                <x-card.header>
                    <div class="flex items-center justify-between">
                        <div>
                            <x-card.title>Ingresos por categoría</x-card.title>
                            <x-card.description>Bar apilado — <code class="text-xs bg-muted px-1 rounded">stacked</code> — composición acumulada por mes</x-card.description>
                        </div>
                        <x-badge variant="outline">6 meses</x-badge>
                    </div>
                </x-card.header>
                <x-card.content class="pt-0">
                    <x-chart
                        type="bar"
                        :series="$stackedSeries"
                        :categories="$months6"
                        :stacked="true"
                        yformat="currency"
                        :height="240"
                    />
                </x-card.content>
            </x-card>

        </div>

        {{-- ── Sección: Circulares ─────────────────────────────────────────────────── --}}
        <div class="space-y-3">
            <div>
                <h2 class="text-base font-semibold">Circulares</h2>
                <p class="text-sm text-muted-foreground">Donut y Pie para distribuciones. RadialBar para progreso hacia una meta.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <x-card>
                    <x-card.header>
                        <x-card.title>Distribución de ventas</x-card.title>
                        <x-card.description>Donut</x-card.description>
                    </x-card.header>
                    <x-card.content class="pt-0">
                        <x-chart
                            type="donut"
                            :series="$donutSeries"
                            :categories="$donutCategories"
                            :height="280"
                        />
                    </x-card.content>
                </x-card>

                <x-card>
                    <x-card.header>
                        <x-card.title>Meta de ventas</x-card.title>
                        <x-card.description>RadialBar — valor único</x-card.description>
                    </x-card.header>
                    <x-card.content class="pt-0">
                        <x-chart
                            type="radialBar"
                            :series="$radialSingle"
                            :categories="$radialSingleCategories"
                            :height="280"
                            :colors="['#3b82f6']"
                        />
                    </x-card.content>
                </x-card>

                <x-card>
                    <x-card.header>
                        <x-card.title>KPIs de negocio</x-card.title>
                        <x-card.description>RadialBar — multi-serie con promedio</x-card.description>
                    </x-card.header>
                    <x-card.content class="pt-0">
                        <x-chart
                            type="radialBar"
                            :series="$radialMulti"
                            :categories="$radialMultiCategories"
                            :height="280"
                        />
                    </x-card.content>
                </x-card>

            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <x-card>
                    <x-card.header>
                        <x-card.title>Ventas por región</x-card.title>
                        <x-card.description>Pie</x-card.description>
                    </x-card.header>
                    <x-card.content class="pt-0">
                        <x-chart
                            type="pie"
                            :series="$pieSeries"
                            :categories="$pieCategories"
                            :height="260"
                        />
                    </x-card.content>
                </x-card>

                <x-card>
                    <x-card.header>
                        <x-card.title>Cobertura de objetivos</x-card.title>
                        <x-card.description>RadialBar — con data labels activados</x-card.description>
                    </x-card.header>
                    <x-card.content class="pt-0">
                        <x-chart
                            type="radialBar"
                            :series="[92, 64, 81]"
                            :categories="['Revenue', 'Leads', 'NPS']"
                            :colors="['#10b981', '#f59e0b', '#3b82f6']"
                            :height="260"
                        />
                    </x-card.content>
                </x-card>

            </div>
        </div>

        {{-- ── Sección: Sparklines ─────────────────────────────────────────────────── --}}
        <div class="space-y-3">
            <div>
                <h2 class="text-base font-semibold">Sparklines</h2>
                <p class="text-sm text-muted-foreground">Mini charts sin ejes ni grilla, ideales para incrustar en KPI cards para mostrar la tendencia.</p>
            </div>
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
                @foreach($sparklines as $kpi)
                <x-card class="overflow-hidden">
                    <x-card.content class="pt-4 pb-0 px-4">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-medium text-muted-foreground">{{ $kpi['label'] }}</span>
                            <x-badge variant="{{ $kpi['up'] ? 'success' : 'destructive' }}" class="text-xs px-1.5 py-0">
                                {{ $kpi['up'] ? '↑' : '↓' }} {{ $kpi['change'] }}
                            </x-badge>
                        </div>
                        <div class="text-2xl font-bold">{{ $kpi['value'] }}</div>
                    </x-card.content>
                    <x-chart
                        type="area"
                        :series="[['name' => $kpi['label'], 'data' => $kpi['data']]]"
                        :sparkline="true"
                        :height="64"
                        :colors="[$kpi['up'] ? '#10b981' : '#ef4444']"
                        curve="smooth"
                    />
                </x-card>
                @endforeach
            </div>
        </div>

        {{-- ── Sección: Especializados ─────────────────────────────────────────────── --}}
        <div class="space-y-3">
            <div>
                <h2 class="text-base font-semibold">Especializados</h2>
                <p class="text-sm text-muted-foreground">Radar para comparar múltiples dimensiones. Scatter para correlaciones entre dos variables cuantitativas.</p>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                <x-card>
                    <x-card.header>
                        <x-card.title>Comparativa de smartphones</x-card.title>
                        <x-card.description>Radar — evaluación multi-dimensión</x-card.description>
                    </x-card.header>
                    <x-card.content class="pt-0">
                        <x-chart
                            type="radar"
                            :series="$radarSeries"
                            :categories="$radarCategories"
                            :height="300"
                        />
                    </x-card.content>
                </x-card>

                <x-card>
                    <x-card.header>
                        <x-card.title>Inversión en marketing vs Ventas</x-card.title>
                        <x-card.description>Scatter — correlación por categoría de producto</x-card.description>
                    </x-card.header>
                    <x-card.content class="pt-0">
                        <x-chart
                            type="scatter"
                            :series="$scatterSeries"
                            yformat="currency"
                            :height="300"
                        />
                    </x-card.content>
                </x-card>

            </div>
        </div>

        {{-- ── Sección: Heatmap ────────────────────────────────────────────────────── --}}
        <div class="space-y-3">
            <div>
                <h2 class="text-base font-semibold">Heatmap</h2>
                <p class="text-sm text-muted-foreground">Intensidad de color para representar densidad. Útil para visualizar actividad por día/hora.</p>
            </div>
            <x-card>
                <x-card.header>
                    <div class="flex items-center justify-between">
                        <div>
                            <x-card.title>Actividad de usuarios por día y hora</x-card.title>
                            <x-card.description>Heatmap — picos en horario laboral, baja actividad nocturna y fines de semana</x-card.description>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-muted-foreground">
                            <span class="flex items-center gap-1.5"><span class="inline-block h-3 w-3 rounded-sm bg-[#93c5fd]"></span>Bajo</span>
                            <span class="flex items-center gap-1.5"><span class="inline-block h-3 w-3 rounded-sm bg-[#3b82f6]"></span>Medio</span>
                            <span class="flex items-center gap-1.5"><span class="inline-block h-3 w-3 rounded-sm bg-[#1e3a8a]"></span>Máximo</span>
                        </div>
                    </div>
                </x-card.header>
                <x-card.content class="pt-0">
                    <x-chart
                        type="heatmap"
                        :series="$heatmapSeries"
                        :categories="$heatmapCategories"
                        :height="280"
                    />
                </x-card.content>
            </x-card>
        </div>

        {{-- ── Sección: Mixed / Combo ───────────────────────────────────────────────── --}}
        <div class="space-y-3">
            <div>
                <h2 class="text-base font-semibold">Combinado (Mixed)</h2>
                <p class="text-sm text-muted-foreground">Combina tipos distintos por serie. Cada serie declara su propio <code class="text-xs bg-muted px-1 rounded">type</code>: bar, line, area.</p>
            </div>
            <x-card>
                <x-card.header>
                    <div class="flex items-center justify-between">
                        <div>
                            <x-card.title>Presupuesto, Gasto real y Objetivo</x-card.title>
                            <x-card.description>Mixed — barras (presupuesto) + líneas (gasto real y objetivo)</x-card.description>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-muted-foreground">
                            <span class="flex items-center gap-1.5">
                                <span class="inline-block h-3 w-6 rounded-sm bg-[#3b82f6]"></span>Presupuesto
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="inline-block h-0.5 w-6 bg-[#8b5cf6]"></span>Gasto real
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="inline-block h-0.5 w-6 bg-[#10b981]"></span>Objetivo
                            </span>
                        </div>
                    </div>
                </x-card.header>
                <x-card.content class="pt-0">
                    <x-chart
                        type="mixed"
                        :series="$mixedSeries"
                        :categories="$months6"
                        yformat="currency"
                        :height="280"
                    />
                </x-card.content>
            </x-card>
        </div>

    </div>

</x-layouts.dashboard>
