import Alpine from 'alpinejs';
import Collapse from '@alpinejs/collapse';
import tiposVehiculo from './alpine/admin/tipos-vehiculo.js';

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

window.Alpine = Alpine;
Alpine.start();
