const PALETTE = ['#3b82f6', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#f97316', '#ec4899'];

export default function desgloseChart(sourceKey) {
    const init = window.__dashboardData ?? {};

    return {
        chart: null,
        datos: init[sourceKey] ?? [],

        validDatos() {
            return this.datos.filter(d => d.toneladas > 0);
        },

        themeColors() {
            const dark = document.documentElement.classList.contains('dark');
            return {
                fg:    dark ? '#fafafa' : '#18181b',
                muted: dark ? '#a1a1aa' : '#71717a',
            };
        },

        buildOptions() {
            const c     = this.themeColors();
            const dark  = document.documentElement.classList.contains('dark');
            const valid = this.validDatos();
            return {
                chart: {
                    type:       'donut',
                    height:     220,
                    background: 'transparent',
                    toolbar:    { show: false },
                    animations: { enabled: true, speed: 350, animateGradually: { enabled: false } },
                    fontFamily: 'Inter, ui-sans-serif, system-ui, sans-serif',
                },
                colors: PALETTE,
                series: valid.map(d => d.toneladas),
                labels: valid.map(d => d.nombre),
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '68%',
                            labels: {
                                show:  true,
                                total: {
                                    show:       true,
                                    label:      'Total',
                                    color:      c.muted,
                                    fontSize:   '11px',
                                    fontWeight: 600,
                                    formatter:  (w) => {
                                        const sum = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                        return sum.toFixed(1) + 't';
                                    },
                                },
                                value: {
                                    color:      c.fg,
                                    fontSize:   '16px',
                                    fontWeight: 700,
                                    formatter:  (v) => Number(v).toFixed(1) + 't',
                                },
                            },
                        },
                    },
                },
                legend: { show: false },
                noData: {
                    text:          'Sin pesajes',
                    align:         'center',
                    verticalAlign: 'middle',
                    style:         { color: c.muted, fontSize: '13px' },
                },
                tooltip: {
                    theme: dark ? 'dark' : 'light',
                    style: { fontSize: '12px', fontFamily: 'Inter, ui-sans-serif, system-ui, sans-serif' },
                    y:     { formatter: (v) => v + 't' },
                },
            };
        },

        updateChart() {
            const valid = this.validDatos();
            this.chart.updateOptions({
                series: valid.map(d => d.toneladas),
                labels: valid.map(d => d.nombre),
            });
        },

        init() {
            this.chart = new window.ApexCharts(this.$refs.chart, this.buildOptions());
            this.chart.render();

            this._refreshHandler = (e) => {
                this.datos = e.detail[sourceKey] ?? [];
                this.updateChart();
            };
            window.addEventListener('dashboard-refreshed', this._refreshHandler);

            const mo = new MutationObserver(() => {
                this.chart.updateOptions(this.buildOptions());
            });
            mo.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

            this.$cleanup(() => {
                mo.disconnect();
                window.removeEventListener('dashboard-refreshed', this._refreshHandler);
                this.chart?.destroy();
            });
        },
    };
}
