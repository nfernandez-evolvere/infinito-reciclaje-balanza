export default (initial = {}) => ({
    modalOpen:   false,
    modalMode:   'create',
    form: {
        id:          null,
        nombre:      '',
        admin_email: '',
    },
    confirmOpen:   false,
    confirmId:     null,
    confirmNombre: '',
    confirmActivo: false,
    deleteOpen:    false,
    deleteId:      null,
    deleteNombre:  '',

    // URLs (passed from Blade via $initial)
    userSearchUrl: '',
    orgBaseUrl:    '',

    // Create mode: combobox para admin inicial
    userQuery:      '',
    userResults:    [],
    userSearchOpen: false,
    selectedUser:   null,
    userSearching:  false,
    _userTimer:     null,

    // Edit mode: usuarios de la org + combobox para agregar
    orgUsers:        [],
    addQuery:        '',
    addResults:      [],
    addSearchOpen:   false,
    addSearching:    false,
    addWorking:      false,
    addError:        '',
    _addTimer:       null,
    pendingRemoveId: null,
    addNewName:      '',

    // Create mode: nombre del admin nuevo
    adminName:       '',

    ...initial,

    // ── Modal ──────────────────────────────────────────────────────────────

    openCreate() {
        this.modalMode      = 'create';
        this.form           = { id: null, nombre: '', admin_email: '' };
        this.selectedUser   = null;
        this.userQuery      = '';
        this.userResults    = [];
        this.userSearchOpen = false;
        this.adminName      = '';
        this.modalOpen      = true;
    },

    openEdit(id, nombre, users = []) {
        this.modalMode       = 'edit';
        this.form            = { id, nombre, admin_email: '' };
        this.orgUsers        = users;
        this.addQuery        = '';
        this.addResults      = [];
        this.addSearchOpen   = false;
        this.addError        = '';
        this.pendingRemoveId = null;
        this.addNewName      = '';
        this.modalOpen       = true;
    },

    // ── Create mode: combobox admin ────────────────────────────────────────

    debouncedUserSearch() {
        this.selectedUser     = null;
        this.form.admin_email = this.userQuery;
        clearTimeout(this._userTimer);
        if (!this.userQuery.trim()) {
            this.userResults    = [];
            this.userSearchOpen = false;
            return;
        }
        this._userTimer = setTimeout(() => this.fetchUsers(), 300);
    },

    async fetchUsers() {
        if (this.userQuery.trim().length < 2) return;
        this.userSearching = true;
        try {
            const res  = await fetch(this.userSearchUrl + '?q=' + encodeURIComponent(this.userQuery), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await res.json();
            this.userResults    = data;
            this.userSearchOpen = data.length > 0;
        } catch {
            this.userResults    = [];
            this.userSearchOpen = false;
        } finally {
            this.userSearching = false;
        }
    },

    selectUser(user) {
        this.selectedUser     = user;
        this.form.admin_email = user.email;
        this.userQuery        = '';
        this.userResults      = [];
        this.userSearchOpen   = false;
    },

    clearUser() {
        this.selectedUser     = null;
        this.form.admin_email = '';
        this.userQuery        = '';
    },

    // ── Edit mode: gestión de usuarios ────────────────────────────────────

    debouncedAddSearch() {
        clearTimeout(this._addTimer);
        this.addNewName = '';
        if (!this.addQuery.trim()) {
            this.addResults    = [];
            this.addSearchOpen = false;
            return;
        }
        this._addTimer = setTimeout(() => this.fetchAddResults(), 300);
    },

    async fetchAddResults() {
        if (this.addQuery.trim().length < 2) return;
        this.addSearching = true;
        try {
            const res      = await fetch(this.userSearchUrl + '?q=' + encodeURIComponent(this.addQuery), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data     = await res.json();
            this.addResults = data.filter(u => !this.orgUsers.some(o => o.id === u.id));
            this.addSearchOpen = this.addResults.length > 0 || this.addQuery.includes('@');
        } catch {
            this.addResults    = [];
            this.addSearchOpen = false;
        } finally {
            this.addSearching = false;
        }
    },

    async addOrgUser(email) {
        if (!email || this.addWorking) return;
        this.addWorking    = true;
        this.addError      = '';
        this.addSearchOpen = false;
        try {
            const payload = { email };
            if (this.addNewName.trim()) payload.name = this.addNewName.trim();

            const res  = await this._post(`${this.orgBaseUrl}/${this.form.id}/usuarios`, payload);
            const data = await res.json();
            if (!res.ok) {
                this.addError = data.message ?? 'No se pudo agregar el usuario.';
            } else {
                this.orgUsers.push(data.user);
                this.addQuery   = '';
                this.addResults = [];
                this.addNewName = '';
                this._toast('Usuario agregado.', 'success', `"${data.user.name}" fue agregado a la organización.`);
            }
        } catch {
            this.addError = 'Error de conexión.';
        } finally {
            this.addWorking = false;
        }
    },

    confirmRemoveOrgUser(userId) {
        this.pendingRemoveId = userId;
    },

    async removeOrgUser(userId) {
        if (this.addWorking) return;
        this.addWorking = true;
        try {
            const res = await this._delete(`${this.orgBaseUrl}/${this.form.id}/usuarios/${userId}`);
            if (res.ok) {
                const removed        = this.orgUsers.find(u => u.id === userId);
                this.orgUsers        = this.orgUsers.filter(u => u.id !== userId);
                this.pendingRemoveId = null;
                this._toast('Usuario quitado.', 'success', removed ? `"${removed.name}" fue quitado de la organización.` : '');
            } else {
                const data = await res.json().catch(() => ({}));
                this.pendingRemoveId = null;
                this._toast('No se pudo quitar el usuario.', 'destructive', data.message ?? '');
            }
        } catch {
            this.pendingRemoveId = null;
            this._toast('No se pudo quitar el usuario.', 'destructive', 'Error de conexión.');
        } finally {
            this.addWorking = false;
        }
    },

    async resetOrgUserPassword(userId) {
        if (this.addWorking) return;
        this.addWorking = true;
        try {
            const res = await this._post(`${this.orgBaseUrl}/${this.form.id}/usuarios/${userId}/reset-password`);
            if (res.ok) {
                this._toast('Email de restablecimiento enviado.', 'success');
            } else {
                const data = await res.json().catch(() => ({}));
                this._toast(data.message ?? 'No se pudo enviar el email.', 'destructive');
            }
        } catch {
            this._toast('Error de conexión.', 'destructive');
        } finally {
            this.addWorking = false;
        }
    },

    // ── Helpers ───────────────────────────────────────────────────────────

    _csrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    },

    async _post(url, data = {}) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN':     this._csrf(),
                'Content-Type':     'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(data),
        });
    },

    async _delete(url) {
        return fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN':     this._csrf(),
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
    },

    _toast(message, variant = 'default', description = '') {
        Alpine.store('toast').add({ message, variant, ...(description && { description }) });
    },

    // ── Toggle / delete confirm ───────────────────────────────────────────

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

});
