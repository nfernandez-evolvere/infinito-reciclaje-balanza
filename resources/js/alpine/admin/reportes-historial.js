const revisionVacia = () => ({
    id:            null,
    tipo:          '',
    periodo:       '',
    generado:      '',
    autor:         '',
    destinatarios: [],
    esInforme:     false,
    conclusiones:  '',
    urls:          { pdf: null, excel: null, aprobar: '', descartar: '', conclusiones: '' },
});

export default ({ parcialUrl = '' } = {}) => ({
    revisionOpen:           false,
    revision:               revisionVacia(),
    conclusionesGuardadas:  '',
    motivoDescarte:         '',
    descarteAbierto:        false,
    parcialUrl,
    refrescando:            false,

    init() {
        // Cuando llega un cambio de estado por WebSocket, refresca la tabla del
        // historial en vivo para que el badge "Generando…" pase a su estado real
        // sin recargar la página.
        window.addEventListener('reporte-estado', () => this.refrescarTabla());
    },

    async refrescarTabla() {
        if (!this.parcialUrl || this.refrescando) return;
        this.refrescando = true;
        try {
            // Conserva la página actual: sin esto el partial siempre devuelve la
            // página 1 y un evento WebSocket tira al usuario al inicio del listado.
            const url = new URL(this.parcialUrl);
            const page = new URLSearchParams(window.location.search).get('page');
            if (page) url.searchParams.set('page', page);
            const r = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!r.ok) return;
            const html = await r.text();
            const cont = document.getElementById('historial-tabla');
            if (!cont) return;
            cont.innerHTML = html;
            // Re-bindea las directivas Alpine del HTML inyectado (los @click
            // resuelven openRevision contra este mismo scope padre).
            window.Alpine?.initTree(cont);
        } finally {
            this.refrescando = false;
        }
    },

    // Con ediciones sin guardar en el análisis, aprobar queda deshabilitado:
    // lo que se envía es siempre lo último persistido en el snapshot.
    get conclusionesDirty() {
        return (this.revision.conclusiones ?? '') !== this.conclusionesGuardadas;
    },

    openRevision(g) {
        this.revision              = { ...revisionVacia(), ...g };
        this.conclusionesGuardadas = this.revision.conclusiones ?? '';
        this.motivoDescarte        = '';
        this.descarteAbierto       = false;
        this.revisionOpen          = true;
    },
});
