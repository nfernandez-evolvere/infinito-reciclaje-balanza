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

/**
 * Panel de mapa de calor embebible. Dos modos:
 *  - { source: 'metricasPorZonaMes' }  → Dashboard: lee el dato inicial de
 *    window.__dashboardData[source] y se reactualiza con el evento
 *    'dashboard-refreshed' (mismo patrón que desgloseChart).
 *  - { zonas: [...] }                  → Reportes: dataset estático del informe.
 */
export default (opts = {}) => {
    const dashInit = window.__dashboardData ?? {};
    const sourceKey = opts.source ?? null;
    const initialZonas = sourceKey ? (dashInit[sourceKey] ?? []) : (opts.zonas ?? []);

    // La instancia Leaflet y el flag de visibilidad viven fuera del estado reactivo.
    let mapa = null;
    let visible = false;

    return {
        zonas: initialZonas,
        sourceKey,
        metricas: METRICAS,
        ramp: RAMP,
        metric: 'toneladas',
        buckets: [],
        min: 0,
        max: 0,

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
            // Colores de la leyenda y el ranking, correctos desde el primer render
            // (incluso antes de que el mapa se cree al hacerse visible).
            this.computeBuckets();

            // Dashboard: el dataset se renueva con cada refresh (botón, intervalo
            // o aplicar rango). El detalle del evento trae todas las claves.
            if (this.sourceKey) {
                this._onRefresh = (e) => {
                    this.zonas = e.detail?.[this.sourceKey] ?? [];
                    this.syncMapa();
                };
                window.addEventListener('dashboard-refreshed', this._onRefresh);
            }

            // El mapa se crea recién cuando el panel entra en viewport: Leaflet
            // renderiza 0px dentro de un tab oculto (display:none). En revelados
            // posteriores se reajusta el tamaño.
            this._io = new IntersectionObserver((entries) => {
                visible = entries.some((en) => en.isIntersecting);
                if (!visible) return;
                if (!mapa) this.initMapa();
                else mapa.resize();
            }, { threshold: 0.01 });
            this._io.observe(this.$el);

            this.$cleanup(() => {
                this._io?.disconnect();
                if (this._onRefresh) window.removeEventListener('dashboard-refreshed', this._onRefresh);
            });
        },

        initMapa() {
            if (mapa || !this.hayMapa) return;
            const el = this.$refs.map;
            if (!el) return;
            mapa = createChoroplethMap(el);
            mapa.setZonas(this.conGeometria);
            this.recolor();
        },

        // Reaplica el dataset al cambiar (refresh del dashboard). Si el mapa todavía
        // no existe, mantiene la leyenda/ranking en sync y lo crea si ya es visible.
        syncMapa() {
            if (mapa) {
                mapa.setZonas(this.conGeometria);
                this.recolor();
            } else {
                this.computeBuckets();
                if (visible) this.initMapa();
            }
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
