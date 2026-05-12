import Alpine from 'alpinejs'
import membersTable from './alpine/members-table'
import Chart from 'chart.js/auto'
import { createIcons, icons } from 'lucide'
window.Chart = Chart
window.lucide = { createIcons, icons }

// — Tema claro/oscuro ————————————————————————————————————————
const savedTheme = localStorage.getItem('theme')
const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
const isDark = savedTheme === 'dark' || (!savedTheme && prefersDark)
document.documentElement.classList.toggle('dark', isDark)

// — Alpine store: tema ————————————————————————————————————————
Alpine.store('theme', {
    dark: isDark,
    toggle() {
        this.dark = !this.dark
        document.documentElement.classList.toggle('dark', this.dark)
        localStorage.setItem('theme', this.dark ? 'dark' : 'light')
    },
})

// — Alpine store: brand theme —————————————————————————————————
Alpine.store('brandTheme', {
    current: localStorage.getItem('brandTheme') || 'default',
    apply(name) {
        this.current = name
        document.documentElement.setAttribute('data-theme', name)
        localStorage.setItem('brandTheme', name)
    },
})

// — Alpine store: toast ———————————————————————————————————————
const TOAST_VARIANT_CLASSES = {
    default:     'bg-background border-border text-foreground',
    destructive: 'bg-destructive border-destructive text-destructive-foreground',
    success:     'bg-success border-success text-success-foreground',
    warning:     'bg-warning border-warning text-warning-foreground',
    info:        'bg-info border-info text-info-foreground',
    loading:     'bg-background border-border text-foreground',
}

Alpine.store('toast', {
    toasts: [],

    add(message, options = {}) {
        const variant  = options.variant  ?? 'default'
        const duration = options.duration ?? 4000
        const id       = Date.now() + Math.random()

        this.toasts.push({
            id,
            message,
            description:  options.description ?? null,
            action:       options.action       ?? null,
            variant,
            variantClass: TOAST_VARIANT_CLASSES[variant] ?? TOAST_VARIANT_CLASSES.default,
            visible: false,
        })

        requestAnimationFrame(() => {
            const t = this.toasts.find(t => t.id === id)
            if (t) t.visible = true
        })

        if (duration !== Infinity) {
            setTimeout(() => this.dismiss(id), duration)
        }
        return id
    },

    dismiss(id) {
        const t = this.toasts.find(t => t.id === id)
        if (t) t.visible = false
        setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id) }, 350)
    },

    success(message, options = {})  { return this.add(message, { ...options, variant: 'success' }) },
    error(message, options = {})    { return this.add(message, { ...options, variant: 'destructive' }) },
    warning(message, options = {})  { return this.add(message, { ...options, variant: 'warning' }) },
    info(message, options = {})     { return this.add(message, { ...options, variant: 'info' }) },
    loading(message, options = {})  { return this.add(message, { ...options, variant: 'loading', duration: Infinity }) },

    // Dispara el toast según el resultado de una Promise
    async promise(promiseFn, { loading = 'Cargando...', success = 'Completado', error = 'Error' } = {}) {
        const id = this.loading(loading)
        try {
            const result = await promiseFn
            this.dismiss(id)
            setTimeout(() => this.success(typeof success === 'function' ? success(result) : success), 100)
        } catch (err) {
            this.dismiss(id)
            setTimeout(() => this.error(typeof error === 'function' ? error(err) : error), 100)
        }
    },
})

// — Alpine.data: por feature ——————————————————————————————————
membersTable(Alpine)

window.Alpine = Alpine
Alpine.start()
