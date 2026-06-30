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
    // Lista completa de servicios activos (incluye los que aún no tienen zonas).
    const allServicios = Array.isArray(opts.servicios) ? opts.servicios : [];

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
        servicioFilter: '',

        // Toneladas acumuladas y si tiene geometría, por servicio, derivado del dataset de zonas.
        statsPorServicio() {
            const m = new Map();
            for (const z of this.zonas) {
                if (z.tipo_servicio_id == null) continue;
                const s = m.get(z.tipo_servicio_id) ?? { nombre: z.tipo_servicio_nombre ?? '—', toneladas: 0, conGeo: false, tieneZonas: false };
                s.toneladas += z.metricas?.toneladas ?? 0;
                s.tieneZonas = true;
                if (z.tiene_geometria) s.conGeo = true;
                m.set(z.tipo_servicio_id, s);
            }
            return m;
        },

        // Servicios del selector. Con la lista completa (allServicios) se incluyen
        // también los que aún no tienen zonas; si no, se derivan del dataset.
        get serviciosDisponibles() {
            const stats = this.statsPorServicio();

            const base = allServicios.length
                ? allServicios.map((s) => ({
                    id: s.id,
                    nombre: s.nombre,
                    toneladas: stats.get(s.id)?.toneladas ?? 0,
                    conGeo: stats.get(s.id)?.conGeo ?? false,
                    tieneZonas: stats.get(s.id)?.tieneZonas ?? false,
                }))
                : [...stats.entries()].map(([id, s]) => ({ id, nombre: s.nombre, toneladas: s.toneladas, conGeo: s.conGeo, tieneZonas: true }));

            return base.sort((a, b) => b.toneladas - a.toneladas);
        },

        get hayVarios() {
            return this.serviciosDisponibles.length > 1;
        },

        // Zonas del servicio seleccionado (o todas si no hay selección).
        get zonasVisibles() {
            if (!this.servicioFilter) return this.zonas;
            return this.zonas.filter((z) => String(z.tipo_servicio_id) === String(this.servicioFilter));
        },

        // El servicio elegido existe pero no tiene zonas → empty-state (sin mapa ni lista).
        get sinZonasEnServicio() {
            return this.servicioFilter !== '' && this.zonasVisibles.length === 0;
        },

        get conGeometria() {
            return this.zonasVisibles.filter((z) => z.tiene_geometria);
        },

        // Global: ¿hay alguna zona con geometría en cualquier servicio? Controla el empty-state.
        get hayMapa() {
            return this.zonas.some((z) => z.tiene_geometria);
        },

        get listaOrdenada() {
            return [...this.zonasVisibles].sort(
                (a, b) => (b.metricas[this.metric] ?? -1) - (a.metricas[this.metric] ?? -1)
            );
        },

        metricaActual() {
            return this.metricas.find((m) => m.key === this.metric);
        },

        init() {
            // Selecciona un servicio por defecto (el más activo con geometría).
            this.ensureServicio();
            // Colores de la leyenda y el ranking, correctos desde el primer render
            // (incluso antes de que el mapa se cree al hacerse visible).
            this.computeBuckets();

            // Dashboard: el dataset se renueva con cada refresh (botón, intervalo
            // o aplicar rango). El detalle del evento trae todas las claves.
            if (this.sourceKey) {
                this._onRefresh = (e) => {
                    this.zonas = e.detail?.[this.sourceKey] ?? [];
                    this.ensureServicio();
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

        // Fija un servicio válido si el actual no está en el dataset (carga o refresh).
        ensureServicio() {
            const list = this.serviciosDisponibles;
            if (!list.length) { this.servicioFilter = ''; return; }
            if (list.some((s) => String(s.id) === String(this.servicioFilter))) return;
            // Por defecto, el más activo con geometría; si no, uno con zonas; si no, el primero.
            const def = list.find((s) => s.conGeo) ?? list.find((s) => s.tieneZonas) ?? list[0];
            this.servicioFilter = String(def.id);
        },

        setServicio(id) {
            this.servicioFilter = id;
            this.syncMapa();
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
