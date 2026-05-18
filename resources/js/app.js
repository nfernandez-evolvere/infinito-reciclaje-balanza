import Alpine from 'alpinejs';
import Collapse from '@alpinejs/collapse';
import tiposVehiculo from './alpine/admin/tipos-vehiculo.js';
import vehiculos from './alpine/admin/vehiculos.js';

Alpine.plugin(Collapse);

// — Alpine store: tema ————————————————————————————————————————
const savedTheme = localStorage.getItem('theme');
const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
const isDark = savedTheme === 'dark' || (!savedTheme && prefersDark);

Alpine.store('theme', {
    dark: isDark,
    toggle() {
        this.dark = !this.dark;
        document.documentElement.classList.toggle('dark', this.dark);
        localStorage.setItem('theme', this.dark ? 'dark' : 'light');
    },
});

Alpine.data('tiposVehiculo', tiposVehiculo);
Alpine.data('vehiculos', vehiculos);

// — Alpine store: toast ———————————————————————————————————————
Alpine.store('toast', {
    toasts: [],
    _counter: 0,

    add({ message, description = null, variant = 'default', duration = 4000, action = null }) {
        const id = ++this._counter;
        this.toasts.push({ id, message, description, variant, action, visible: true,
            variantClass: this._variantClass(variant) });

        if (duration > 0) {
            setTimeout(() => this.dismiss(id), duration);
        }
        return id;
    },

    dismiss(id) {
        const t = this.toasts.find(t => t.id === id);
        if (t) t.visible = false;
        setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 300);
    },

    _variantClass(variant) {
        return {
            success:     'bg-success text-success-foreground border-success/30',
            destructive: 'bg-destructive text-destructive-foreground border-destructive/30',
            warning:     'bg-warning text-warning-foreground border-warning/30',
            info:        'bg-info text-info-foreground border-info/30',
            loading:     'bg-card text-card-foreground border-border',
        }[variant] ?? 'bg-card text-card-foreground border-border';
    },
});

window.Alpine = Alpine;
Alpine.start();
