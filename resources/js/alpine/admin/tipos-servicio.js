import { createZonaMapEditor } from '../../maps/zona-map-editor.js';

const DIAS_CORTO = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
const DIAS_LARGO = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
const initHorarios = () => Array.from({ length: 7 }, () => []);
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
        form: { id: null, nombre: '', tipo_vehiculo_ids: [] },

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
            this.form      = { id: null, nombre: '', tipo_vehiculo_ids: [] };
            this.modalOpen = true;
        },

        openEdit(id, nombre, tipoVehiculoIds) {
            this.modalMode = 'edit';
            this.form      = { id, nombre, tipo_vehiculo_ids: tipoVehiculoIds };
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

        // ─── Zona CRUD ─────────────────────────────────────────────────
        openCreateZona(servicioId, servicioNombre) {
            this.zonaModalMode          = 'create';
            this.selectedServicioNombre = servicioNombre;
            this.zonaForm               = emptyZonaForm();
            this.zonaForm.tipo_servicio_id = servicioId;
            this.zonaModalOpen          = true;
        },

        openEditZona(zona, servicioNombre) {
            this.zonaModalMode          = 'edit';
            this.selectedServicioNombre = servicioNombre;
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
                horariosPorDia:   zona.horariosPorDia ?? initHorarios(),
            };
            this.zonaModalOpen = true;
        },

        toggleTurno(turno) {
            const idx = this.zonaForm.turnos.indexOf(turno);
            if (idx >= 0) {
                this.zonaForm.turnos.splice(idx, 1);
            } else {
                this.zonaForm.turnos.push(turno);
            }
        },

        // horarios
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
