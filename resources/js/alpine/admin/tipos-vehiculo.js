export default (initial = {}) => ({
    modalOpen:   false,
    modalMode:   'create',
    form: { id: null, nombre: '', peso_min_kg: '', peso_max_kg: '' },
    filterOpen:  false,
    confirmOpen: false,
    confirmId:   null,
    confirmNombre: '',
    confirmActivo: false,

    ...initial,

    openCreate() {
        this.modalMode  = 'create';
        this.form       = { id: null, nombre: '', peso_min_kg: '', peso_max_kg: '' };
        this.modalOpen  = true;
    },

    openEdit(id, nombre, pesoMin, pesoMax) {
        this.modalMode  = 'edit';
        this.form       = { id, nombre, peso_min_kg: pesoMin, peso_max_kg: pesoMax };
        this.modalOpen  = true;
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
});
