import { createChoroplethMap } from '../../maps/choropleth.js';

// Rampa de calor (ColorBrewer YlOrRd, 5 pasos) — claro = poco, oscuro = mucho.
const RAMP = ['#ffffb2', '#fecc5c', '#fd8d3c', '#f03b20', '#bd0026'];
const SIN_DATOS = '#cbd5e1'; // slate-300 — zona con geometría pero sin actividad en el rango

const METRICAS = [
    { key: 'toneladas',  label: 'Toneladas',  unidad: 't',      decimales: 2 },
    { key: 'pesajes',    label: 'Viajes',     unidad: 'viajes', decimales: 0 },
    { key: 'per_capita', label: 'Per cápita', unidad: 'kg/hab', decimales: 2 },
    { key: 'densidad',   label: 'Densidad',   unidad: 'kg/ha',  decimales: 1 },
];

export default (zonas = []) => {
    // La instancia Leaflet vive fuera del estado reactivo de Alpine.
    let mapa = null;

    return {
        zonas,
        metricas: METRICAS,
        ramp: RAMP,
        metric: 'toneladas',
        buckets: [],
        min: 0,
        max: 0,
        filterOpen: false,

        get conGeometria() {
            return this.zonas.filter((z) => z.tiene_geometria);
        },

        get hayMapa() {
            return this.conGeometria.length > 0;
        },

        get listaOrdenada() {
            return [...this.zonas].sort(
                (a, b) => (b.metricas[this.metric] ?? -1) - (a.metricas[this.metric] ?? -1)
            );
        },

        metricaActual() {
            return this.metricas.find((m) => m.key === this.metric);
        },

        init() {
            this.$nextTick(() => this.initMapa());
        },

        initMapa() {
            if (mapa || !this.hayMapa) return;
            const el = document.getElementById('mapa-calor-map');
            if (!el) return;
            mapa = createChoroplethMap(el);
            mapa.setZonas(this.conGeometria);
            this.recolor();
        },

        setMetric(key) {
            this.metric = key;
            this.recolor();
        },

        computeBuckets() {
            const valores = this.conGeometria
                .map((z) => z.metricas[this.metric])
                .filter((v) => v !== null && v > 0);

            if (!valores.length) {
                this.min = 0;
                this.max = 0;
                this.buckets = [];
                return;
            }

            this.min = Math.min(...valores);
            this.max = Math.max(...valores);

            if (this.min === this.max) {
                const color = this.ramp[this.ramp.length - 1];
                this.buckets = [{ color, label: this.fmt(this.min, this.metricaActual().decimales) }];
                return;
            }

            const step = (this.max - this.min) / this.ramp.length;
            const dec = this.metricaActual().decimales;
            this.buckets = this.ramp.map((color, i) => {
                const lo = this.min + step * i;
                const hi = i === this.ramp.length - 1 ? this.max : this.min + step * (i + 1);
                return { color, label: `${this.fmt(lo, dec)}–${this.fmt(hi, dec)}` };
            });
        },

        colorFor(value) {
            if (value === null || value <= 0) return SIN_DATOS;
            if (this.max === this.min) return this.ramp[this.ramp.length - 1];

            const step = (this.max - this.min) / this.ramp.length;
            let idx = Math.floor((value - this.min) / step);
            if (idx >= this.ramp.length) idx = this.ramp.length - 1;
            if (idx < 0) idx = 0;
            return this.ramp[idx];
        },

        recolor() {
            this.computeBuckets();
            if (mapa) {
                mapa.recolor(
                    (z) => this.colorFor(z.metricas[this.metric]),
                    (z) => this.tooltipHtml(z)
                );
            }
        },

        // — formato y etiquetas —
        fmt(value, dec = 0) {
            if (value === null || value === undefined) return '—';
            return Number(value).toLocaleString('es-AR', {
                minimumFractionDigits: dec,
                maximumFractionDigits: dec,
            });
        },

        valorMetrica(z, key = this.metric) {
            const m = this.metricas.find((x) => x.key === key);
            const v = z.metricas[key];
            if (v === null) return '—';
            return `${this.fmt(v, m.decimales)} ${m.unidad}`;
        },

        tooltipHtml(z) {
            const filas = this.metricas
                .map((m) => {
                    const v = z.metricas[m.key];
                    const val = v === null ? '—' : `${this.fmt(v, m.decimales)} ${m.unidad}`;
                    return `<div style="display:flex;justify-content:space-between;gap:16px"><span>${m.label}</span><strong>${val}</strong></div>`;
                })
                .join('');
            return `<div style="min-width:170px"><div style="font-weight:600;margin-bottom:4px">${z.nombre}</div>${filas}</div>`;
        },
    };
};
