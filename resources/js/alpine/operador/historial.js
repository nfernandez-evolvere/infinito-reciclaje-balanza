export default function historial() {
    return {
        filterOpen: false,
        filterOpenMod: false,
        metricasOpen: false,

        egresoId: null,
        egresoPatente: '',
        egresoTab: 'pesajes',
        horaActual: '',

        logPatente: '',
        logEntradas: [],
        logCargando: false,

        cancelarId: null,
        cancelarPatente: '',
        cancelarTab: 'pesajes',
        motivoCancelacion: '',

        // El tercer argumento (tab) sólo lo pasa la tabla del tab «Modificaciones»;
        // sirve para volver a ese tab tras registrar el egreso o la cancelación.
        abrirEgreso(id, patente, tab = 'pesajes') {
            this.egresoId = id;
            this.egresoPatente = patente;
            this.egresoTab = tab;
            const now = new Date();
            this.horaActual = now.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });
            window.dispatchEvent(new Event('modal-egreso-open'));
        },

        abrirCancelar(id, patente, tab = 'pesajes') {
            this.cancelarId = id;
            this.cancelarPatente = patente;
            this.cancelarTab = tab;
            this.motivoCancelacion = '';
            window.dispatchEvent(new Event('modal-cancelar-open'));
        },

        async abrirLog(id, patente) {
            this.logPatente = patente;
            this.logEntradas = [];
            this.logCargando = true;
            window.dispatchEvent(new Event('modal-log-open'));

            const res = await fetch(`/pesajes/${id}/log`);
            const data = await res.json();
            this.logEntradas = data;
            this.logCargando = false;
        },
    };
}
