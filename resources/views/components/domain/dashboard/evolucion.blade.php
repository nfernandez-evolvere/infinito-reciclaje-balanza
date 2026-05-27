@props(['evolucion7', 'evolucion15', 'evolucion90'])

<x-ui.card variant="elevated">
    <div
        x-data="{
            periodo: 7,
            chart: null,
            datasets: {
                7:  {{ Js::from($evolucion7) }},
                15: {{ Js::from($evolucion15) }},
                90: {{ Js::from($evolucion90) }},
            },
            get promedio() { return this.datasets[this.periodo].promedio; },
            primaryColor() {
                const el = document.createElement('span');
                el.className = 'bg-primary';
                el.style.cssText = 'position:fixed;top:-999px;opacity:0;pointer-events:none';
                document.body.appendChild(el);
                const c = getComputedStyle(el).backgroundColor;
                document.body.removeChild(el);
                return c;
            },
            get series() {
                return [{ name: 'Toneladas netas', data: this.datasets[this.periodo].datos.map(d => d.toneladas) }];
            },
            get categories() {
                return this.datasets[this.periodo].datos.map(d => d.fecha);
            },
            columnWidth() {
                return this.periodo <= 15 ? '55%' : '80%';
            },
            tickAmount() {
                return this.periodo <= 15 ? undefined : 10;
            },
            promedioAnnotation(muted) {
                if (!this.promedio) return { yaxis: [] };
                return {
                    yaxis: [{
                        y: this.promedio,
                        borderColor: muted,
                        strokeDashArray: 4,
                        label: {
                            text: 'Prom. ' + this.promedio + ' t',
                            position: 'right',
                            offsetX: -8,
                            style: {
                                color: muted,
                                fontSize: '10px',
                                fontWeight: 400,
                                background: 'transparent',
                                padding: { left: 4, right: 4, top: 2, bottom: 2 },
                            },
                        },
                    }],
                };
            },
            baseOptions(color) {
                const dark   = document.documentElement.classList.contains('dark');
                const muted  = dark ? '#a1a1aa' : '#71717a';
                const border = dark ? '#3f3f46' : '#e4e4e7';
                return {
                    chart: {
                        type: 'bar', height: 240, background: 'transparent',
                        toolbar: { show: false }, zoom: { enabled: false },
                        animations: { enabled: true, speed: 300, animateGradually: { enabled: false } },
                        fontFamily: 'Inter, ui-sans-serif, system-ui, sans-serif',
                    },
                    colors: [color],
                    series: this.series,
                    dataLabels: { enabled: false },
                    plotOptions: { bar: { borderRadius: 3, columnWidth: this.columnWidth() } },
                    grid: { borderColor: border, strokeDashArray: 4, xaxis: { lines: { show: false } } },
                    xaxis: {
                        categories: this.categories,
                        labels: { style: { colors: muted, fontSize: '11px' }, rotate: 0 },
                        axisBorder: { show: false },
                        axisTicks: { show: false },
                        tickAmount: this.tickAmount(),
                    },
                    yaxis: { labels: { style: { colors: muted, fontSize: '11px' } } },
                    annotations: this.promedioAnnotation(muted),
                    tooltip: {
                        theme: dark ? 'dark' : 'light',
                        style: { fontSize: '12px', fontFamily: 'Inter, ui-sans-serif, system-ui, sans-serif' },
                        y: { formatter: (v) => v + ' t' },
                    },
                };
            },
            init() {
                const color = this.primaryColor();
                this.chart = new window.ApexCharts(this.$refs.chart, this.baseOptions(color));
                this.chart.render();

                this.$watch('periodo', () => {
                    const dark  = document.documentElement.classList.contains('dark');
                    const muted = dark ? '#a1a1aa' : '#71717a';
                    this.chart.updateOptions({
                        series: this.series,
                        xaxis: { categories: this.categories, tickAmount: this.tickAmount() },
                        plotOptions: { bar: { borderRadius: 3, columnWidth: this.columnWidth() } },
                        annotations: this.promedioAnnotation(muted),
                    });
                });

                const mo = new MutationObserver(() => {
                    const c = this.primaryColor();
                    this.chart.updateOptions(this.baseOptions(c));
                });
                mo.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
                this.$cleanup(() => mo.disconnect());
            },
        }"
    >
        <x-ui.card.header>
            <div class="flex items-center justify-between gap-3 w-full">
                <div>
                    <x-ui.card.title>Evolución diaria</x-ui.card.title>
                    <x-ui.card.description>Toneladas netas por día</x-ui.card.description>
                </div>
                {{-- Selector de período --}}
                <div class="flex items-center rounded-md border border-border bg-muted/40 p-0.5 gap-0.5 shrink-0">
                    @foreach([7 => '7d', 15 => '15d', 90 => '3m'] as $dias => $label)
                        <button
                            type="button"
                            @click="periodo = {{ $dias }}"
                            :class="periodo === {{ $dias }}
                                ? 'bg-background text-foreground shadow-sm'
                                : 'text-muted-foreground hover:text-foreground'"
                            class="rounded px-2.5 py-1 text-xs font-medium transition-colors"
                        >{{ $label }}</button>
                    @endforeach
                </div>
            </div>
        </x-ui.card.header>
        <x-ui.card.content class="pt-0 px-4 pb-4">
            <div x-ref="chart"></div>
        </x-ui.card.content>
    </div>
</x-ui.card>
