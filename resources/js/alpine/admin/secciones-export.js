// Popover "Secciones" de la pantalla Generar: ajuste ad-hoc de las secciones del
// informe v2 para la descarga en curso (no persiste nada). Mientras la selección
// coincide con la configuración general, la URL de descarga queda limpia y el
// backend resuelve con la general; al diferir, se agregan secciones[] a la URL
// del formato correspondiente.
export default ({ catalogo = { pdf: [], excel: [] }, general = { pdf: [], excel: [] }, urls = { pdf: '', excel: '' } } = {}) => ({
    catalogo,
    general,
    urls,
    pdf:   [...general.pdf],
    excel: [...general.excel],

    toggleSeccion(formato, clave) {
        const seleccion = this[formato];
        if (seleccion.includes(clave)) {
            this[formato] = seleccion.filter(k => k !== clave);
        } else {
            // Insertar respetando el orden del catálogo: URLs y comparaciones estables.
            this[formato] = this.catalogo[formato].filter(k => seleccion.includes(k) || k === clave);
        }
    },

    esGeneral(formato) {
        return JSON.stringify(this[formato]) === JSON.stringify(this.general[formato]);
    },

    ajustado() {
        return ! this.esGeneral('pdf') || ! this.esGeneral('excel');
    },

    restablecer() {
        this.pdf   = [...this.general.pdf];
        this.excel = [...this.general.excel];
    },

    url(formato) {
        if (this.esGeneral(formato)) return this.urls[formato];

        const u = new URL(this.urls[formato], window.location.origin);
        this[formato].forEach(k => u.searchParams.append('secciones[]', k));

        return u.toString();
    },
});
