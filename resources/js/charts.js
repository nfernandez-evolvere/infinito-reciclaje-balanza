import ApexCharts from 'apexcharts';

export function apexChart(config) {
    return {
        instance: null,

        colors() {
            const dark = document.documentElement.classList.contains('dark');
            return {
                fg:     dark ? '#fafafa' : '#18181b',
                muted:  dark ? '#a1a1aa' : '#71717a',
                border: dark ? '#3f3f46' : '#e4e4e7',
            };
        },

        formatter() {
            const map = {
                currency: (v) => '$' + (v >= 1000 ? (v / 1000).toFixed(1) + 'k' : v),
                percent:  (v) => v + '%',
                number:   (v) => v,
            };
            return map[config.yformat ?? 'number'];
        },

        options() {
            const dark   = document.documentElement.classList.contains('dark');
            const c      = this.colors();
            const series = config.series ?? [];
            const colors = config.colors ?? ['#3b82f6', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444'];

            const base = {
                chart: {
                    type:       config.type === 'mixed' ? 'line' : (config.type ?? 'bar'),
                    height:     config.height ?? 280,
                    background: 'transparent',
                    toolbar:    { show: false },
                    zoom:       { enabled: config.zoom ?? false },
                    animations: { enabled: true, speed: 350, animateGradually: { enabled: false } },
                    stacked:    config.stacked ?? false,
                    sparkline:  { enabled: config.sparkline ?? false },
                    fontFamily: 'Inter, ui-sans-serif, system-ui, sans-serif',
                },
                colors,
                series,
                dataLabels: { enabled: config.dataLabels ?? false },
                grid: {
                    borderColor:     c.border,
                    strokeDashArray: 4,
                    xaxis: { lines: { show: false } },
                },
                xaxis: {
                    categories: config.categories ?? [],
                    labels:     { style: { colors: c.muted, fontSize: '11px' } },
                    axisBorder: { show: false },
                    axisTicks:  { show: false },
                },
                yaxis: {
                    labels: {
                        style:     { colors: c.muted, fontSize: '11px' },
                        formatter: this.formatter(),
                    },
                },
                tooltip: {
                    theme: dark ? 'dark' : 'light',
                    style: { fontSize: '12px', fontFamily: 'Inter, ui-sans-serif, system-ui, sans-serif' },
                    y:     { formatter: this.formatter() },
                },
                legend: {
                    labels:   { colors: c.muted },
                    fontSize: '12px',
                },
            };

            // ── Bar ──────────────────────────────────────────────────────────────────
            if (config.type === 'bar') {
                base.plotOptions = {
                    bar: {
                        horizontal:  config.horizontal ?? false,
                        borderRadius: 3,
                        columnWidth: '55%',
                        barHeight:   '60%',
                        distributed: config.distributed ?? false,
                    },
                };
                if (config.distributed) {
                    base.legend = { show: false };
                }
            }

            // ── Area / Line ──────────────────────────────────────────────────────────
            if (config.type === 'area' || config.type === 'line') {
                base.stroke = { curve: config.curve ?? 'smooth', width: config.strokeWidth ?? 2 };
                if (config.type === 'area') {
                    base.fill = {
                        type: 'gradient',
                        gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.0, stops: [0, 100] },
                    };
                }
            }

            // ── Mixed (bar + line por serie) ─────────────────────────────────────────
            if (config.type === 'mixed') {
                const strokeWidths = series.map(s => s.type === 'line' ? 2 : 0);
                base.stroke    = { curve: 'smooth', width: strokeWidths };
                base.plotOptions = { bar: { borderRadius: 3, columnWidth: '55%' } };
            }

            // ── Donut / Pie ──────────────────────────────────────────────────────────
            if (config.type === 'donut' || config.type === 'pie') {
                base.labels = config.categories ?? [];
                delete base.xaxis;
                delete base.yaxis;
                delete base.grid;
                base.plotOptions = {
                    pie: {
                        donut: {
                            size: '68%',
                            labels: {
                                show:  true,
                                total: { show: true, color: c.muted, fontSize: '13px', fontWeight: 600 },
                                value: { color: c.fg, fontSize: '22px', fontWeight: 700 },
                            },
                        },
                    },
                };
                base.legend = { position: 'bottom', labels: { colors: c.fg }, fontSize: '12px' };
            }

            // ── RadialBar ────────────────────────────────────────────────────────────
            if (config.type === 'radialBar') {
                base.labels = config.categories ?? [];
                delete base.xaxis;
                delete base.yaxis;
                delete base.grid;
                base.plotOptions = {
                    radialBar: {
                        hollow: { size: series.length === 1 ? '65%' : '40%' },
                        track:  { background: c.border },
                        dataLabels: {
                            name:  {
                                fontSize: '14px', color: c.muted,
                                fontFamily: 'Inter, ui-sans-serif, system-ui, sans-serif',
                            },
                            value: {
                                fontSize: '22px', fontWeight: 700, color: c.fg,
                                fontFamily: 'Inter, ui-sans-serif, system-ui, sans-serif',
                                formatter: (v) => v + '%',
                            },
                            total: {
                                show:       series.length > 1,
                                label:      'Promedio',
                                color:      c.muted,
                                fontSize:   '13px',
                                fontWeight: 600,
                                fontFamily: 'Inter, ui-sans-serif, system-ui, sans-serif',
                                formatter:  (w) => {
                                    const vals = w.globals.seriesTotals;
                                    return Math.round(vals.reduce((a, b) => a + b, 0) / vals.length) + '%';
                                },
                            },
                        },
                    },
                };
                base.stroke = { lineCap: 'round' };
                base.legend = { position: 'bottom', labels: { colors: c.fg }, fontSize: '12px' };
            }

            // ── Radar ────────────────────────────────────────────────────────────────
            if (config.type === 'radar') {
                delete base.grid;
                base.stroke  = { width: 2 };
                base.fill    = { opacity: 0.15 };
                base.markers = { size: 4 };
                base.xaxis   = {
                    categories: config.categories ?? [],
                    labels: {
                        style: {
                            colors:   Array(config.categories?.length ?? 6).fill(c.muted),
                            fontSize: '11px',
                        },
                    },
                };
                delete base.yaxis;
            }

            // ── Heatmap ──────────────────────────────────────────────────────────────
            if (config.type === 'heatmap') {
                base.dataLabels = { enabled: config.dataLabels ?? false };
                base.plotOptions = {
                    heatmap: {
                        shadeIntensity:       0.65,
                        radius:               2,
                        useFillColorAsStroke: false,
                        colorScale: {
                            ranges: [
                                { from: 0,  to: 20,  color: c.border,  name: 'Mínimo' },
                                { from: 21, to: 45,  color: '#93c5fd', name: 'Bajo'   },
                                { from: 46, to: 65,  color: '#3b82f6', name: 'Medio'  },
                                { from: 66, to: 85,  color: '#1d4ed8', name: 'Alto'   },
                                { from: 86, to: 100, color: '#1e3a8a', name: 'Máximo' },
                            ],
                        },
                    },
                };
                delete base.yaxis;
            }

            // ── Scatter ──────────────────────────────────────────────────────────────
            if (config.type === 'scatter') {
                base.markers = { size: 7, strokeWidth: 0 };
                base.grid.xaxis = { lines: { show: true } };
                base.xaxis = {
                    ...base.xaxis,
                    type:       'numeric',
                    tickAmount: 6,
                    labels:     { style: { colors: c.muted, fontSize: '11px' }, formatter: this.formatter() },
                };
            }

            return base;
        },

        init() {
            this.instance = new ApexCharts(this.$refs.chart, this.options());
            this.instance.render();

            const mutationObserver = new MutationObserver(() => {
                this.instance.updateOptions(this.options(), false, true);
            });
            mutationObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

            const resizeObserver = new ResizeObserver(() => {
                this.instance?.updateOptions({ chart: { width: '100%' } }, false, false);
            });
            resizeObserver.observe(this.$el);

            this.$cleanup(() => {
                mutationObserver.disconnect();
                resizeObserver.disconnect();
                this.instance?.destroy();
            });
        },
    };
}
