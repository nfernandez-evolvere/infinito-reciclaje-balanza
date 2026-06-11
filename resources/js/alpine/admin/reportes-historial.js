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

export default () => ({
    revisionOpen:           false,
    revision:               revisionVacia(),
    conclusionesGuardadas:  '',
    motivoDescarte:         '',
    descarteAbierto:        false,

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
