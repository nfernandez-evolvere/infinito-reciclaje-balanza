const formVacio = () => ({
    id:             null,
    nombre:         '',
    tipo:           'informe_mensual',
    frecuencia:     'mensual',
    cron_expresion: '0 8 1 * *',
    formatos:       ['pdf'],
    revision:       'revisar',
    // Secciones del informe: sin personalizar hereda la configuración general.
    // Las listas arrancan con el default general (openCreate/openEdit las cargan).
    secciones_personalizadas: false,
    secciones:      { pdf: [], excel: [] },
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
    // Secciones de la configuración general: punto de partida al personalizar.
    seccionesDefault:       { pdf: [], excel: [] },

    deleteOpen:   false,
    deleteId:     null,
    deleteNombre: '',

    enviarOpen:   false,
    enviarId:     null,
    enviarNombre: '',
    enviarUrl:    null,
    enviando:     false,

    ...initial,

    init() {
        if (this.modalOpen && this._oldDestinatariosStr) {
            const tags = this._oldDestinatariosStr.split(',').map(e => e.trim()).filter(Boolean);
            this.$nextTick(() => resetDestinatarios(tags));
        }
    },

    openCreate() {
        this.form           = formVacio();
        this.form.secciones = this._seccionesDefaultCopy();
        this.modalMode      = 'create';
        this.modalOpen      = true;
        this.$nextTick(() => resetDestinatarios([]));
    },

    toggleFormato(formato) {
        const i = this.form.formatos.indexOf(formato);
        if (i === -1) {
            this.form.formatos.push(formato);
        } else {
            this.form.formatos.splice(i, 1);
        }
    },

    toggleSeccion(formato, clave) {
        const lista = this.form.secciones[formato];
        const i = lista.indexOf(clave);
        if (i === -1) {
            lista.push(clave);
        } else {
            lista.splice(i, 1);
        }
    },

    _seccionesDefaultCopy() {
        return {
            pdf:   [...(this.seccionesDefault.pdf ?? [])],
            excel: [...(this.seccionesDefault.excel ?? [])],
        };
    },

    confirmEnviar(id, nombre, url) {
        this.enviarId     = id;
        this.enviarNombre = nombre;
        this.enviarUrl    = url;
        this.enviarOpen   = true;
    },

    // Encola el envío por AJAX (sin recargar la pantalla): el resultado real del
    // reporte llega después por WebSocket (toast + campana + historial en vivo).
    async executeEnviar() {
        if (! this.enviarUrl || this.enviando) return;
        this.enviando = true;

        try {
            const r = await fetch(this.enviarUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });
            const data = await r.json().catch(() => ({}));

            if (r.ok && data.toast) {
                this.$store.toast.add(data.toast);
                // Refresca la tabla del Historial para que la fila recién encolada
                // ("Generando…") aparezca sin recargar (mismo evento que el push WS).
                window.dispatchEvent(new CustomEvent('reporte-estado', { detail: {} }));
            } else {
                this.$store.toast.add({ message: 'No se pudo encolar el envío.', description: 'Intentá de nuevo en unos segundos.', variant: 'destructive' });
            }
        } catch (e) {
            this.$store.toast.add({ message: 'No se pudo encolar el envío.', description: 'Revisá tu conexión e intentá de nuevo.', variant: 'destructive' });
        } finally {
            this.enviando   = false;
            this.enviarOpen = false;
        }
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
            formatos:       Array.isArray(p.formatos) && p.formatos.length ? p.formatos : ['pdf'],
            revision:       p.revision || 'heredar',
            // p.secciones solo viene cuando el programado personalizó; si no,
            // se precargan las de la configuración general como punto de partida.
            secciones_personalizadas: !! p.secciones,
            secciones: p.secciones
                ? { pdf: [...(p.secciones.pdf ?? [])], excel: [...(p.secciones.excel ?? [])] }
                : this._seccionesDefaultCopy(),
            activo: p.activo,
        };
        this.modalMode = 'edit';
        this.modalOpen = true;
        const tags = p.destinatarios_str
            ? p.destinatarios_str.split(',').map(e => e.trim()).filter(Boolean)
            : [];
        this.$nextTick(() => resetDestinatarios(tags));
    },
});
