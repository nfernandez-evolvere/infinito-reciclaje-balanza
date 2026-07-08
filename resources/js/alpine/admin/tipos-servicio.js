import { createZonaMapEditor } from '../../maps/zona-map-editor.js';

const DIAS_CORTO = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
const DIAS_LARGO = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
const initHorarios = () => Array.from({ length: 7 }, () => []);

// Turnos más comunes, ofrecidos como sugerencias de un clic (el campo sigue siendo libre).
const TURNOS_SUGERIDOS = ['Diurna', 'Nocturna'];
// Grupos de días para los presets de horarios (índices 0=Lun … 6=Dom).
const PRESETS_DIAS = {
    labores: [0, 1, 2, 3, 4],
    todos:   [0, 1, 2, 3, 4, 5, 6],
    finde:   [5, 6],
};
const emptyZonaForm = () => ({
    id: null,
    tipo_servicio_id: null,
    nombre: '',
    hectareas: '',
    barrios: '',
    habitantes: '',
    geojson: '',
    centro_lat: '',
    centro_lng: '',
    turnosEnabled: false,
    turnos: [],
    horariosPorDia: initHorarios(),
});

export default (initial = {}) => {
    // El mapa de Leaflet vive fuera del estado reactivo de Alpine.
    let mapEditor = null;

    return {
        // ─── Servicio (tipo de servicio) ───────────────────────────────
        // — modal crear/editar —
        modalOpen:   false,
        modalMode:   'create',
        form: { id: null, nombre: '', descripcion: '', tipo_vehiculo_ids: [] },

        // — drawer filtros —
        filterOpen: false,

        // — modal confirmar toggle —
        confirmOpen:   false,
        confirmId:     null,
        confirmNombre: '',
        confirmActivo: false,

        // — modal confirmar delete —
        deleteOpen:   false,
        deleteId:     null,
        deleteNombre: '',

        // ─── Zona (gestionada dentro de cada servicio) ─────────────────
        zonaModalOpen:    false,
        zonaModalMode:    'create',
        zonaForm:         emptyZonaForm(),
        selectedServicioNombre: '',
        zonasGuia:        [],
        // Buffer de texto del input de turnos (transitorio, no se envía — sólo se
        // envían los chips ya agregados en zonaForm.turnos). Vive a nivel raíz para
        // seguir definido aunque zonaForm se reconstruya al reabrir tras un error.
        turnoInput:       '',

        // Estado de los collapsibles de Turnos/Horarios/Mapa (bindeados vía
        // x-modelable en <x-ui.collapsible>). Se abren solos si la zona ya trae
        // datos cargados, para que "Editar" no esconda lo guardado.
        turnosSeccionAbierta:   false,
        horariosSeccionAbierta: false,
        mapaSeccionAbierta:     false,

        // — confirmar toggle zona —
        zonaConfirmOpen:   false,
        zonaConfirmId:     null,
        zonaConfirmNombre: '',
        zonaConfirmActivo: false,

        // — confirmar delete zona —
        zonaDeleteOpen:   false,
        zonaDeleteId:     null,
        zonaDeleteNombre: '',

        // — constantes para la vista —
        diasCorto: DIAS_CORTO,
        diasLargo: DIAS_LARGO,

        ...initial,

        // ─── Servicio CRUD ─────────────────────────────────────────────
        openCreate() {
            this.modalMode = 'create';
            this.form      = { id: null, nombre: '', descripcion: '', tipo_vehiculo_ids: [] };
            this.modalOpen = true;
        },

        openEdit(id, nombre, descripcion, tipoVehiculoIds) {
            this.modalMode = 'edit';
            this.form      = { id, nombre, descripcion: descripcion ?? '', tipo_vehiculo_ids: tipoVehiculoIds };
            this.modalOpen = true;
        },

        confirmToggle(id, nombre, activo) {
            this.confirmId     = id;
            this.confirmNombre = nombre;
            this.confirmActivo = activo;
            this.confirmOpen   = true;
        },

        executeToggle() {
            document.getElementById('toggle-servicio-' + this.confirmId).submit();
        },

        confirmDelete(id, nombre) {
            this.deleteId     = id;
            this.deleteNombre = nombre;
            this.deleteOpen   = true;
        },

        executeDelete() {
            document.getElementById('delete-servicio-' + this.deleteId).submit();
        },

        // ─── Mapa (Leaflet + Geoman) — editor del polígono de la zona ──
        initZonaMap() {
            if (mapEditor) return;
            const el = document.getElementById('zona-map');
            if (!el) return;
            mapEditor = createZonaMapEditor({
                onChange: ({ geojson, lat, lng }) => {
                    this.zonaForm.geojson    = geojson;
                    this.zonaForm.centro_lat = lat;
                    this.zonaForm.centro_lng = lng;
                },
            });
            mapEditor.mount(el);
        },

        // Se llama al abrir el modal: inicializa el mapa (lazy) y carga la geometría del form.
        // Como guía solo se muestran las zonas del mismo servicio que la zona en edición.
        syncMapToForm() {
            this.$nextTick(() => {
                this.initZonaMap();
                if (!mapEditor) return;
                const servicioId = Number(this.zonaForm.tipo_servicio_id);
                const guia = (this.zonasGuia || []).filter(
                    (z) => Number(z.tipo_servicio_id) === servicioId,
                );
                mapEditor.show(this.zonaForm.geojson || null, guia, this.zonaForm.id);
            });
        },

        // Se llama al expandir el collapsible del mapa: el contenedor estaba a altura
        // 0 mientras estaba colapsado, así que Leaflet necesita invalidateSize() con
        // las dimensiones reales una vez terminada la animación de apertura (300ms).
        onMapaExpandido() {
            setTimeout(() => this.syncMapToForm(), 320);
        },

        resumenMapa() {
            return this.zonaForm.geojson ? 'Área dibujada' : 'Sin área';
        },

        // ─── Zona CRUD ─────────────────────────────────────────────────
        openCreateZona(servicioId, servicioNombre) {
            this.zonaModalMode          = 'create';
            this.selectedServicioNombre = servicioNombre;
            this.zonaForm               = emptyZonaForm();
            this.zonaForm.tipo_servicio_id = servicioId;
            this.turnoInput             = '';
            this.turnosSeccionAbierta   = false;
            this.horariosSeccionAbierta = false;
            this.mapaSeccionAbierta     = false;
            this.zonaModalOpen          = true;
        },

        openEditZona(zona, servicioNombre) {
            this.zonaModalMode          = 'edit';
            this.selectedServicioNombre = servicioNombre;
            const horariosPorDia = zona.horariosPorDia ?? initHorarios();
            this.zonaForm = {
                id:               zona.id,
                tipo_servicio_id: zona.tipo_servicio_id,
                nombre:           zona.nombre ?? '',
                hectareas:        zona.hectareas ?? '',
                barrios:          zona.barrios ?? '',
                habitantes:       zona.habitantes ?? '',
                geojson:          zona.geojson ? (typeof zona.geojson === 'string' ? zona.geojson : JSON.stringify(zona.geojson)) : '',
                centro_lat:       zona.centro_lat ?? '',
                centro_lng:       zona.centro_lng ?? '',
                turnosEnabled:    (zona.turnos ?? []).length > 0,
                turnos:           zona.turnos ?? [],
                horariosPorDia,
            };
            this.turnoInput             = '';
            // Auto-expandir las secciones que ya traen datos cargados.
            this.turnosSeccionAbierta   = this.zonaForm.turnos.length > 0;
            this.horariosSeccionAbierta = horariosPorDia.some((f) => f.length > 0);
            this.mapaSeccionAbierta     = !!this.zonaForm.geojson;
            this.zonaModalOpen          = true;
        },

        // Turnos de texto libre por zona (sin catálogo): Enter/coma agrega el chip
        // escrito en turnoInput; también se agrega con un clic desde las sugerencias.
        // Editar un turno es sacar el chip y volver a escribirlo.
        addTurno(value = null) {
            const nombre = (value ?? this.turnoInput).trim();
            if (nombre && !this.zonaForm.turnos.includes(nombre)) {
                this.zonaForm.turnos.push(nombre);
            }
            // Solo limpia el input cuando el alta vino de escribir (no de una sugerencia).
            if (value === null) this.turnoInput = '';
        },

        removeTurno(index) {
            this.zonaForm.turnos.splice(index, 1);
        },

        // Sugerencias todavía no agregadas a esta zona.
        turnosSugeridos() {
            return TURNOS_SUGERIDOS.filter((t) => !this.zonaForm.turnos.includes(t));
        },

        // Resumen para el trigger del collapsible (visible aunque esté cerrado).
        resumenTurnos() {
            const n = this.zonaForm.turnos.length;
            if (!this.zonaForm.turnosEnabled || n === 0) return 'Sin turnos';
            return n === 1 ? '1 turno' : `${n} turnos`;
        },

        // ─── Horarios ──────────────────────────────────────────────────
        toggleDia(diaIdx) {
            const franjas = this.zonaForm.horariosPorDia[diaIdx];
            if (franjas.length === 0) {
                this.zonaForm.horariosPorDia[diaIdx] = [{ inicio: '', fin: '' }];
            } else {
                this.zonaForm.horariosPorDia[diaIdx] = [];
            }
        },

        addFranja(diaIdx) {
            this.zonaForm.horariosPorDia[diaIdx].push({ inicio: '', fin: '' });
        },

        removeFranja(diaIdx, franjaIdx) {
            this.zonaForm.horariosPorDia[diaIdx].splice(franjaIdx, 1);
        },

        updateFranja(diaIdx, franjaIdx, field, value) {
            this.zonaForm.horariosPorDia[diaIdx][franjaIdx][field] = value;
        },

        resumenHorarios() {
            const n = this.zonaForm.horariosPorDia.filter((f) => f.length > 0).length;
            if (n === 0) return 'Sin días';
            return n === 1 ? '1 día' : `${n} días`;
        },

        // Preset de días (Lun a Vie / Todos / Fin de semana): si el grupo entero ya
        // está activo lo desactiva; si no, activa los días que falten (con una franja vacía).
        togglePreset(nombre) {
            const indices = PRESETS_DIAS[nombre] ?? [];
            const todosActivos = indices.every((i) => this.zonaForm.horariosPorDia[i].length > 0);
            indices.forEach((i) => {
                if (todosActivos) {
                    this.zonaForm.horariosPorDia[i] = [];
                } else if (this.zonaForm.horariosPorDia[i].length === 0) {
                    this.zonaForm.horariosPorDia[i] = [{ inicio: '', fin: '' }];
                }
            });
        },

        presetActivo(nombre) {
            const indices = PRESETS_DIAS[nombre] ?? [];
            return indices.length > 0 && indices.every((i) => this.zonaForm.horariosPorDia[i].length > 0);
        },

        // ¿Hay algún otro día activo al que copiarle las franjas de este?
        hayOtrosDiasActivos(diaIdx) {
            return this.zonaForm.horariosPorDia.some((f, i) => i !== diaIdx && f.length > 0);
        },

        // Replica las franjas de un día a todos los demás días ya activos (no prende
        // días apagados). Clona cada franja para no compartir referencias.
        copiarFranjasADiasActivos(diaIdx) {
            const origen = this.zonaForm.horariosPorDia[diaIdx];
            if (origen.length === 0) return;
            this.zonaForm.horariosPorDia = this.zonaForm.horariosPorDia.map((franjas, i) => {
                if (i === diaIdx || franjas.length === 0) return franjas;
                return origen.map((f) => ({ ...f }));
            });
        },

        confirmToggleZona(id, nombre, activo) {
            this.zonaConfirmId     = id;
            this.zonaConfirmNombre = nombre;
            this.zonaConfirmActivo = activo;
            this.zonaConfirmOpen   = true;
        },

        executeToggleZona() {
            document.getElementById('toggle-zona-' + this.zonaConfirmId).submit();
        },

        confirmDeleteZona(id, nombre) {
            this.zonaDeleteId     = id;
            this.zonaDeleteNombre = nombre;
            this.zonaDeleteOpen   = true;
        },

        executeDeleteZona() {
            document.getElementById('delete-zona-' + this.zonaDeleteId).submit();
        },
    };
};
