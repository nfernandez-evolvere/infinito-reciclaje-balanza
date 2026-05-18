const DIAS_CORTO = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
const DIAS_LARGO = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
const initHorarios = () => Array.from({ length: 7 }, () => []);

export default (initial = {}) => ({
    // — modal crear/editar zona —
    modalOpen: false,
    modalMode: 'create',
    form: { id: null, nombre: '', hectareas: '', barrios: '', habitantes: '' },

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

    // zona CRUD
    openCreate() {
        this.modalMode = 'create';
        this.form      = { id: null, nombre: '', hectareas: '', barrios: '', habitantes: '' };
        this.modalOpen = true;
    },

    openEdit(id, nombre, hectareas, barrios, habitantes) {
        this.modalMode = 'edit';
        this.form      = { id, nombre, hectareas: hectareas ?? '', barrios: barrios ?? '', habitantes: habitantes ?? '' };
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
});
