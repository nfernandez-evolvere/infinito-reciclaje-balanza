<x-layouts.showcase title="Chart — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Chart</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Wrapper sobre Chart.js. Soporta line, bar, pie, doughnut, radar y polarArea. Los datos y opciones se pasan como props PHP y se pasan a Chart.js via Alpine.</x-ui.typography>
    </div>

    {{-- Line --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Line — ventas mensuales</x-ui.typography>
        <x-ui.card>
            <x-ui.card.content class="pt-6">
                <x-ui.chart
                    type="line"
                    :data="[
                        'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                        'datasets' => [[
                            'label'           => 'Ventas 2025',
                            'data'            => [4200, 5800, 4900, 7100, 6300, 8200],
                            'borderColor'     => 'oklch(0.585 0.233 277)',
                            'backgroundColor' => 'oklch(0.585 0.233 277 / 10%)',
                            'fill'            => true,
                            'tension'         => 0.4,
                            'pointRadius'     => 4,
                        ], [
                            'label'           => 'Ventas 2024',
                            'data'            => [3100, 4200, 3800, 5400, 4900, 6100],
                            'borderColor'     => 'oklch(0.723 0.219 142)',
                            'backgroundColor' => 'oklch(0.723 0.219 142 / 10%)',
                            'fill'            => true,
                            'tension'         => 0.4,
                            'pointRadius'     => 4,
                            'borderDash'      => [6, 3],
                        ]],
                    ]"
                    height="280px"
                />
            </x-ui.card.content>
        </x-ui.card>
    </section>

    <x-ui.separator />

    {{-- Bar --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Bar — ingresos por canal</x-ui.typography>
        <x-ui.card>
            <x-ui.card.content class="pt-6">
                <x-ui.chart
                    type="bar"
                    :data="[
                        'labels' => ['Q1', 'Q2', 'Q3', 'Q4'],
                        'datasets' => [[
                            'label'           => 'Directo',
                            'data'            => [12000, 19000, 14000, 22000],
                            'backgroundColor' => 'oklch(0.585 0.233 277 / 80%)',
                            'borderRadius'    => 4,
                        ], [
                            'label'           => 'Orgánico',
                            'data'            => [8000, 11000, 9500, 15000],
                            'backgroundColor' => 'oklch(0.723 0.219 142 / 80%)',
                            'borderRadius'    => 4,
                        ], [
                            'label'           => 'Referidos',
                            'data'            => [3000, 5000, 4200, 7000],
                            'backgroundColor' => 'oklch(0.828 0.189 84 / 80%)',
                            'borderRadius'    => 4,
                        ]],
                    ]"
                    height="280px"
                />
            </x-ui.card.content>
        </x-ui.card>
    </section>

    <x-ui.separator />

    {{-- Pie + Doughnut lado a lado --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Pie & Doughnut — distribución</x-ui.typography>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-ui.card>
                <x-ui.card.header>
                    <x-ui.card.title>Pie</x-ui.card.title>
                </x-ui.card.header>
                <x-ui.card.content>
                    <x-ui.chart
                        type="pie"
                        :data="[
                            'labels' => ['Mobile', 'Desktop', 'Tablet'],
                            'datasets' => [[
                                'data'            => [58, 32, 10],
                                'backgroundColor' => [
                                    'oklch(0.585 0.233 277 / 85%)',
                                    'oklch(0.723 0.219 142 / 85%)',
                                    'oklch(0.828 0.189 84 / 85%)',
                                ],
                                'borderWidth' => 2,
                                'borderColor' => 'var(--background)',
                            ]],
                        ]"
                        height="240px"
                    />
                </x-ui.card.content>
            </x-ui.card>

            <x-ui.card>
                <x-ui.card.header>
                    <x-ui.card.title>Doughnut</x-ui.card.title>
                </x-ui.card.header>
                <x-ui.card.content>
                    <x-ui.chart
                        type="doughnut"
                        :data="[
                            'labels' => ['Free', 'Pro', 'Team', 'Enterprise'],
                            'datasets' => [[
                                'data'            => [45, 30, 18, 7],
                                'backgroundColor' => [
                                    'oklch(0.585 0.233 277 / 85%)',
                                    'oklch(0.723 0.219 142 / 85%)',
                                    'oklch(0.828 0.189 84 / 85%)',
                                    'oklch(0.637 0.237 25 / 85%)',
                                ],
                                'borderWidth' => 2,
                                'borderColor' => 'var(--background)',
                            ]],
                        ]"
                        height="240px"
                    />
                </x-ui.card.content>
            </x-ui.card>
        </div>
    </section>

    <x-ui.separator />

    {{-- Bar horizontal --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Bar horizontal — top productos</x-ui.typography>
        <x-ui.card>
            <x-ui.card.content class="pt-6">
                <x-ui.chart
                    type="bar"
                    :data="[
                        'labels' => ['Plan Enterprise', 'Plan Pro', 'Add-on Storage', 'Plan Team', 'Add-on API'],
                        'datasets' => [[
                            'label'           => 'Ingresos (USD)',
                            'data'            => [48200, 31500, 18700, 14300, 9600],
                            'backgroundColor' => [
                                'oklch(0.585 0.233 277 / 85%)',
                                'oklch(0.585 0.233 277 / 70%)',
                                'oklch(0.585 0.233 277 / 55%)',
                                'oklch(0.585 0.233 277 / 40%)',
                                'oklch(0.585 0.233 277 / 28%)',
                            ],
                            'borderRadius'    => 4,
                            'borderSkipped'   => false,
                        ]],
                    ]"
                    :options="[
                        'indexAxis' => 'y',
                        'plugins'   => ['legend' => ['display' => false]],
                        'scales'    => [
                            'x' => ['grid' => ['color' => 'var(--border)']],
                            'y' => ['grid' => ['display' => false]],
                        ],
                    ]"
                    height="240px"
                />
            </x-ui.card.content>
        </x-ui.card>
    </section>

    <x-ui.separator />

    {{-- Stacked bar --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Bar apilado — desglose de costos</x-ui.typography>
        <x-ui.card>
            <x-ui.card.content class="pt-6">
                <x-ui.chart
                    type="bar"
                    :data="[
                        'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                        'datasets' => [[
                            'label'           => 'Infraestructura',
                            'data'            => [3200, 3200, 3400, 3400, 3800, 3800],
                            'backgroundColor' => 'oklch(0.585 0.233 277 / 85%)',
                            'borderRadius'    => [4, 4, 0, 0],
                            'borderSkipped'   => false,
                        ], [
                            'label'           => 'Personal',
                            'data'            => [8500, 8500, 8500, 9200, 9200, 9200],
                            'backgroundColor' => 'oklch(0.723 0.219 142 / 80%)',
                        ], [
                            'label'           => 'Marketing',
                            'data'            => [1800, 2400, 2100, 3200, 2700, 4100],
                            'backgroundColor' => 'oklch(0.828 0.189 84 / 80%)',
                        ], [
                            'label'           => 'Otros',
                            'data'            => [600, 750, 820, 690, 1100, 950],
                            'backgroundColor' => 'oklch(0.637 0.237 25 / 75%)',
                        ]],
                    ]"
                    :options="[
                        'scales' => [
                            'x' => ['stacked' => true, 'grid' => ['display' => false]],
                            'y' => ['stacked' => true, 'grid' => ['color' => 'var(--border)']],
                        ],
                    ]"
                    height="280px"
                />
            </x-ui.card.content>
        </x-ui.card>
    </section>

    <x-ui.separator />

    {{-- Radar + PolarArea lado a lado --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Radar & PolarArea</x-ui.typography>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-ui.card>
                <x-ui.card.header>
                    <x-ui.card.title>Radar — perfil de producto</x-ui.card.title>
                </x-ui.card.header>
                <x-ui.card.content>
                    <x-ui.chart
                        type="radar"
                        :data="[
                            'labels' => ['Rendimiento', 'Usabilidad', 'Seguridad', 'Escalabilidad', 'Soporte', 'Precio'],
                            'datasets' => [[
                                'label'           => 'Nuestro producto',
                                'data'            => [88, 92, 85, 79, 95, 72],
                                'borderColor'     => 'oklch(0.585 0.233 277)',
                                'backgroundColor' => 'oklch(0.585 0.233 277 / 15%)',
                                'pointBackgroundColor' => 'oklch(0.585 0.233 277)',
                                'pointRadius'     => 4,
                            ], [
                                'label'           => 'Competidor',
                                'data'            => [75, 80, 90, 85, 68, 88],
                                'borderColor'     => 'oklch(0.637 0.237 25)',
                                'backgroundColor' => 'oklch(0.637 0.237 25 / 12%)',
                                'pointBackgroundColor' => 'oklch(0.637 0.237 25)',
                                'pointRadius'     => 4,
                            ]],
                        ]"
                        :options="[
                            'scales' => [
                                'r' => [
                                    'min' => 0,
                                    'max' => 100,
                                    'ticks' => ['stepSize' => 20, 'display' => false],
                                    'grid'  => ['color' => 'var(--border)'],
                                    'pointLabels' => ['font' => ['size' => 12]],
                                ],
                            ],
                        ]"
                        height="300px"
                    />
                </x-ui.card.content>
            </x-ui.card>

            <x-ui.card>
                <x-ui.card.header>
                    <x-ui.card.title>PolarArea — tickets por categoría</x-ui.card.title>
                </x-ui.card.header>
                <x-ui.card.content>
                    <x-ui.chart
                        type="polarArea"
                        :data="[
                            'labels' => ['Bug', 'Feature', 'Consulta', 'Facturación', 'Acceso'],
                            'datasets' => [[
                                'data'            => [34, 22, 18, 12, 8],
                                'backgroundColor' => [
                                    'oklch(0.637 0.237 25 / 75%)',
                                    'oklch(0.585 0.233 277 / 75%)',
                                    'oklch(0.723 0.219 142 / 75%)',
                                    'oklch(0.828 0.189 84 / 75%)',
                                    'oklch(0.72 0.2 330 / 75%)',
                                ],
                                'borderWidth' => 2,
                                'borderColor' => 'var(--background)',
                            ]],
                        ]"
                        :options="[
                            'scales' => [
                                'r' => [
                                    'ticks'  => ['display' => false],
                                    'grid'   => ['color' => 'var(--border)'],
                                ],
                            ],
                        ]"
                        height="300px"
                    />
                </x-ui.card.content>
            </x-ui.card>
        </div>
    </section>

    <x-ui.separator />

    {{-- Line sin relleno, múltiples series --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Line — métricas de retención semanal</x-ui.typography>
        <x-ui.card>
            <x-ui.card.content class="pt-6">
                <x-ui.chart
                    type="line"
                    :data="[
                        'labels' => ['S1', 'S2', 'S3', 'S4', 'S5', 'S6', 'S7', 'S8', 'S9', 'S10', 'S11', 'S12'],
                        'datasets' => [[
                            'label'           => 'Plan Pro',
                            'data'            => [0.82, 0.80, 0.83, 0.81, 0.85, 0.84, 0.87, 0.86, 0.89, 0.88, 0.91, 0.90],
                            'borderColor'     => 'oklch(0.585 0.233 277)',
                            'backgroundColor' => 'oklch(0.585 0.233 277)',
                            'fill'            => false,
                            'tension'         => 0.3,
                            'pointRadius'     => 3,
                        ], [
                            'label'           => 'Plan Team',
                            'data'            => [0.74, 0.73, 0.76, 0.75, 0.78, 0.77, 0.79, 0.81, 0.80, 0.83, 0.84, 0.86],
                            'borderColor'     => 'oklch(0.723 0.219 142)',
                            'backgroundColor' => 'oklch(0.723 0.219 142)',
                            'fill'            => false,
                            'tension'         => 0.3,
                            'pointRadius'     => 3,
                        ], [
                            'label'           => 'Plan Free',
                            'data'            => [0.51, 0.49, 0.53, 0.48, 0.52, 0.50, 0.54, 0.53, 0.57, 0.55, 0.58, 0.60],
                            'borderColor'     => 'oklch(0.828 0.189 84)',
                            'backgroundColor' => 'oklch(0.828 0.189 84)',
                            'fill'            => false,
                            'tension'         => 0.3,
                            'pointRadius'     => 3,
                            'borderDash'      => [5, 3],
                        ]],
                    ]"
                    :options="[
                        'scales' => [
                            'y' => [
                                'min'   => 0.4,
                                'max'   => 1.0,
                                'ticks' => [
                                    'format'   => ['style' => 'percent'],
                                    'stepSize' => 0.2,
                                ],
                                'grid'  => ['color' => 'var(--border)'],
                            ],
                            'x' => ['grid' => ['display' => false]],
                        ],
                    ]"
                    height="280px"
                />
            </x-ui.card.content>
        </x-ui.card>
    </section>

</div>
</x-layouts.showcase>
