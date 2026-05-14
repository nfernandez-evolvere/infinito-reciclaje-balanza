/* global React, Icon, Button, Field, Card, Pill, Badge, Banner, SuccessOverlay,
   VEHICLES, SERVICIOS, SERVICIO_CASCADE, ORIGENES_DATA, ORIGEN_SERVICIOS, VEHICLE_TYPES, fmtKg, fmtN */
const { useState, useMemo, useRef, useEffect } = React;

function Balanza({ pesajes, onSave, onDirtyChange }) {
  const [query, setQuery] = useState("");
  const [vehicle, setVehicle] = useState(null);
  const [showSugg, setShowSugg] = useState(false);
  const [servicio, setServicio] = useState("");
  const [origen, setOrigen] = useState("");
  const [turno, setTurno] = useState("");
  const [bruto, setBruto] = useState("");
  const [success, setSuccess] = useState(null);

  const vehicleRef = useRef(null);
  const servicioRef = useRef(null);
  const brutoRef = useRef(null);

  const matches = useMemo(() => {
    const q = query.trim().toLowerCase();
    if (!q) return [];
    return VEHICLES.filter((v) =>
      v.patente.toLowerCase().includes(q) || v.interno.includes(q)
    ).slice(0, 6);
  }, [query]);

  const cascade = servicio ? SERVICIO_CASCADE[servicio] : null;
  const tipoSugerido = cascade?.tipoSugerido;

  // Orígenes activos que tienen este servicio asignado en ORIGEN_SERVICIOS
  const origenesDelServicio = servicio
    ? ORIGEN_SERVICIOS
        .filter((a) => a.servicioNombre === servicio)
        .map((a) => ORIGENES_DATA.find((z) => z.id === a.origenId))
        .filter((z) => z && z.estado === "Activo")
    : [];

  // Turnos disponibles para la combinación origen+servicio seleccionada
  const asignacionActual = origen && servicio
    ? ORIGEN_SERVICIOS.find((a) => a.servicioNombre === servicio && ORIGENES_DATA.find((z) => z.id === a.origenId)?.nombre === origen)
    : null;
  const turnosDelServicio = asignacionActual?.turnos ?? [];
  const servicioRequiereTurno = turnosDelServicio.length > 0;
  const tipoActivo = vehicle?.tipo;
  const tipoMismatch = vehicle && tipoSugerido && tipoSugerido !== tipoActivo;
  const range = tipoActivo ? VEHICLE_TYPES[tipoActivo] : null;
  const brutoN = parseInt((bruto || "").replace(/\D/g, ""), 10) || 0;
  const neto = vehicle && brutoN ? Math.max(0, brutoN - vehicle.tara) : 0;
  const inRange = range && brutoN >= range.rangeMin && brutoN <= range.rangeMax;
  const outOfRange = range && brutoN > 0 && !inRange;

  const selectVehicle = (v) => {
    setVehicle(v);
    setQuery(v.patente);
    setShowSugg(false);
    setTimeout(() => servicioRef.current?.focus(), 50);
  };

  const pickService = (s) => {
    setServicio(s);
    setOrigen("");
    setTurno("");
    if (s) setTimeout(() => brutoRef.current?.focus(), 50);
  };

  const reset = () => {
    setQuery(""); setVehicle(null); setServicio("");
    setOrigen(""); setTurno(""); setBruto("");
    setTimeout(() => vehicleRef.current?.focus(), 50);
  };

  const canSave = vehicle && servicio && origen && (!servicioRequiereTurno || turno) && brutoN > 0;

  const save = () => {
    if (!canSave) return;
    const now = new Date();
    const hora = now.toLocaleTimeString("es-AR", { hour: "2-digit", minute: "2-digit" });
    onSave({
      id: Date.now(),
      horaEntrada: hora, horaSalida: null, brutoSalida: null,
      patente: vehicle.patente, tipo: tipoActivo,
      servicio, origen, turno: turno || null, bruto: brutoN, tara: vehicle.tara, neto,
      operador: "roberto",
      estado: "En predio",
    });
    setSuccess({ patente: vehicle.patente, neto });
    reset();
  };

  // Global keyboard shortcuts
  useEffect(() => {
    const handler = (e) => {
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === "s") {
        e.preventDefault();
        save();
      } else if (e.key === "Escape") {
        e.preventDefault();
        reset();
      }
    };
    window.addEventListener("keydown", handler);
    return () => window.removeEventListener("keydown", handler);
  }, [canSave, vehicle, servicio, origen, brutoN]);

  useEffect(() => { vehicleRef.current?.focus(); }, []);

  // Inform parent whenever the form has any user-entered data
  useEffect(() => {
    const dirty = !!(vehicle || servicio || bruto || query || origen);
    onDirtyChange && onDirtyChange(dirty);
  }, [vehicle, servicio, bruto, query, origen, onDirtyChange]);

  return (
    <div className="page" style={{ maxWidth: 880 }}>
      <div style={{ display: "flex", alignItems: "baseline", gap: 16, marginBottom: 4 }}>
        <h1>Registro de pesaje</h1>
        <Pill kind="green" dot>Balanza en línea</Pill>
      </div>
      <p className="lede">Seguí los tres pasos. Los datos del padrón se completan solos.</p>

      <div className="kbd-bar" style={{ marginBottom: 16 }}>
        <Icon name="keyboard" size={14} />
        <span className="kbd"><kbd>↵</kbd> siguiente campo</span>
        <span className="kbd"><kbd>Ctrl</kbd>+<kbd>S</kbd> guardar</span>
        <span className="kbd"><kbd>Esc</kbd> limpiar</span>
      </div>

      <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
        {/* Step 1: Vehicle */}
        <Card>
          <Step number={1} title="Vehículo" complete={!!vehicle} last>
            <div className="field" style={{ position: "relative" }}>
              <input
                ref={vehicleRef}
                className="input"
                placeholder="Patente o número interno"
                value={query}
                onChange={(e) => { setQuery(e.target.value); setVehicle(null); setShowSugg(true); }}
                onFocus={() => setShowSugg(true)}
                onBlur={() => setTimeout(() => setShowSugg(false), 120)}
                onKeyDown={(e) => {
                  if (e.key === "Enter" && matches.length) {
                    e.preventDefault();
                    selectVehicle(matches[0]);
                  } else if (e.key === "Enter" && vehicle) {
                    e.preventDefault();
                    servicioRef.current?.focus();
                  }
                }}
              />
              {showSugg && matches.length > 0 && (
                <div className="popper">
                  {matches.map((v) => (
                    <div key={v.id} className="opt" onMouseDown={(e) => { e.preventDefault(); selectVehicle(v); }}>
                      <div><b>{v.patente}</b> <span style={{ color: "var(--ink-500)" }}>· int. {v.interno}</span></div>
                      <div className="sub">{v.tipo} · Tara {fmtKg(v.tara)} · {v.titular}</div>
                    </div>
                  ))}
                </div>
              )}
            </div>
            {vehicle && (
              <div style={{ display: "flex", gap: 8, marginTop: 12, flexWrap: "wrap" }}>
                <Badge label="Tara" value={fmtKg(vehicle.tara)} />
                <Badge label="Tipo" value={vehicle.tipo} />
                <Badge label="Titular" value={vehicle.titular === "Municipal" ? "Municipalidad de Corrientes" : vehicle.titular} />
                <Badge label="Interno" value={vehicle.interno} />
              </div>
            )}
          </Step>
        </Card>

        {/* Step 2: Service + Zone + Turno */}
        <Card>
          <Step number={2} title="Tipo de servicio y origen" complete={!!servicio && !!origen && (!servicioRequiereTurno || !!turno)} disabled={!vehicle} last>
            <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
              <Field label="Tipo de servicio">
                <select
                  ref={servicioRef}
                  className="select"
                  value={servicio}
                  onChange={(e) => pickService(e.target.value)}
                  disabled={!vehicle}
                  onKeyDown={(e) => {
                    if (e.key === "Enter" && servicio) {
                      e.preventDefault();
                      brutoRef.current?.focus();
                    }
                  }}
                >
                  <option value="">Seleccionar servicio…</option>
                  {SERVICIOS.map((s) => <option key={s} value={s}>{s}</option>)}
                </select>
              </Field>
              {servicio && (
                <Field label="Origen">
                  <select
                    className="select"
                    value={origen}
                    onChange={(e) => { setOrigen(e.target.value); setTurno(""); }}
                  >
                    <option value="">Seleccionar origen…</option>
                    {origenesDelServicio.map((z) => <option key={z.id} value={z.nombre}>{z.nombre}</option>)}
                  </select>
                </Field>
              )}
              {servicio && servicioRequiereTurno && (
                <Field label="Turno" hint="Requerido para este servicio.">
                  <select
                    className="select"
                    value={turno}
                    onChange={(e) => setTurno(e.target.value)}
                  >
                    <option value="">Seleccionar turno…</option>
                    {turnosDelServicio.map((t) => <option key={t} value={t}>{t}</option>)}
                  </select>
                </Field>
              )}
            </div>
            {servicio && (
              <div style={{ display: "flex", gap: 8, marginTop: 12, flexWrap: "wrap", alignItems: "center" }}>
                <Badge kind="blue" label="Tipo habitual" value={tipoSugerido} />
              </div>
            )}
            {tipoMismatch && (
              <div className="cascade-warn">
                <div className="ic"><Icon name="alert-triangle" size={16} /></div>
                <div>
                  <b>No es el tipo habitual para este servicio.</b><br />
                  Para <b>{servicio}</b> se espera un <b>{tipoSugerido}</b>; este vehículo es <b>{tipoActivo}</b>. El pesaje se guarda igual.
                </div>
              </div>
            )}
          </Step>
        </Card>

        {/* Step 3: Weight */}
        <Card>
          <Step number={3} title="Peso bruto" complete={brutoN > 0} disabled={!servicio} last>
            <div className={"readout-shell " + (outOfRange ? "warn" : inRange ? "ok" : "")}>
              <div>
                <div className="readout-label">Peso bruto en balanza</div>
                <div style={{ display: "flex", alignItems: "baseline", gap: 8 }}>
                  <input
                    ref={brutoRef}
                    className={"readout-input num" + (outOfRange ? " warn" : inRange ? " ok" : "")}
                    placeholder="0"
                    value={bruto ? parseInt(bruto.replace(/\D/g, ""), 10).toLocaleString("es-AR") : ""}
                    onChange={(e) => setBruto(e.target.value)}
                    disabled={!servicio}
                    inputMode="numeric"
                    onKeyDown={(e) => {
                      if (e.key === "Enter" && canSave) {
                        e.preventDefault();
                        save();
                      }
                    }}
                  />
                  <span className="readout-unit">kg</span>
                </div>
              </div>
              <div className="readout-side">
                <div className="row"><span>Tara</span><b>{vehicle ? fmtKg(vehicle.tara) : "—"}</b></div>
                <div className="row neto"><span>Neto estimado</span><b>{vehicle && brutoN ? fmtKg(neto) : "—"}</b></div>
              </div>
            </div>
            {range && (
              <div style={{ marginTop: 10, fontSize: 13, color: outOfRange ? "var(--orange-700)" : "var(--ink-500)" }}>
                {outOfRange
                  ? <><b>Fuera del rango habitual para {tipoActivo}</b> ({fmtN(range.rangeMin)} – {fmtN(range.rangeMax)} kg). La validación no bloquea el guardado.</>
                  : <>Rango habitual {tipoActivo}: {fmtN(range.rangeMin)} – {fmtN(range.rangeMax)} kg.</>}
              </div>
            )}
          </Step>
        </Card>

        {/* Summary */}
        <Card style={{ background: canSave ? "var(--green-50)" : "var(--surface)" }}>
          <div className="card-title">
            <span>Resumen del pesaje</span>
            <span className="subtitle">{new Date().toLocaleDateString("es-AR")} · {new Date().toLocaleTimeString("es-AR", { hour: "2-digit", minute: "2-digit" })}</span>
          </div>
          <div style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", gap: 16 }}>
            <SummaryCell label="Vehículo"  value={vehicle ? vehicle.patente : "—"} />
            <SummaryCell label="Servicio"  value={servicio || "—"} />
            <SummaryCell label="Origen"    value={origen || "—"} />
            <SummaryCell label="Turno"     value={turno || "—"} />
            <SummaryCell label="Tipo"      value={tipoActivo || "—"} />
            <SummaryCell label="Peso bruto" value={brutoN ? fmtKg(brutoN) : "—"} num />
            <SummaryCell label="Tara"       value={vehicle ? fmtKg(vehicle.tara) : "—"} num />
            <SummaryCell label="Neto"       value={vehicle && brutoN ? fmtKg(neto) : "—"} num strong />
            <SummaryCell label="Operador" value="Roberto" />
          </div>
        </Card>
      </div>

      {/* Sticky action bar */}
      <div className="action-bar">
        <Button kind="secondary" icon="rotate-ccw" onClick={reset}>Limpiar <span className="kbd" style={{ marginLeft: 6, opacity: 0.7 }}><kbd>Esc</kbd></span></Button>
        <div className="spacer" />
        <span className="muted body-sm">
          {canSave ? "Listo para guardar"
          : vehicle && servicio && origen && servicioRequiereTurno && !turno ? "Elegí el turno"
          : vehicle && servicio && origen ? "Ingresá el peso bruto"
          : vehicle && servicio ? "Elegí el origen"
          : vehicle ? "Elegí el servicio"
          : "Buscá el vehículo"}
        </span>
        <Button kind="save" onClick={save} disabled={!canSave} icon="save">
          Guardar pesaje
          <span className="kbd" style={{ marginLeft: 8, color: "rgba(255,255,255,0.9)" }}><kbd style={{ background: "rgba(255,255,255,0.18)", borderColor: "rgba(255,255,255,0.3)", color: "white" }}>Ctrl</kbd>+<kbd style={{ background: "rgba(255,255,255,0.18)", borderColor: "rgba(255,255,255,0.3)", color: "white" }}>S</kbd></span>
        </Button>
      </div>

      {success && <SuccessOverlay patente={success.patente} neto={success.neto} onDone={() => setSuccess(null)} />}
    </div>
  );
}

