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
        ultimoLabel(min) {
            if (min === null) return '—';
            if (min < 60) return min + ' min';
            const h = Math.floor(min / 60), m = min % 60;
            return h + 'h' + (m > 0 ? ' ' + m + 'min' : '');
        },
        ultimoClass(min) {
            if (min === null) return 'text-muted-foreground';
            if (min < 15) return 'text-success';
            if (min < 60) return 'text-warning';
            return 'text-destructive';
        },

        async refresh() {
            if (this.refreshing) return;
            this.refreshing = true;
            try {
                const res = await fetch(init.refreshUrl, {
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
            setInterval(() => this.refresh(), 60000);
        },
    };
}
