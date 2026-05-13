/* global React, Button, Card, Pill, Modal, Field, Icon,
   ZONAS_DATA, ZONA_SERVICIOS, SERVICIOS */
const { useState } = React;

const DIAS = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo"];
const TURNOS_OPCIONES = ["Diurna", "Nocturna"];

const initHorarios = () => DIAS.map(() => []);

function HorarioEditor({ horariosPorDia, onChange }) {
  const addFranja = (diaIdx) => {
    const next = horariosPorDia.map((f, i) => i === diaIdx ? [...f, { inicio: "", fin: "" }] : f);
    onChange(next);
  };
  const removeFranja = (diaIdx, franjaIdx) => {
    const next = horariosPorDia.map((f, i) => i === diaIdx ? f.filter((_, j) => j !== franjaIdx) : f);
    onChange(next);
  };
  const updateFranja = (diaIdx, franjaIdx, field, value) => {
    const next = horariosPorDia.map((f, i) =>
      i === diaIdx ? f.map((fr, j) => j === franjaIdx ? { ...fr, [field]: value } : fr) : f
    );
    onChange(next);
  };

  return (
    <div style={{ display: "flex", flexDirection: "column", gap: 6 }}>
      {DIAS.map((diaNombre, diaIdx) => (
        <div key={diaIdx} style={{
          border: "1px solid var(--border)",
          borderRadius: 8,
          padding: "8px 12px",
          background: "var(--surface-50, var(--card))",
        }}>
          <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: horariosPorDia[diaIdx].length > 0 ? 8 : 0 }}>
            <span style={{ fontSize: 13, fontWeight: 600, color: "var(--ink-700)" }}>{diaNombre}</span>
            <button
              className="btn btn-sm"
              style={{ fontSize: 11, padding: "2px 8px", height: 22, display: "flex", alignItems: "center", gap: 3, background: "transparent", border: "1px dashed var(--primary)", color: "var(--primary)", borderRadius: 5, cursor: "pointer", fontWeight: 500 }}
              onClick={() => addFranja(diaIdx)}
            >
              <Icon name="plus" size={11} /> Agregar otra franja
            </button>
          </div>
          {horariosPorDia[diaIdx].length > 0 && (
            <div style={{ display: "flex", flexDirection: "column", gap: 4 }}>
              {horariosPorDia[diaIdx].map((fr, franjaIdx) => (
                <div key={franjaIdx} style={{ display: "flex", alignItems: "center", gap: 4, width: "fit-content", background: "var(--background)", border: "1px solid var(--border)", borderRadius: 6, padding: "3px 8px" }}>
                  <input
                    className="input" type="time"
                    style={{ width: 84, padding: "1px 4px", fontSize: 12, height: 24 }}
                    value={fr.inicio}
                    onChange={(e) => updateFranja(diaIdx, franjaIdx, "inicio", e.target.value)}
                  />
                  <span style={{ fontSize: 12, color: "var(--ink-400)" }}>–</span>
                  <input
                    className="input" type="time"
                    style={{ width: 84, padding: "1px 4px", fontSize: 12, height: 24 }}
                    value={fr.fin}
                    onChange={(e) => updateFranja(diaIdx, franjaIdx, "fin", e.target.value)}
                  />
                  <button className="btn btn-ghost btn-sm" style={{ padding: "0 3px", height: 22, marginLeft: 2 }}
                    onClick={() => removeFranja(diaIdx, franjaIdx)} title="Quitar franja">
                    <Icon name="x" size={11} />
                  </button>
                </div>
              ))}
            </div>
          )}
        </div>
      ))}
    </div>
  );
}

function HorarioResumen({ horarios }) {
  if (!horarios || horarios.length === 0) return <span style={{ color: "var(--ink-400)" }}>—</span>;
  return (
    <div style={{ display: "flex", flexDirection: "column", gap: 2 }}>
      {horarios.map((h) => (
        <span key={h.dia} style={{ fontSize: 12, color: "var(--ink-500)" }}>
          <b style={{ color: "var(--ink-700)" }}>{h.diaNombre.slice(0, 3)}</b>{" "}
          {h.franjas.map((f) => `${f.inicio}–${f.fin}`).join(", ")}
        </span>
      ))}
    </div>
  );
}

