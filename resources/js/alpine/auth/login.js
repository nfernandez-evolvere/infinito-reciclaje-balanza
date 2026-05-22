export default ({ initialEmail = '' } = {}) => ({
    emailVal:     initialEmail,
    orgs:         [],
    orgId:        null,
    isSuperAdmin: false,
    loading:      false,
    fetched:      false,
    showPassword: false,
    _t:           null,

    init() {
        this._orgHandler = e => { this.orgId = e.detail.id; };
        window.addEventListener('login:org-select', this._orgHandler);

        if (this._emailOk(this.emailVal)) this._fetch();
        this.$watch('emailVal', v => {
            this.orgs         = [];
            this.orgId        = null;
            this.isSuperAdmin = false;
            this.fetched      = false;
            clearTimeout(this._t);
            if (!this._emailOk(v)) return;
            this._t = setTimeout(() => this._fetch(), 400);
        });
    },

    destroy() {
        window.removeEventListener('login:org-select', this._orgHandler);
    },

    _emailOk(v) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test((v ?? '').trim());
    },

    async _fetch() {
        if (!this._emailOk(this.emailVal)) return;
        this.loading = true;
        try {
            const r = await fetch(
                '/login/organizaciones?email=' + encodeURIComponent(this.emailVal.trim()),
                { headers: { 'X-Requested-With': 'XMLHttpRequest' } }
            );
            if (r.ok) {
                const d = await r.json();
                this.isSuperAdmin = d.super_admin ?? false;
                this.orgs         = d.orgs ?? [];
            }
        } catch (e) {
        } finally {
            this.loading = false;
            this.fetched = true;
        }
    },
});
