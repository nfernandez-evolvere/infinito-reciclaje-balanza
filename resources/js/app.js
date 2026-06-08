import Alpine from 'alpinejs';
import Collapse from '@alpinejs/collapse';
import tiposVehiculo from './alpine/admin/tipos-vehiculo.js';
import tiposServicio from './alpine/admin/tipos-servicio.js';
import vehiculos from './alpine/admin/vehiculos.js';
import zonas from './alpine/admin/zonas.js';
import mapaCalor from './alpine/admin/mapa-calor.js';
import usuarios from './alpine/admin/usuarios.js';
import organizaciones from './alpine/super_admin/organizaciones.js';
import loginForm from './alpine/auth/login.js';
import balanza from './alpine/operador/balanza.js';
import historial from './alpine/operador/historial.js';
import historialFiltroPatente from './alpine/operador/historial-filtro-patente.js';
import dashboard from './alpine/admin/dashboard.js';
import notificaciones from './alpine/admin/notificaciones.js';
import evolucionChart, { evolucionRangoChart } from './alpine/admin/evolucion-chart.js';
import desgloseChart from './alpine/admin/desglose-chart.js';
import reportesProgramados from './alpine/admin/reportes-programados.js';
import reportesConfiguracion from './alpine/admin/reportes-configuracion.js';
import tagsInput from './alpine/tags-input.js';
import { apexChart } from './charts.js';

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
Alpine.data('tiposServicio', tiposServicio);
Alpine.data('vehiculos', vehiculos);
Alpine.data('zonas', zonas);
Alpine.data('mapaCalor', mapaCalor);
Alpine.data('usuarios', usuarios);
Alpine.data('organizaciones', organizaciones);
Alpine.data('loginForm', loginForm);
Alpine.data('balanza', balanza);
Alpine.data('historial', historial);
Alpine.data('historialFiltroPatente', historialFiltroPatente);
Alpine.data('dashboardData', dashboard);
Alpine.data('notificaciones', notificaciones);
Alpine.data('evolucionChart', evolucionChart);
Alpine.data('evolucionRangoChart', evolucionRangoChart);
Alpine.data('desgloseChart', desgloseChart);
Alpine.data('reportesProgramados', reportesProgramados);
Alpine.data('reportesConfiguracion', reportesConfiguracion);
Alpine.data('tagsInput', tagsInput);
Alpine.data('apexChart', apexChart);

// — Alpine store: toast ———————————————————————————————————————
Alpine.store('toast', {
    toasts: [],
    _counter: 0,

    add({ message, description = null, variant = 'default', duration = 6000, action = null }) {
        const id = ++this._counter;
        const classes = this._variantClasses(variant);
        this.toasts.push({ id, message, description, variant, action, visible: true, ...classes });

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

    _variantClasses(variant) {
        const map = {
            success:     { toastClass: 'bg-card text-card-foreground border-success/40',     iconClass: 'bg-success/20 text-success',         accentClass: 'border-l-success' },
            destructive: { toastClass: 'bg-card text-card-foreground border-destructive/40', iconClass: 'bg-destructive/15 text-destructive', accentClass: 'border-l-destructive' },
            warning:     { toastClass: 'bg-card text-card-foreground border-warning/40',     iconClass: 'bg-warning/20 text-warning',         accentClass: 'border-l-warning' },
            info:        { toastClass: 'bg-card text-card-foreground border-info/40',        iconClass: 'bg-info/15 text-info',               accentClass: 'border-l-info' },
            loading:     { toastClass: 'bg-card text-card-foreground border-border',         iconClass: 'bg-muted text-muted-foreground',     accentClass: '' },
        };
        return map[variant] ?? { toastClass: 'bg-card text-card-foreground border-border', iconClass: 'bg-muted text-muted-foreground', accentClass: '' };
    },
});

// — Alpine store: lightbox ————————————————————————————————————
Alpine.store('lightbox', {
    open: false,
    src: '',
    alt: '',
    show(src, alt) { this.src = src; this.alt = alt; this.open = true; },
    hide() { this.open = false; },
});

window.Alpine = Alpine;
Alpine.start();
