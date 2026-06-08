export default function notificaciones({ count = 0, urls = {} } = {}) {
    return {
        open: false,
        count,
        items: [],
        loading: false,

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
