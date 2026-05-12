<x-layouts.dashboard title="Ingresos">

    <x-slot name="breadcrumb">
        <x-breadcrumb>
            <x-breadcrumb.item><x-breadcrumb.link href="/dashboard">Dashboard</x-breadcrumb.link></x-breadcrumb.item>
            <x-breadcrumb.separator />
            <x-breadcrumb.item><x-breadcrumb.page>Ingresos</x-breadcrumb.page></x-breadcrumb.item>
        </x-breadcrumb>
    </x-slot>

    @php
        $monthly = [
            ['month'=>'Ene','revenue'=>18400,'orders'=>86, 'avg'=>214],
            ['month'=>'Feb','revenue'=>22100,'orders'=>103,'avg'=>215],
            ['month'=>'Mar','revenue'=>27300,'orders'=>127,'avg'=>215],
            ['month'=>'Abr','revenue'=>24600,'orders'=>115,'avg'=>214],
            ['month'=>'May','revenue'=>30000,'orders'=>140,'avg'=>214],
            ['month'=>'Jun','revenue'=>27900,'orders'=>130,'avg'=>215],
            ['month'=>'Jul','revenue'=>20400,'orders'=>95, 'avg'=>215],
            ['month'=>'Ago','revenue'=>23100,'orders'=>108,'avg'=>214],
            ['month'=>'Sep','revenue'=>25500,'orders'=>119,'avg'=>214],
            ['month'=>'Oct','revenue'=>27900,'orders'=>130,'avg'=>215],
            ['month'=>'Nov','revenue'=>30000,'orders'=>140,'avg'=>214],
            ['month'=>'Dic','revenue'=>13500,'orders'=>63, 'avg'=>214],
        ];

        $totalRevenue = array_sum(array_column($monthly, 'revenue'));
        $totalOrders  = array_sum(array_column($monthly, 'orders'));
        $avgMonthly   = $totalRevenue / 12;
        $avgTicket    = $totalRevenue / $totalOrders;

        $breakdown = [
            ['category'=>'Electrónica', 'orders'=>876, 'revenue'=>187410,'pct'=>70.1,'trend'=>'up',  'change'=>'+18.2%'],
            ['category'=>'Ropa',        'orders'=>498, 'revenue'=>44820, 'pct'=>16.7,'trend'=>'up',  'change'=>'+9.4%'],
            ['category'=>'Hogar',       'orders'=>234, 'revenue'=>28080, 'pct'=>10.5,'trend'=>'down','change'=>'-2.1%'],
            ['category'=>'Deportes',    'orders'=>72,  'revenue'=>7200,  'pct'=>2.7, 'trend'=>'up',  'change'=>'+34.0%'],
        ];

        $revenueSeries  = [['name'=>'Ingresos', 'data'=>array_column($monthly, 'revenue')]];
        $months         = array_column($monthly, 'month');
        $ordersSeries   = [['name'=>'Pedidos',  'data'=>array_column($monthly, 'orders')]];

        $categoryRevSeries   = [['name'=>'Ingresos', 'data'=>array_column($breakdown, 'revenue')]];
        $categoryNames       = array_column($breakdown, 'category');
    @endphp

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Ingresos</h1>
                <p class="text-muted-foreground text-sm">Análisis financiero del ejercicio 2026.</p>
            </div>
            <div class="flex items-center gap-2">
                <x-button variant="outline" size="sm"
                    @click="$dispatch('toast', { message: 'Reporte generado', variant: 'success' })">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                    Exportar reporte
                </x-button>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
            <x-card>
                <x-card.content class="pt-6">
                    <p class="text-sm font-medium text-muted-foreground">Ingresos anuales</p>
                    <p class="text-3xl font-bold mt-1">${{ number_format($totalRevenue, 0, ',', '.') }}</p>
                    <div class="flex items-center gap-1.5 mt-1">
                        <x-badge variant="success" class="text-xs px-1.5">↑ +14.3%</x-badge>
                        <span class="text-xs text-muted-foreground">vs 2025</span>
                    </div>
                </x-card.content>
            </x-card>
            <x-card>
                <x-card.content class="pt-6">
                    <p class="text-sm font-medium text-muted-foreground">Promedio mensual</p>
                    <p class="text-3xl font-bold mt-1">${{ number_format($avgMonthly, 0, ',', '.') }}</p>
                    <p class="text-xs text-muted-foreground mt-1">en 12 meses</p>
                </x-card.content>
            </x-card>
            <x-card>
                <x-card.content class="pt-6">
                    <p class="text-sm font-medium text-muted-foreground">Ticket promedio</p>
                    <p class="text-3xl font-bold mt-1">${{ number_format($avgTicket, 0) }}</p>
                    <div class="flex items-center gap-1.5 mt-1">
                        <x-badge variant="success" class="text-xs px-1.5">↑ +3.9%</x-badge>
                        <span class="text-xs text-muted-foreground">vs 2025</span>
                    </div>
                </x-card.content>
            </x-card>
            <x-card>
                <x-card.content class="pt-6">
                    <p class="text-sm font-medium text-muted-foreground">Margen neto</p>
                    <p class="text-3xl font-bold mt-1">28.4%</p>
                    <div class="flex items-center gap-1.5 mt-1">
                        <x-badge variant="destructive" class="text-xs px-1.5">↓ -1.2%</x-badge>
                        <span class="text-xs text-muted-foreground">vs 2025</span>
                    </div>
                </x-card.content>
            </x-card>
        </div>

        {{-- Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            <x-card class="lg:col-span-2">
                <x-card.header>
                    <div class="flex items-center justify-between">
                        <div>
                            <x-card.title>Ingresos por mes</x-card.title>
                            <x-card.description>Enero — Diciembre 2026</x-card.description>
                        </div>
                        <x-badge variant="outline">${{ number_format($totalRevenue, 0, ',', '.') }} total</x-badge>
                    </div>
                </x-card.header>
                <x-card.content class="pt-0">
                    <x-chart
                        type="area"
                        :series="$revenueSeries"
                        :categories="$months"
                        yformat="currency"
                        :height="240"
                    />
                </x-card.content>
            </x-card>

            <x-card>
                <x-card.header>
                    <x-card.title>Ingresos por categoría</x-card.title>
                    <x-card.description>Participación en el total anual</x-card.description>
                </x-card.header>
                <x-card.content class="pt-0">
                    <x-chart
                        type="donut"
                        :series="array_column($breakdown, 'revenue')"
                        :categories="$categoryNames"
                        :height="240"
                    />
                </x-card.content>
            </x-card>

        </div>

        {{-- Pedidos por mes --}}
        <x-card>
            <x-card.header>
                <x-card.title>Volumen de pedidos</x-card.title>
                <x-card.description>{{ $totalOrders }} pedidos cobrados en 2026</x-card.description>
            </x-card.header>
            <x-card.content class="pt-0">
                <x-chart
                    type="bar"
                    :series="$ordersSeries"
                    :categories="$months"
                    :height="200"
                />
            </x-card.content>
        </x-card>

        {{-- Desglose por categoría --}}
        <x-card>
            <x-card.header>
                <x-card.title>Desglose por categoría</x-card.title>
                <x-card.description>Rendimiento por línea de producto en 2026</x-card.description>
            </x-card.header>
            <x-card.content class="p-0">
                <x-table>
                    <x-table.header>
                        <x-table.row>
                            <x-table.head>Categoría</x-table.head>
                            <x-table.head>Pedidos</x-table.head>
                            <x-table.head>Ingresos</x-table.head>
                            <x-table.head>% del total</x-table.head>
                            <x-table.head>Participación</x-table.head>
                            <x-table.head>vs año anterior</x-table.head>
                        </x-table.row>
                    </x-table.header>
                    <x-table.body>
                        @foreach($breakdown as $row)
                        <x-table.row>
                            <x-table.cell class="font-medium">{{ $row['category'] }}</x-table.cell>
                            <x-table.cell class="text-muted-foreground">{{ number_format($row['orders'], 0, ',', '.') }}</x-table.cell>
                            <x-table.cell class="font-semibold">${{ number_format($row['revenue'], 0, ',', '.') }}</x-table.cell>
                            <x-table.cell class="text-muted-foreground">{{ $row['pct'] }}%</x-table.cell>
                            <x-table.cell>
                                <div class="flex items-center gap-2 w-32">
                                    <x-progress :value="$row['pct']" class="flex-1 h-1.5" />
                                    <span class="text-xs text-muted-foreground w-8 shrink-0">{{ $row['pct'] }}%</span>
                                </div>
                            </x-table.cell>
                            <x-table.cell>
                                <x-badge variant="{{ $row['trend'] === 'up' ? 'success' : 'destructive' }}" class="text-xs">
                                    {{ $row['trend'] === 'up' ? '↑' : '↓' }} {{ $row['change'] }}
                                </x-badge>
                            </x-table.cell>
                        </x-table.row>
                        @endforeach
                    </x-table.body>
                </x-table>
            </x-card.content>
        </x-card>

    </div>
</x-layouts.dashboard>
