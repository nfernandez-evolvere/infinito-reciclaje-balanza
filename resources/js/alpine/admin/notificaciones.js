export default function notificaciones({ count = 0, urls = {} } = {}) {
    return {
        open: false,
        count,
        items: [],
        loading: false,

        init() {
            // Push de tiempo real: una notificación de reporte recién llegada se
            // suma a la campana sin recargar ni reabrir el dropdown.
            window.addEventListener('reporte-notificacion', (e) => {
                const item = e.detail;
                if (!item) return;
                if (!this.items.some(i => i.id === item.id)) {
                    this.items = [item, ...this.items].slice(0, 5);
                }
                this.count++;
            });
        },

        get esMobile() { return window.innerWidth < 640; },
        get sheetOpen() { return this.open && this.esMobile; },
        set sheetOpen(v) { if (!v) this.open = false; },

        async toggle() {
            this.open = !this.open;
            if (this.open && this.items.length === 0) await this.cargar();
        },

        async cargar() {
            this.loading = true;
            try {
                const r = await fetch(urls.novedades);
                const d = await r.json();
                this.count = d.count;
                this.items = d.items;
            } finally {
                this.loading = false;
            }
        },

        async marcarTodas() {
            await fetch(urls.leerTodas, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'Content-Type': 'application/json',
                },
            });
            this.count = 0;
            this.items = [];
            this.open = false;
        },
    };
}
