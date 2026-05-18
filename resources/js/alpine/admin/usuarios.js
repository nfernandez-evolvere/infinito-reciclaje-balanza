export default (initial = {}) => ({
    modalOpen:   false,
    modalMode:   'create',
    form: {
        id:       null,
        name:     '',
        email:    '',
        role:     '',
        password: '',
        password_confirmation: '',
    },
    filterOpen:    false,
    confirmOpen:   false,
    confirmId:     null,
    confirmNombre: '',
    confirmActivo: false,
    resetOpen:     false,
    resetId:       null,
    resetNombre:   '',

    ...initial,

    openCreate() {
        this.modalMode = 'create';
        this.form      = {
            id:       null,
            name:     '',
            email:    '',
            role:     '',
            password: '',
            password_confirmation: '',
        };
        this.modalOpen = true;
    },

    openEdit(id, name, email, role) {
        this.modalMode = 'edit';
        this.form      = { id, name, email, role, password: '', password_confirmation: '' };
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

    openResetPassword(id, nombre) {
        this.resetId     = id;
        this.resetNombre = nombre;
        this.resetOpen   = true;
    },
});
