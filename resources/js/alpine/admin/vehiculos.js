export default (initial = {}) => ({
    modalOpen:   false,
    modalMode:   'create',
    form: {
        id:               null,
        patente:          '',
        numero_interno:   '',
        tara_kg:          '',
        tipo_vehiculo_id: '',
        titular:          '',
        capacidad_kg:     '',
        observaciones:    '',
        // Decisión de corrección de tara (solo en edición)
        _tara_original:   '',
        pesajes_count:    0,
        _intencion_tara:  '',
        _motivo_tara:     '',
    },
    filterOpen:    false,
    confirmOpen:   false,
    confirmId:     null,
    confirmNombre: '',
    confirmActivo: false,
    deleteOpen:    false,
    deleteId:      null,
    deleteNombre:  '',
    // Sub-paso de decisión de tara: false = mostrando opciones, true = confirmada (resumen)
    taraDecisionConfirmada: false,
    // Envío en curso: el form navega, así que solo da feedback entre el click y la recarga.
    saving: false,

    ...initial,

    openCreate() {
        this.modalMode = 'create';
        this.taraDecisionConfirmada = false;
        this.saving    = false;
        this.form      = {
            id:               null,
            patente:          '',
            numero_interno:   '',
            tara_kg:          '',
            tipo_vehiculo_id: '',
            titular:          '',
            capacidad_kg:     '',
            observaciones:    '',
            _tara_original:   '',
            pesajes_count:    0,
            _intencion_tara:  '',
            _motivo_tara:     '',
        };
        this.modalOpen = true;
    },

    openEdit(id, patente, numeroInterno, taraKg, tipoVehiculoId, titular, capacidadKg, observaciones, pesajesCount = 0) {
        this.modalMode = 'edit';
        this.taraDecisionConfirmada = false;
        this.saving    = false;
        this.form      = {
            id,
            patente,
            numero_interno:   numeroInterno,
            tara_kg:          taraKg,
            tipo_vehiculo_id: tipoVehiculoId,
            titular,
            capacidad_kg:     capacidadKg,
            observaciones:    observaciones,
            _tara_original:   taraKg,
            pesajes_count:    pesajesCount,
            _intencion_tara:  '',
            _motivo_tara:     '',
        };
        this.modalOpen = true;
    },

    // ¿La tara cambió en un vehículo que ya tiene pesajes? (requiere decisión)
    get taraCambioConPesajes() {
        return this.modalMode === 'edit'
            && Number(this.form.pesajes_count) > 0
            && this.form.tara_kg !== '' && this.form._tara_original !== ''
            && Number(this.form.tara_kg) !== Number(this.form._tara_original);
    },

    // Sub-paso 1: elegir cómo aplicar el cambio (oculta los demás campos).
    get mostrarDecisionTara() {
        return this.taraCambioConPesajes && !this.taraDecisionConfirmada;
    },

    // Sub-paso 2: decisión confirmada → resumen + campos visibles de nuevo.
    get mostrarResumenTara() {
        return this.taraCambioConPesajes && this.taraDecisionConfirmada;
    },

    // Texto del botón de envío según el estado y el tipo de operación.
    get textoGuardar() {
        if (this.saving) {
            if (this.modalMode === 'create') return 'Creando…';
            if (this.form._intencion_tara === 'corregir_dato' && this.mostrarResumenTara) {
                return 'Recalculando pesajes…';
            }
            return 'Guardando…';
        }
        return this.modalMode === 'create' ? 'Crear' : 'Guardar cambios';
    },

    confirmarDecisionTara() {
        if (!this.form._intencion_tara || !String(this.form._motivo_tara).trim()) return;
        this.taraDecisionConfirmada = true;
    },

    cancelarDecisionTara() {
        // Descarta el cambio de tara y vuelve a la edición normal.
        this.form.tara_kg        = this.form._tara_original;
        this.form._intencion_tara = '';
        this.form._motivo_tara    = '';
        this.taraDecisionConfirmada = false;
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
});
