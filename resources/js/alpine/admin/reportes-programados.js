const formVacio = () => ({
    id:             null,
    nombre:         '',
    tipo:           'informe_mensual',
    frecuencia:     'mensual',
    cron_expresion: '0 8 1 * *',
    activo:         true,
});

const resetDestinatarios = (tags) => {
    window.dispatchEvent(new CustomEvent('tags-input-set', {
        detail: { name: 'destinatarios', tags },
    }));
};

export default (initial = {}) => ({
    modalOpen:              false,
    modalMode:              'create',
    form:                   formVacio(),
    _oldDestinatariosStr:   '',

    deleteOpen:   false,
    deleteId:     null,
    deleteNombre: '',

    enviarOpen:   false,
    enviarId:     null,
    enviarNombre: '',

    ...initial,

    init() {
        if (this.modalOpen && this._oldDestinatariosStr) {
            const tags = this._oldDestinatariosStr.split(',').map(e => e.trim()).filter(Boolean);
            this.$nextTick(() => resetDestinatarios(tags));
        }
    },

    openCreate() {
        this.form      = formVacio();
        this.modalMode = 'create';
        this.modalOpen = true;
        this.$nextTick(() => resetDestinatarios([]));
    },

    confirmEnviar(id, nombre) {
        this.enviarId     = id;
        this.enviarNombre = nombre;
        this.enviarOpen   = true;
    },

    executeEnviar() {
        document.getElementById('enviar-' + this.enviarId).submit();
    },

    confirmDelete(id, nombre) {
        this.deleteId     = id;
        this.deleteNombre = nombre;
        this.deleteOpen   = true;
    },

    executeDelete() {
        document.getElementById('delete-' + this.deleteId).submit();
    },

    openEdit(p) {
        this.form = {
            id:             p.id,
            nombre:         p.nombre,
            tipo:           p.tipo,
            frecuencia:     p.frecuencia,
            cron_expresion: p.cron_expresion,
            activo:         p.activo,
        };
        this.modalMode = 'edit';
        this.modalOpen = true;
        const tags = p.destinatarios_str
            ? p.destinatarios_str.split(',').map(e => e.trim()).filter(Boolean)
            : [];
        this.$nextTick(() => resetDestinatarios(tags));
    },
});
