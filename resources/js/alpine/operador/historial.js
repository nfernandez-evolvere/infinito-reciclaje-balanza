export default function historial() {
    return {
        egresoId: null,
        egresoPatente: '',
        horaActual: '',

        logPatente: '',
        logEntradas: [],
        logCargando: false,

        abrirEgreso(id, patente) {
            this.egresoId = id;
            this.egresoPatente = patente;
            const now = new Date();
            this.horaActual = now.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });
            window.dispatchEvent(new Event('modal-egreso-open'));
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
