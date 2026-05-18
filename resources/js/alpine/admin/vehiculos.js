export default (initial = {}) => ({
    modalOpen:   false,
    modalMode:   'create',
    form: {
        id:               null,
        patente:          '',
        numero_interno:   '',
        tara_kg:          '',
        tipo_vehiculo_id: '',
        titular:          '',
        capacidad_kg:     '',
        observaciones:    '',
    },
    filterOpen:    false,
    confirmOpen:   false,
    confirmId:     null,
    confirmNombre: '',
    confirmActivo: false,
    deleteOpen:    false,
    deleteId:      null,
    deleteNombre:  '',

    ...initial,

    openCreate() {
        this.modalMode = 'create';
        this.form      = {
            id:               null,
            patente:          '',
            numero_interno:   '',
            tara_kg:          '',
            tipo_vehiculo_id: '',
            titular:          '',
            capacidad_kg:     '',
            observaciones:    '',
        };
        this.modalOpen = true;
    },

    openEdit(id, patente, numeroInterno, taraKg, tipoVehiculoId, titular, capacidadKg, observaciones) {
        this.modalMode = 'edit';
        this.form      = {
            id,
            patente,
            numero_interno:   numeroInterno,
            tara_kg:          taraKg,
            tipo_vehiculo_id: tipoVehiculoId,
            titular,
            capacidad_kg:     capacidadKg,
            observaciones:    observaciones,
        };
        this.modalOpen = true;
    },

    confirmToggle(id, nombre, activo) {
        this.confirmId     = id;
        this.confirmNombre = nombre;
        this.confirmActivo = activo;
        this.confirmOpen   = true;
    },

    executeToggle() {
        document.getElementById('toggle-' + this.confirmId).submit();
    },

    confirmDelete(id, nombre) {
        this.deleteId     = id;
        this.deleteNombre = nombre;
        this.deleteOpen   = true;
    },

    executeDelete() {
        document.getElementById('delete-' + this.deleteId).submit();
    },
});
