import { createZonaMapEditor } from '../../maps/zona-map-editor.js';

const DIAS_CORTO = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
const DIAS_LARGO = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
const initHorarios = () => Array.from({ length: 7 }, () => []);
const emptyForm = () => ({ id: null, nombre: '', hectareas: '', barrios: '', habitantes: '', geojson: '', centro_lat: '', centro_lng: '' });

export default (initial = {}) => {
    // El mapa de Leaflet vive fuera del estado reactivo de Alpine.
    let mapEditor = null;

    return {
    // — modal crear/editar zona —
    modalOpen: false,
    modalMode: 'create',
    form: emptyForm(),
    zonasGuia: [],

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

    // — modal asignar/editar servicio —
    servicioModalOpen:    false,
    servicioModalMode:    'create',
    selectedZonaId:       null,
    selectedZonaNombre:   '',
    assignedServicioIds:  [],
    editServicioId:       null,
    editServicioNombre:   '',
    servicioForm: {
        tipo_servicio_id: '',
        turnosEnabled:    false,
        turnos:           [],
        horariosPorDia:   initHorarios(),
    },

    // — modal confirmar quitar servicio —
    quitarOpen:          false,
    quitarZonaId:        null,
    quitarZonaNombre:    '',
    quitarServicioId:    null,
    quitarServicioNombre: '',

    // — constantes para la vista —
    diasCorto: DIAS_CORTO,
    diasLargo:  DIAS_LARGO,

    ...initial,

    // mapa (Leaflet + Geoman) — editor del polígono de la zona
    initZonaMap() {
        if (mapEditor) return;
        const el = document.getElementById('zona-map');
        if (!el) return;
        mapEditor = createZonaMapEditor({
            onChange: ({ geojson, lat, lng }) => {
                this.form.geojson    = geojson;
                this.form.centro_lat = lat;
                this.form.centro_lng = lng;
            },
        });
        mapEditor.mount(el);
    },

    // Se llama al abrir el modal: inicializa el mapa (lazy) y carga la geometría del form.
    syncMapToForm() {
        this.$nextTick(() => {
            this.initZonaMap();
            if (mapEditor) mapEditor.show(this.form.geojson || null, this.zonasGuia || [], this.form.id);
        });
    },

    // zona CRUD
    openCreate() {
        this.modalMode = 'create';
        this.form      = emptyForm();
        this.modalOpen = true;
    },

    openEdit(id, nombre, hectareas, barrios, habitantes, geojson, centroLat, centroLng) {
        this.modalMode = 'edit';
        this.form      = {
            id,
            nombre,
            hectareas:  hectareas ?? '',
            barrios:    barrios ?? '',
            habitantes: habitantes ?? '',
            geojson:    geojson ? (typeof geojson === 'string' ? geojson : JSON.stringify(geojson)) : '',
            centro_lat: centroLat ?? '',
            centro_lng: centroLng ?? '',
        };
        this.modalOpen = true;
    },

    confirmToggle(id, nombre, activo) {
        this.confirmId     = id;
        this.confirmNombre = nombre;
        this.confirmActivo = activo;
        this.confirmOpen   = true;
    },

    executeToggle() {
        document.getElementById('toggle-' + this.confirmId).submit();
    },

    confirmDelete(id, nombre) {
        this.deleteId     = id;
        this.deleteNombre = nombre;
        this.deleteOpen   = true;
    },

    executeDelete() {
        document.getElementById('delete-' + this.deleteId).submit();
    },

    // servicios
    openAsignarServicio(zonaId, zonaNombre, assignedIds = []) {
        this.selectedZonaId      = zonaId;
        this.selectedZonaNombre  = zonaNombre;
        this.assignedServicioIds = assignedIds;
        this.servicioModalMode   = 'create';
        this.editServicioId      = null;
        this.editServicioNombre  = '';
        this.servicioForm        = { tipo_servicio_id: '', turnosEnabled: false, turnos: [], horariosPorDia: initHorarios() };
        this.servicioModalOpen   = true;
    },

    openEditServicio(zonaId, zonaNombre, tipoServicioId, tipoServicioNombre, turnos, horariosPorDia) {
        this.selectedZonaId     = zonaId;
        this.selectedZonaNombre = zonaNombre;
        this.servicioModalMode  = 'edit';
        this.editServicioId     = tipoServicioId;
        this.editServicioNombre = tipoServicioNombre;
        this.servicioForm       = {
            tipo_servicio_id: tipoServicioId,
            turnosEnabled:    turnos.length > 0,
            turnos:           turnos,
            horariosPorDia:   horariosPorDia,
        };
        this.servicioModalOpen = true;
    },

    toggleTurno(turno) {
        const idx = this.servicioForm.turnos.indexOf(turno);
        if (idx >= 0) {
            this.servicioForm.turnos.splice(idx, 1);
        } else {
            this.servicioForm.turnos.push(turno);
        }
    },

    // horarios
    toggleDia(diaIdx) {
        const franjas = this.servicioForm.horariosPorDia[diaIdx];
        if (franjas.length === 0) {
            this.servicioForm.horariosPorDia[diaIdx] = [{ inicio: '', fin: '' }];
        } else {
            this.servicioForm.horariosPorDia[diaIdx] = [];
        }
    },

    addFranja(diaIdx) {
        this.servicioForm.horariosPorDia[diaIdx].push({ inicio: '', fin: '' });
    },

    removeFranja(diaIdx, franjaIdx) {
        this.servicioForm.horariosPorDia[diaIdx].splice(franjaIdx, 1);
    },

    updateFranja(diaIdx, franjaIdx, field, value) {
        this.servicioForm.horariosPorDia[diaIdx][franjaIdx][field] = value;
    },

    confirmQuitarServicio(zonaId, zonaNombre, servicioId, servicioNombre) {
        this.quitarZonaId        = zonaId;
        this.quitarZonaNombre    = zonaNombre;
        this.quitarServicioId    = servicioId;
        this.quitarServicioNombre = servicioNombre;
        this.quitarOpen          = true;
    },

    executeQuitarServicio() {
        document.getElementById('quitar-' + this.quitarZonaId + '-' + this.quitarServicioId).submit();
    },
    };
};
