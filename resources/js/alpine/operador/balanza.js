export default function balanza(initial = null, opts = {}) {
    return {
        _cancelUrl:          opts.cancelUrl ?? '/historial',
        query:               initial ? initial.vehiculo.patente : '',
        vehiculo:            initial ? initial.vehiculo : null,
        showSugg:            false,
        matches:             [],
        servicioId:          initial ? String(initial.servicioId) : '',
        servicioNombre:      initial ? initial.servicioNombre : '',
        tipoSugerido:        initial?.tipoSugerido ?? '',
        zonasDisponibles:    initial ? initial.zonasDisponibles : [],
        zonaId:              initial ? String(initial.zonaId) : '',
        zonaNombre:          initial ? initial.zonaNombre : '',
        turnosDisponibles:   initial ? initial.turnosDisponibles : [],
        turno:               initial ? (initial.turno ?? '') : '',
        bruto:               initial ? String(initial.pesoBruto) : '',
        brutoN:              initial ? initial.pesoBruto : 0,
        fechaHoraActual:     '',
        paso1Editando:       false,
        paso2Editando:       !!initial,
        paso3Editando:       !!initial,
        mobileResumenAbierto: false,
        confirmOpen:         false,
        editMode:            !!initial,
        motivo:              '',
        observaciones:       initial?.observaciones ?? '',

        get requiereTurno() { return this.turnosDisponibles.length > 0; },
        get neto() {
            if (!this.vehiculo || this.brutoN <= 0) return 0;
            return Math.max(0, this.brutoN - this.vehiculo.tara);
        },
        get inRange() {
            if (!this.vehiculo?.peso_min || this.brutoN <= 0) return false;
            return this.brutoN >= this.vehiculo.peso_min && this.brutoN <= this.vehiculo.peso_max;
        },
        get outOfRange() {
            if (!this.vehiculo?.peso_min || this.brutoN <= 0) return false;
            return !this.inRange;
        },
        get tipoMismatch() {
            return this.vehiculo && this.tipoSugerido && this.tipoSugerido !== this.vehiculo.tipo;
        },
        get servicioCompleto() {
            return !!(this.servicioId && this.zonaId && (!this.requiereTurno || this.turno));
        },
        get canSave() {
            const base = !!(this.vehiculo && this.servicioId && this.zonaId && (!this.requiereTurno || this.turno) && this.brutoN > 0);
            return this.editMode ? base && !!this.motivo.trim() : base;
        },
        get hintContextual() {
            if (this.editMode) {
                if (!this.motivo.trim()) return 'Describí el motivo de la edición';
                if (this.canSave) return 'Listo para guardar';
                return 'Completá los datos';
            }
            if (this.canSave) return 'Listo para guardar';
            if (this.vehiculo && this.servicioId && this.zonaId && this.requiereTurno && !this.turno) return 'Elegí el turno';
            if (this.vehiculo && this.servicioId && this.zonaId) return 'Ingresá el peso bruto';
            if (this.vehiculo && this.servicioId) return 'Elegí el origen';
            if (this.vehiculo) return 'Elegí el servicio';
            return 'Buscá el vehículo';
        },
        get sucio() {
            if (this.editMode) return false;
            return !!(this.vehiculo || this.servicioId || this.bruto || this.query || this.zonaId);
        },

        init() {
            if (!this.editMode) {
                this.$nextTick(() => this.$refs.inputVehiculo?.focus());
            }
            this.actualizarHora();
            setInterval(() => this.actualizarHora(), 30000);

            this.$watch('vehiculo', (v) => {
                if (v) {
                    this.paso1Editando = false;
                    this.$nextTick(() => this.scrollToPaso(2));
                } else {
                    this.paso1Editando = false;
                    this.paso2Editando = false;
                    this.paso3Editando = false;
                }
            });

            this.$watch('servicioCompleto', (v) => {
                if (v && !this.editMode) {
                    this.paso2Editando = false;
                    this.$nextTick(() => this.scrollToPaso(3));
                }
            });

            if (initial) {
                setTimeout(() => {
                    this._selectEl('wrapServicio')?.dispatchEvent(
                        new CustomEvent('select-sync', { detail: { value: String(initial.servicioId) } })
                    );
                    this._selectEl('wrapOrigen')?.dispatchEvent(
                        new CustomEvent('select-sync', { detail: { value: String(initial.zonaId) } })
                    );
                    if (initial.turno) {
                        this._selectEl('wrapTurno')?.dispatchEvent(
                            new CustomEvent('select-sync', { detail: { value: initial.turno } })
                        );
                    }
                }, 50);
            }
        },

        scrollToPaso(n) {
            const el = document.getElementById(`paso-${n}`);
            if (!el) return;
            const top = el.getBoundingClientRect().top + window.scrollY - 96;
            window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
        },
        actualizarHora() {
            const now = new Date();
            this.fechaHoraActual = now.toLocaleDateString('es-AR') + ' · ' + now.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });
        },
        onKey(e) {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
                e.preventDefault(); this.guardar();
            } else if (e.key === 'Escape') {
                e.preventDefault(); this.limpiar();
            }
        },
        async onQuery() {
            this.vehiculo = null;
            const q = this.query.trim();
            if (!q) { this.matches = []; return; }
            const res = await fetch(`/vehiculos/buscar?q=${encodeURIComponent(q)}`);
            this.matches = await res.json();
            this.showSugg = true;
        },
        _selectEl(ref) {
            return this.$refs[ref]?.querySelector('[x-data]') ?? null;
        },
        seleccionar(v) {
            this.vehiculo = v;
            this.query = v.patente;
            this.showSugg = false;
            this.$nextTick(() => this.$refs.wrapServicio?.querySelector('button')?.focus());
        },
        enterVehiculo() {
            if (this.matches.length) this.seleccionar(this.matches[0]);
            else if (this.vehiculo) this.$refs.wrapServicio?.querySelector('button')?.focus();
        },
        onSelectServicio({ value, label }) {
            this.servicioId = value;
            this.servicioNombre = label ?? '';
            this.zonaId = ''; this.zonaNombre = ''; this.turno = '';
            this.turnosDisponibles = []; this.zonasDisponibles = []; this.tipoSugerido = '';
            this._selectEl('wrapOrigen')?.dispatchEvent(new CustomEvent('select-sync', { detail: { value: '' } }));
            this._selectEl('wrapOrigen')?.dispatchEvent(new CustomEvent('select-items-clear'));
            this._selectEl('wrapTurno')?.dispatchEvent(new CustomEvent('select-sync',  { detail: { value: '' } }));
            this._selectEl('wrapTurno')?.dispatchEvent(new CustomEvent('select-items-clear'));
            if (!value) return;
            fetch(`/servicios/${value}/zonas`)
                .then(r => r.json())
                .then(data => {
                    this.tipoSugerido     = data.tipo_vehiculo_sugerido ?? '';
                    this.zonasDisponibles = data.zonas ?? [];
                    this.$nextTick(() => this.$refs.wrapOrigen?.querySelector('button')?.focus());
                });
        },
        onZonaChange({ value, label }) {
            this.zonaId = value;
            const zona = this.zonasDisponibles.find(z => String(z.id) === String(value));
            this.zonaNombre        = zona?.nombre ?? label ?? '';
            this.turnosDisponibles = zona?.turnos ?? [];
            this.turno = '';
            this._selectEl('wrapTurno')?.dispatchEvent(new CustomEvent('select-sync', { detail: { value: '' } }));
            this._selectEl('wrapTurno')?.dispatchEvent(new CustomEvent('select-items-clear'));
        },
        onBruto() {
            const digits = this.bruto.replace(/\D/g, '').slice(0, 6);
            this.bruto = digits;
            const n = parseInt(digits, 10);
            this.brutoN = isNaN(n) ? 0 : n;
            if (this.brutoN > 0) this.paso3Editando = true;
        },
        limpiar() {
            if (this.editMode) {
                window.location.href = this._cancelUrl;
                return;
            }
            this.query = ''; this.vehiculo = null; this.showSugg = false; this.matches = [];
            this.servicioId = ''; this.servicioNombre = ''; this.tipoSugerido = '';
            this.zonasDisponibles = []; this.zonaId = ''; this.zonaNombre = '';
            this.turnosDisponibles = []; this.turno = ''; this.bruto = ''; this.brutoN = 0;
            this.paso1Editando = false; this.paso2Editando = false; this.paso3Editando = false;
            this.mobileResumenAbierto = false;
            this._selectEl('wrapServicio')?.dispatchEvent(new CustomEvent('select-sync', { detail: { value: '' } }));
            ['wrapOrigen', 'wrapTurno'].forEach(ref => {
                this._selectEl(ref)?.dispatchEvent(new CustomEvent('select-sync', { detail: { value: '' } }));
                this._selectEl(ref)?.dispatchEvent(new CustomEvent('select-items-clear'));
            });
            this.$nextTick(() => this.$refs.inputVehiculo?.focus());
        },
        guardar() {
            if (!this.canSave) return;
            if (this.editMode) {
                window.onbeforeunload = null;
                this.$refs.form.submit();
            } else {
                this.confirmOpen = true;
            }
        },
        confirmar() {
            window.onbeforeunload = null;
            this.$refs.form.submit();
        },
        setBeforeUnload(sucio) {
            window.onbeforeunload = sucio ? () => true : null;
        },
        fmtKg(n) { return n == null ? '—' : n.toLocaleString('es-AR') + ' kg'; },
        fmtN(n)   { return n ? n.toLocaleString('es-AR') : ''; },
    };
}
