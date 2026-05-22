export default function historial() {
    return {
        modalEgreso: false,
        egresoId: null,
        egresoPatente: '',
        horaActual: '',

        modalEdicion: false,
        edicionId: null,
        edicionData: {},
        edicionMotivo: '',

        modalLog: false,
        logPatente: '',
        logEntradas: [],
        logCargando: false,

        abrirEgreso(id, patente) {
            this.egresoId = id;
            this.egresoPatente = patente;
            const now = new Date();
            this.horaActual = now.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });
            this.modalEgreso = true;
        },

        abrirEdicion(id, data) {
            this.edicionId = id;
            this.edicionData = { ...data };
            this.edicionMotivo = '';
            this.modalEdicion = true;
        },

        async abrirLog(id, patente) {
            this.logPatente = patente;
            this.logEntradas = [];
            this.logCargando = true;
            this.modalLog = true;

            const res = await fetch(`/api/pesajes/${id}/log`);
            const data = await res.json();
            this.logEntradas = data;
            this.logCargando = false;
        },
    };
}