function AbmZonas() {
  const [rows, setRows] = useState(ZONAS_DATA);
  const [asignaciones, setAsignaciones] = useState(ZONA_SERVICIOS);
  const [modalOpen, setModalOpen] = useState(false);
  const [asignModalOpen, setAsignModalOpen] = useState(false);
  const [selectedZona, setSelectedZona] = useState(null);
  const [draft, setDraft] = useState({ nombre: "", hectareas: "", barrios: "", estado: "Activo" });
  const [asignDraft, setAsignDraft] = useState({ servicioNombre: "", turnos: [], horariosPorDia: initHorarios() });

  const submit = () => {
    if (!draft.nombre.trim()) return;
    const newZona = { id: Date.now(), nombre: draft.nombre, hectareas: parseFloat(draft.hectareas) || 0, barrios: parseInt(draft.barrios, 10) || 0, estado: draft.estado };
    setRows((rs) => [newZona, ...rs]);
    setDraft({ nombre: "", hectareas: "", barrios: "", estado: "Activo" });
    setModalOpen(false);
  };

  const toggleTurno = (t) => {
    setAsignDraft((d) => ({
      ...d,
      turnos: d.turnos.includes(t) ? d.turnos.filter((x) => x !== t) : [...d.turnos, t],
    }));
  };

  const submitAsign = () => {
    if (!asignDraft.servicioNombre || !selectedZona) return;
    const exists = asignaciones.find((a) => a.zonaId === selectedZona.id && a.servicioNombre === asignDraft.servicioNombre);
    if (exists) return;
    const horarios = asignDraft.horariosPorDia
      .map((franjas, i) => ({ dia: i + 1, diaNombre: DIAS[i], franjas: franjas.filter((f) => f.inicio && f.fin) }))
      .filter((h) => h.franjas.length > 0);
    setAsignaciones((as) => [...as, { zonaId: selectedZona.id, servicioNombre: asignDraft.servicioNombre, turnos: asignDraft.turnos, horarios }]);
    setAsignDraft({ servicioNombre: "", turnos: [], horariosPorDia: initHorarios() });
    setAsignModalOpen(false);
  };

  const removeAsign = (zonaId, servicioNombre) => {
    setAsignaciones((as) => as.filter((a) => !(a.zonaId === zonaId && a.servicioNombre === servicioNombre)));
  };

  const totalHa = rows.reduce((a, r) => a + r.hectareas, 0);

  return (
    <div className="page">
      <div style={{ display: "flex", alignItems: "flex-end", justifyContent: "space-between", marginBottom: 4 }}>
        <div>
          <h1>Zonas operativas</h1>
          <p className="lede">Zonas geográficas · {totalHa.toLocaleString("es-AR")} ha en total.</p>
        </div>
        <Button icon="plus" onClick={() => setModalOpen(true)}>Agregar zona</Button>
      </div>

      <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
        {rows.map((z) => {
          const serviciosZona = asignaciones.filter((a) => a.zonaId === z.id);
          return (
            <Card key={z.id} compact>
              <div style={{ display: "flex", alignItems: "flex-start", justifyContent: "space-between", gap: 16 }}>
                <div>
                  <div style={{ display: "flex", alignItems: "center", gap: 10, marginBottom: 4 }}>
                    <b style={{ fontSize: 15 }}>{z.nombre}</b>
                    {z.estado === "Activo" ? <Pill kind="green" dot>Activo</Pill> : <Pill kind="gray" dot>Inactivo</Pill>}
                  </div>
                  <div style={{ fontSize: 13, color: "var(--ink-500)" }}>
                    {z.hectareas ? `${z.hectareas.toLocaleString("es-AR")} ha` : "—"}{z.barrios ? ` · ${z.barrios} barrios` : ""}
                  </div>
                </div>
                <div className="actions">
                  <button className="btn btn-ghost btn-sm" title="Editar zona"><Icon name="pencil" size={14} /></button>
                  <button className="btn btn-ghost btn-sm" title="Cambiar estado"><Icon name="power" size={14} /></button>
                </div>
              </div>

              <div style={{ marginTop: 12 }}>
                <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: 6 }}>
                  <span style={{ fontSize: 12, fontWeight: 600, color: "var(--ink-500)", textTransform: "uppercase", letterSpacing: "0.05em" }}>Servicios asignados</span>
                  <button className="btn btn-ghost btn-sm" style={{ fontSize: 12, display: "flex", alignItems: "center", gap: 4 }}
                    onClick={() => { setSelectedZona(z); setAsignModalOpen(true); }}>
                    <Icon name="plus" size={12} /> Agregar
                  </button>
                </div>
                {serviciosZona.length === 0
                  ? <p style={{ fontSize: 13, color: "var(--ink-400)", margin: 0 }}>Sin servicios asignados. Esta zona no aparecerá en el formulario de pesaje.</p>
                  : (
                    <table className="table" style={{ fontSize: 13 }}>
                      <thead>
                        <tr>
                          <th>Servicio</th>
                          <th>Turnos</th>
                          <th>Horarios de recorrido</th>
                          <th style={{ textAlign: "right" }}></th>
                        </tr>
                      </thead>
                      <tbody>
                        {serviciosZona.map((a) => (
                          <tr key={a.servicioNombre}>
                            <td><b>{a.servicioNombre}</b></td>
                            <td>
                              {a.turnos && a.turnos.length > 0
                                ? <div style={{ display: "flex", gap: 4 }}>{a.turnos.map((t) => <Pill key={t} kind="blue">{t}</Pill>)}</div>
                                : <span style={{ color: "var(--ink-400)" }}>Sin turno</span>}
                            </td>
                            <td><HorarioResumen horarios={a.horarios} /></td>
                            <td style={{ textAlign: "right" }}>
                              <button className="btn btn-ghost btn-sm" title="Quitar" onClick={() => removeAsign(z.id, a.servicioNombre)}>
                                <Icon name="x" size={13} />
                              </button>
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  )}
              </div>
            </Card>
          );
        })}
      </div>

      {/* Modal nueva zona */}
      {modalOpen && (
        <Modal title="Nueva zona" onClose={() => setModalOpen(false)}
          footer={<>
            <Button kind="secondary" onClick={() => setModalOpen(false)}>Cancelar</Button>
            <Button kind="primary" onClick={submit} icon="save">Guardar</Button>
          </>}>
          <div className="grid grid-2">
            <Field label="Nombre" style={{ gridColumn: "1 / -1" }}>
              <input className="input" value={draft.nombre} onChange={(e) => setDraft({ ...draft, nombre: e.target.value })} placeholder="Ej. Zona Costanera" />
            </Field>
            <Field label="Superficie (hectáreas)"><input className="input num" value={draft.hectareas} onChange={(e) => setDraft({ ...draft, hectareas: e.target.value })} placeholder="0" /></Field>
            <Field label="Cantidad de barrios"><input className="input num" value={draft.barrios} onChange={(e) => setDraft({ ...draft, barrios: e.target.value })} placeholder="0" /></Field>
          </div>
        </Modal>
      )}

      {/* Modal asignar servicio */}
      {asignModalOpen && selectedZona && (
        <Modal title={`Asignar servicio — ${selectedZona.nombre}`} onClose={() => setAsignModalOpen(false)}
          footer={<>
            <Button kind="secondary" onClick={() => setAsignModalOpen(false)}>Cancelar</Button>
            <Button kind="primary" onClick={submitAsign} icon="save">Guardar</Button>
          </>}>
          <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
            <Field label="Tipo de servicio">
              <select className="select" value={asignDraft.servicioNombre} onChange={(e) => setAsignDraft({ ...asignDraft, servicioNombre: e.target.value })}>
                <option value="">Seleccionar…</option>
                {SERVICIOS.filter((s) => !asignaciones.find((a) => a.zonaId === selectedZona.id && a.servicioNombre === s))
                  .map((s) => <option key={s}>{s}</option>)}
              </select>
            </Field>

            <Field label="Turnos disponibles"
              hint="Dejá en blanco si este servicio en esta zona no requiere turno.">
              <div style={{ display: "flex", gap: 12 }}>
                {TURNOS_OPCIONES.map((t) => (
                  <label key={t} style={{ display: "flex", alignItems: "center", gap: 6, fontSize: 14, cursor: "pointer" }}>
                    <input type="checkbox" checked={asignDraft.turnos.includes(t)} onChange={() => toggleTurno(t)} />
                    {t}
                  </label>
                ))}
              </div>
            </Field>

            <Field label="Horarios de recorrido"
              hint="Optativo. Podés agregar varias franjas por día. Las franjas pueden cruzar medianoche (ej: 20:00–02:00).">
              <HorarioEditor
                horariosPorDia={asignDraft.horariosPorDia}
                onChange={(next) => setAsignDraft((d) => ({ ...d, horariosPorDia: next }))}
              />
            </Field>
          </div>
        </Modal>
      )}
    </div>
  );
}

window.AbmZonas = AbmZonas;
