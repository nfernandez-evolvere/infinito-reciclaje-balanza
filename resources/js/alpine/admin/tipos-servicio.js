export default (initial = {}) => ({
    // — modal crear/editar —
    modalOpen:   false,
    modalMode:   'create',
    form: { id: null, nombre: '', tipo_vehiculo_ids: [] },

    // — drawer filtros —
    filterOpen: false,

    // — modal confirmar toggle —
    confirmOpen:   false,
    confirmId:     null,
    confirmNombre: '',
    confirmActivo: false,

    // — modal confirmar delete —
    deleteOpen:   false,
    deleteId:     null,
    deleteNombre: '',

    ...initial,

    openCreate() {
        this.modalMode = 'create';
        this.form      = { id: null, nombre: '', tipo_vehiculo_ids: [] };
        this.modalOpen = true;
    },

    openEdit(id, nombre, tipoVehiculoIds) {
        this.modalMode = 'edit';
        this.form      = { id, nombre, tipo_vehiculo_ids: tipoVehiculoIds };
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
