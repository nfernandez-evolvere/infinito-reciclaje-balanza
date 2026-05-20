export default (initial = {}) => ({
    modalOpen:   false,
    modalMode:   'create',
    form: {
        id:                        null,
        nombre:                    '',
        slug:                      '',
        admin_email:               '',
        admin_password:            '',
        admin_password_confirmation: '',
    },
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
            id: null, nombre: '', slug: '',
            admin_email: '', admin_password: '', admin_password_confirmation: '',
        };
        this.modalOpen = true;
    },

    openEdit(id, nombre, slug) {
        this.modalMode = 'edit';
        this.form      = {
            id, nombre, slug: slug ?? '',
            admin_email: '', admin_password: '', admin_password_confirmation: '',
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
