export function evolucionRangoChart(dataset) {
    return {
        chart: null,
        dataset: dataset ?? { datos: [], promedio: 0 },

        get promedio() { return this.dataset?.promedio ?? 0; },

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
            return [{ name: 'Toneladas netas', data: (this.dataset?.datos ?? []).map(d => d.toneladas) }];
        },

        get categories() {
            return (this.dataset?.datos ?? []).map(d => d.fecha);
        },

        columnWidth() {
            const count = this.dataset?.datos?.length ?? 0;
            if (count <= 7)  return '55%';
            if (count <= 15) return '70%';
            return '65%';
        },

        get chartMinWidth() {
            const count = this.dataset?.datos?.length || 0;
            return count * 55;
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
                    type: 'bar', height: 240, width: '100%', background: 'transparent',
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

        updateChart() {
            this.chart.destroy();
            this.chart = new window.ApexCharts(this.$refs.chart, this.baseOptions(this.primaryColor()));
            this.chart.render();
        },

        init() {
            const color = this.primaryColor();
            this.chart = new window.ApexCharts(this.$refs.chart, this.baseOptions(color));
            this.chart.render();

            this._refreshHandler = (e) => {
                const d = e.detail;
                if (d.evolucionRango) {
                    this.dataset = d.evolucionRango;
                    this.updateChart();
                }
            };
            window.addEventListener('dashboard-refreshed', this._refreshHandler);

            const mo = new MutationObserver(() => {
                this.chart.updateOptions(this.baseOptions(this.primaryColor()));
            });
            mo.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

            this.$cleanup(() => {
                mo.disconnect();
                window.removeEventListener('dashboard-refreshed', this._refreshHandler);
            });
        },
    };
}

export default function evolucionChart(datasets7, datasets15, datasets90) {
    return {
        periodo: 7,
        chart: null,
        datasets: { 7: datasets7, 15: datasets15, 90: datasets90 },

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
            if (this.periodo === 7)  return '55%';
            if (this.periodo === 15) return '70%';
            return '65%';
        },

        get chartMinWidth() {
            const count = this.datasets[this.periodo]?.datos?.length || 0;
            return count * 55;
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
                    type: 'bar', height: 240, width: '100%', background: 'transparent',
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

        updateChart() {
            this.chart.destroy();
            this.chart = new window.ApexCharts(this.$refs.chart, this.baseOptions(this.primaryColor()));
            this.chart.render();
        },

        init() {
            const color = this.primaryColor();
            this.chart = new window.ApexCharts(this.$refs.chart, this.baseOptions(color));
            this.chart.render();

            this.$watch('periodo', () => this.updateChart());

            this._refreshHandler = (e) => {
                const d = e.detail;
                this.datasets = { 7: d.evolucion7, 15: d.evolucion15, 90: d.evolucion90 };
                this.updateChart();
            };
            window.addEventListener('dashboard-refreshed', this._refreshHandler);

            const mo = new MutationObserver(() => {
                this.chart.updateOptions(this.baseOptions(this.primaryColor()));
            });
            mo.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

            this.$cleanup(() => {
                mo.disconnect();
                window.removeEventListener('dashboard-refreshed', this._refreshHandler);
            });
        },
    };
}
