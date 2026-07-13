export const PALETTE = ['#3b82f6', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#f97316', '#ec4899'];

export default function desgloseChart(sourceKey) {
    const init = window.__dashboardData ?? {};

    return {
        chart: null,
        datos: init[sourceKey] ?? [],
        servicioFiltro: '',

        validDatos() {
            return this.datos.filter(d =>
                d.toneladas > 0 &&
                (!this.servicioFiltro || String(d.tipo_servicio_id) === String(this.servicioFiltro))
            );
        },

        // Color estable por zona: se toma de la posición en la lista completa (mismo
        // criterio que desgloseColor en el dashboard), así el donut y los puntos de la
        // tabla coinciden aunque el filtro de servicio muestre solo un subconjunto.
        colorsFor(valid) {
            const validAll = this.datos.filter(d => d.toneladas > 0);
            return valid.map(d => PALETTE[validAll.findIndex(x => x.nombre === d.nombre) % PALETTE.length]);
        },

        // Servicios presentes (orden por nombre, igual que serviciosDesglose del
        // dashboard) y servicio por defecto — el primero. Mantiene al donut alineado
        // con el selector de la tabla sin depender del orden de inicialización.
        serviciosPresentes() {
            const vistos = new Map();
            for (const d of this.datos) {
                if (d.tipo_servicio_id != null && !vistos.has(d.tipo_servicio_id)) {
                    vistos.set(d.tipo_servicio_id, d.tipo_servicio);
                }
            }
            return [...vistos.entries()]
                .map(([id, nombre]) => ({ id, nombre }))
                .sort((a, b) => a.nombre.localeCompare(b.nombre, 'es'));
        },
        servicioDefault() {
            const s = this.serviciosPresentes();
            return s.length ? String(s[0].id) : '';
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
                colors: this.colorsFor(valid),
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
                colors: this.colorsFor(valid),
                series: valid.map(d => d.toneladas),
                labels: valid.map(d => d.nombre),
            });
        },

        init() {
            this.chart = new window.ApexCharts(this.$refs.chart, this.buildOptions());
            this.chart.render();

            // Servicio por defecto al montar: el donut arranca mostrando un servicio.
            this.servicioFiltro = this.servicioDefault();
            this.updateChart();

            this._refreshHandler = (e) => {
                this.datos = e.detail[sourceKey] ?? [];
                // Si el servicio elegido ya no existe tras refrescar, vuelve al default.
                const ids = this.serviciosPresentes().map(s => String(s.id));
                if (ids.length && !ids.includes(String(this.servicioFiltro))) {
                    this.servicioFiltro = this.servicioDefault();
                }
                this.updateChart();
            };
            window.addEventListener('dashboard-refreshed', this._refreshHandler);

            // El selector de servicio (hermano en la card) filtra también el donut.
            this._servicioHandler = (e) => {
                if (e.detail?.source !== sourceKey) return;
                this.servicioFiltro = e.detail.value ?? '';
                this.updateChart();
            };
            window.addEventListener('desglose-servicio', this._servicioHandler);

            const mo = new MutationObserver(() => {
                this.chart.updateOptions(this.buildOptions());
            });
            mo.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

            this.$cleanup(() => {
                mo.disconnect();
                window.removeEventListener('dashboard-refreshed', this._refreshHandler);
                window.removeEventListener('desglose-servicio', this._servicioHandler);
                this.chart?.destroy();
            });
        },
    };
}
