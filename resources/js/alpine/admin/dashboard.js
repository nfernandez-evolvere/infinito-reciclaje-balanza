import { PALETTE } from './desglose-chart.js';

export default function dashboardData() {
    const init = window.__dashboardData ?? {};

    return {
        lastRefresh: new Date().toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' }),
        refreshing: false,

        kpisDia:             init.kpisDia             ?? {},
        kpisMes:             init.kpisMes             ?? {},
        evolucion7:          init.evolucion7           ?? { datos: [], promedio: 0 },
        evolucion15:         init.evolucion15          ?? { datos: [], promedio: 0 },
        evolucion90:         init.evolucion90          ?? { datos: [], promedio: 0 },
        desgloseVehiculo:    init.desgloseVehiculo     ?? [],
        desgloseZona:        init.desgloseZona         ?? [],
        desgloseVehiculoMes: init.desgloseVehiculoMes  ?? [],
        desgloseZonaMes:     init.desgloseZonaMes      ?? [],
        alertas:             init.alertas              ?? 0,

        // Rango personalizado
        tmpDesde:              null,
        tmpHasta:              null,
        desdeRango:            null,
        hastaRango:            null,
        kpisRango:             null,
        evolucionRango:        null,
        desgloseVehiculoRango: [],
        desgloseZonaRango:     [],

        get sinDatos() {
            return (this.kpisDia?.total ?? 0) === 0 && (this.kpisMes?.total ?? 0) === 0;
        },

        desgloseColor(source, nombre) {
            const valid = (this[source] ?? []).filter(d => d.toneladas > 0);
            const idx = valid.findIndex(d => d.nombre === nombre);
            return idx >= 0 ? PALETTE[idx % PALETTE.length] : null;
        },

        fmt(n, d = 0) {
            return Number(n).toLocaleString('es-AR', { minimumFractionDigits: d, maximumFractionDigits: d });
        },
        deltaText(delta, suffix) {
            if (delta === null) return 'Sin comparación disponible';
            return (delta >= 0 ? '+' : '') + delta + '% ' + suffix;
        },
        deltaClass(delta) {
            if (delta === null) return 'text-xs font-normal mt-0.5 text-muted-foreground';
            return 'text-xs font-normal mt-0.5 ' + (delta >= 0 ? 'text-success' : 'text-destructive');
        },
        deltaBadgeText(delta) {
            if (delta === null) return '—';
            return (delta >= 0 ? '+' : '') + delta + '%';
        },
        deltaBorderStyle(delta) {
            if (delta === null) return 'border-left: 2px solid var(--color-border)';
            return delta >= 0
                ? 'border-left: 2px solid var(--color-success)'
                : 'border-left: 2px solid var(--color-destructive)';
        },
        deltaBadgeClass(delta) {
            const base = 'inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs font-semibold cursor-pointer select-none transition-colors';
            if (delta === null) return base + ' border-border text-muted-foreground';
            return delta >= 0
                ? base + ' bg-success/10 text-success border-success/30'
                : base + ' bg-destructive/10 text-destructive border-destructive/30';
        },
        ultimoLabel(min) {
            if (min === null) return 'Sin pesaje';
            if (min < 60) return min + ' min';
            const h = Math.floor(min / 60), m = min % 60;
            return h + 'h' + (m > 0 ? ' ' + m + 'min' : '');
        },
        ultimoClass(min) {
            if (min === null) return '';
            if (min < 180) return 'text-success';
            if (min < 480) return 'text-warning';
            return 'text-destructive';
        },
        ultimoVariant(min) {
            if (min === null || min < 180) return 'primary';
            if (min < 480) return 'warning';
            return 'destructive';
        },
        rangoLabel() {
            if (!this.desdeRango || !this.hastaRango) return '';
            const fmt = d => new Date(d + 'T00:00:00').toLocaleDateString('es-AR', { day: '2-digit', month: '2-digit' });
            return this.desdeRango === this.hastaRango
                ? fmt(this.desdeRango)
                : fmt(this.desdeRango) + ' – ' + fmt(this.hastaRango);
        },

        async applyRango(desde = null, hasta = null) {
            desde = desde || this.tmpDesde;
            hasta = hasta || this.tmpHasta;
            if (!desde || !hasta) return;
            if (desde > hasta) [desde, hasta] = [hasta, desde];

            this.desdeRango = desde;
            this.hastaRango = hasta;
            this.refreshing = true;
            try {
                const url = new URL(init.refreshUrl, window.location.origin);
                url.searchParams.set('desde', desde);
                url.searchParams.set('hasta', hasta);
                const res = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!res.ok) return;
                const data = await res.json();
                Object.assign(this, data);
                this.lastRefresh = new Date().toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });
                this.$dispatch('activate-tab', 'personalizado');
                await this.$nextTick();
                this.$dispatch('dashboard-refreshed', data);
            } finally {
                this.refreshing = false;
            }
        },

        clearRango() {
            this.desdeRango = null;
            this.hastaRango = null;
            this.tmpDesde   = null;
            this.tmpHasta   = null;
            this.kpisRango             = null;
            this.evolucionRango        = null;
            this.desgloseVehiculoRango = [];
            this.desgloseZonaRango     = [];
            this.$dispatch('activate-tab', 'hoy');
        },

        async refresh() {
            if (this.refreshing) return;
            this.refreshing = true;
            try {
                const url = new URL(init.refreshUrl, window.location.origin);
                if (this.desdeRango && this.hastaRango) {
                    url.searchParams.set('desde', this.desdeRango);
                    url.searchParams.set('hasta', this.hastaRango);
                }
                const res = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!res.ok) return;
                const data = await res.json();
                Object.assign(this, data);
                this.lastRefresh = new Date().toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });
                this.$dispatch('dashboard-refreshed', data);
            } finally {
                this.refreshing = false;
            }
        },

        init() {
            setInterval(() => this.refresh(), 600000);
        },
    };
}
