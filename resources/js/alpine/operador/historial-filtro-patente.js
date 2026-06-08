export default ({ value = '', url = null } = {}) => ({
    url,
    query:     value,
    vehiculos: [],
    cargados:  false,
    showSugg:  false,

    async cargar() {
        this.showSugg = true;
        if (this.cargados || !this.url) return;
        this.cargados = true;
        const res = await fetch(this.url);
        this.vehiculos = await res.json();
    },

    get matches() {
        const q = this.query.trim().toLowerCase();
        if (!q) return this.vehiculos;
        return this.vehiculos.filter(v =>
            v.patente.toLowerCase().includes(q) || (v.interno ?? '').toLowerCase().includes(q)
        );
    },

    seleccionar(patente) {
        this.query = patente;
        this.showSugg = false;
    },
});