function Step({ number, title, disabled, complete, last, children }) {
  return (
    <div style={{
      paddingBottom: last ? 0 : 20, marginBottom: last ? 0 : 20,
      borderBottom: last ? "0" : "1px dashed var(--line)",
      opacity: disabled ? 0.45 : 1,
      transition: "opacity 180ms var(--ease)",
    }}>
      <div style={{ display: "flex", alignItems: "center", gap: 12, marginBottom: 14 }}>
        <div style={{
          width: 28, height: 28, borderRadius: 999,
          background: complete ? "var(--green-700)" : disabled ? "var(--bg)" : "var(--surface)",
          border: complete ? "0" : "1.5px solid " + (disabled ? "var(--ink-300)" : "var(--green-700)"),
          color: complete ? "white" : disabled ? "var(--ink-500)" : "var(--green-700)",
          display: "grid", placeItems: "center", fontWeight: 700, fontSize: 13,
          transition: "all 180ms var(--ease)",
        }}>
          {complete ? <Icon name="check" size={16} /> : number}
        </div>
        <h3 style={{ margin: 0, fontSize: 18 }}>{title}</h3>
      </div>
      {children}
    </div>
  );
}

function SummaryCell({ label, value, num, strong }) {
  return (
    <div>
      <div style={{ fontSize: 11, color: "var(--ink-500)", fontWeight: 600, letterSpacing: "0.04em", textTransform: "uppercase", marginBottom: 4 }}>{label}</div>
      <div className={num ? "num" : ""} style={{ fontSize: strong ? 22 : 15, fontWeight: strong ? 700 : 600, color: "var(--ink-900)", lineHeight: 1.1 }}>{value}</div>
    </div>
  );
}

window.Balanza = Balanza;
