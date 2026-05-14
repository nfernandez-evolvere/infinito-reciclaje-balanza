/* global React, Button, Card, Pill, Modal, Field, Icon, useAppContext */
const { useState } = React;

const DIAS = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo"];
const DIAS_CORTO = ["Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"];

const initHorarios = () => DIAS.map(() => []);

function HorarioEditorCompact({ horariosPorDia, onChange }) {
  const toggleDia = (i) => {
    const next = horariosPorDia.map((f, idx) =>
      idx === i ? (f.length === 0 ? [{ inicio: "", fin: "" }] : []) : f
    );
    onChange(next);
  };
  const updateFranja = (diaIdx, franjaIdx, field, value) => {
    const next = horariosPorDia.map((f, i) =>
      i === diaIdx ? f.map((fr, j) => j === franjaIdx ? { ...fr, [field]: value } : fr) : f
    );
    onChange(next);
  };
  const addFranja = (diaIdx) => {
    const next = horariosPorDia.map((f, i) => i === diaIdx ? [...f, { inicio: "", fin: "" }] : f);
    onChange(next);
  };
  const removeFranja = (diaIdx, franjaIdx) => {
    const next = horariosPorDia.map((f, i) =>
      i === diaIdx ? f.filter((_, j) => j !== franjaIdx) : f
    );
    onChange(next);
  };

  const activeDias = horariosPorDia.filter((f) => f.length > 0);

  return (
    <div style={{ display: "flex", flexDirection: "column", gap: 10 }}>
      {/* Selector de días */}
      <div style={{ display: "flex", gap: 5 }}>
        {DIAS_CORTO.map((d, i) => {
          const active = horariosPorDia[i].length > 0;
          return (
            <button key={i} type="button" onClick={() => toggleDia(i)}
              style={{
                padding: "4px 9px", fontSize: 12, fontWeight: 600, borderRadius: 6, cursor: "pointer",
                border: "1px solid " + (active ? "var(--primary)" : "var(--border)"),
                background: active ? "var(--primary)" : "transparent",
                color: active ? "white" : "var(--ink-500)",
                transition: "all 120ms",
              }}>
              {d}
            </button>
          );
        })}
      </div>

      {/* Filas de horario para días activos */}
      {activeDias.length === 0 && (
        <p style={{ fontSize: 13, color: "var(--ink-400)", margin: 0 }}>Seleccioná los días de operación.</p>
      )}
      {DIAS.map((diaNombre, diaIdx) => {
        const franjas = horariosPorDia[diaIdx];
        if (franjas.length === 0) return null;
        return (
          <div key={diaIdx} style={{ display: "flex", flexDirection: "column", gap: 4 }}>
            <span style={{ fontSize: 12, fontWeight: 600, color: "var(--ink-600)" }}>{diaNombre}</span>
            {franjas.map((fr, franjaIdx) => (
              <div key={franjaIdx} style={{ display: "flex", alignItems: "center", gap: 6 }}>
                <input className="input" type="time" value={fr.inicio}
                  onChange={(e) => updateFranja(diaIdx, franjaIdx, "inicio", e.target.value)}
                  style={{ width: 88, fontSize: 13 }} />
                <span style={{ color: "var(--ink-400)", fontSize: 13 }}>–</span>
                <input className="input" type="time" value={fr.fin}
                  onChange={(e) => updateFranja(diaIdx, franjaIdx, "fin", e.target.value)}
                  style={{ width: 88, fontSize: 13 }} />
                {franjaIdx === 0
                  ? <button type="button" className="btn btn-ghost btn-sm"
                      style={{ fontSize: 12, color: "var(--primary)" }}
                      onClick={() => addFranja(diaIdx)}>
                      <Icon name="plus" size={12} /> franja
                    </button>
                  : <button type="button" className="btn btn-ghost btn-sm"
                      onClick={() => removeFranja(diaIdx, franjaIdx)} title="Quitar">
                      <Icon name="x" size={12} />
                    </button>
                }
              </div>
            ))}
          </div>
        );
      })}
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
  const { zonas: rows, setZonas: setRows, zonaServicios: asignaciones, setZonaServicios: setAsignaciones, servicioNames: SERVICIOS } = useAppContext();
  const [modalOpen, setModalOpen] = useState(false);
  const [editZona, setEditZona] = useState(null);             // zona object being edited
  const [editDraft, setEditDraft] = useState(null);
  const [asignModalOpen, setAsignModalOpen] = useState(false);
  const [editAsign, setEditAsign] = useState(null);            // asignacion existente en edición
  const [horariosFor, setHorariosFor] = useState(null);
  const [confirmQuitar, setConfirmQuitar] = useState(null);
  const [confirmToggle, setConfirmToggle] = useState(null);
  const [selectedZona, setSelectedZona] = useState(null);
  const [draft, setDraft] = useState({ nombre: "", hectareas: "", barrios: "", estado: "Activo" });
  const [asignDraft, setAsignDraft] = useState({ servicioNombre: "", turnosEnabled: false, turnos: [], horariosPorDia: initHorarios() });

  const reconstructHorarios = (horarios) => {
    const h = initHorarios();
    (horarios || []).forEach(({ dia, franjas }) => { h[dia - 1] = franjas; });
    return h;
  };

  const openNuevaAsign = (zona) => {
    setEditAsign(null);
    setSelectedZona(zona);
    setAsignDraft({ servicioNombre: "", turnosEnabled: false, turnos: [], horariosPorDia: initHorarios() });
    setAsignModalOpen(true);
  };

  const openEditAsign = (zona, asign) => {
    setEditAsign(asign);
    setSelectedZona(zona);
    setAsignDraft({
      servicioNombre: asign.servicioNombre,
      turnosEnabled: asign.turnos.length > 0,
      turnos: asign.turnos,
      horariosPorDia: reconstructHorarios(asign.horarios),
    });
    setAsignModalOpen(true);
  };

  const openEdit = (z) => {
    setEditZona(z);
    setEditDraft({ nombre: z.nombre, hectareas: String(z.hectareas), barrios: String(z.barrios) });
  };

  const submitEdit = () => {
    if (!editDraft.nombre.trim()) return;
    setRows((rs) => rs.map((r) => r.id === editZona.id
      ? { ...r, nombre: editDraft.nombre, hectareas: parseFloat(editDraft.hectareas) || 0, barrios: parseInt(editDraft.barrios, 10) || 0 }
      : r
    ));
    setEditZona(null);
    setEditDraft(null);
  };

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
    const horarios = asignDraft.horariosPorDia
      .map((franjas, i) => ({ dia: i + 1, diaNombre: DIAS[i], franjas: franjas.filter((f) => f.inicio && f.fin) }))
      .filter((h) => h.franjas.length > 0);
    const turnos = asignDraft.turnosEnabled ? asignDraft.turnos : [];
    const updated = { zonaId: selectedZona.id, servicioNombre: asignDraft.servicioNombre, turnos, horarios };
    if (editAsign) {
      setAsignaciones((as) => as.map((a) =>
        a.zonaId === selectedZona.id && a.servicioNombre === editAsign.servicioNombre ? updated : a
      ));
    } else {
      const exists = asignaciones.find((a) => a.zonaId === selectedZona.id && a.servicioNombre === asignDraft.servicioNombre);
      if (exists) return;
      setAsignaciones((as) => [...as, updated]);
    }
    setEditAsign(null);
    setAsignDraft({ servicioNombre: "", turnosEnabled: false, turnos: [], horariosPorDia: initHorarios() });
    setAsignModalOpen(false);
  };

  const removeAsign = (zonaId, servicioNombre) => {
    setAsignaciones((as) => as.filter((a) => !(a.zonaId === zonaId && a.servicioNombre === servicioNombre)));
    setConfirmQuitar(null);
  };

  const toggleEstado = (zonaId) => {
    setRows((rs) => rs.map((r) => r.id === zonaId ? { ...r, estado: r.estado === "Activo" ? "Inactivo" : "Activo" } : r));
    setConfirmToggle(null);
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
                  <button className="btn btn-ghost btn-sm" title="Editar zona" onClick={() => openEdit(z)}><Icon name="pencil" size={14} /> Editar</button>
                  <button className="btn btn-ghost btn-sm" title={z.estado === "Activo" ? "Desactivar" : "Activar"}
                    onClick={() => setConfirmToggle(z)}>
                    <Icon name="power" size={14} />
                    {z.estado === "Activo" ? "Desactivar" : "Activar"}
                  </button>
                </div>
              </div>

              <div style={{ marginTop: 12 }}>
                <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: 6 }}>
                  <span style={{ fontSize: 12, fontWeight: 600, color: "var(--ink-500)", textTransform: "uppercase", letterSpacing: "0.05em" }}>Servicios asignados</span>
                  <button className="btn btn-ghost btn-sm" style={{ fontSize: 12, display: "flex", alignItems: "center", gap: 4 }}
                    onClick={() => openNuevaAsign(z)}>
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
                            <td style={{ textAlign: "right" }}>
                              <div className="actions">
                                <button className="btn btn-ghost btn-sm"
                                  title="Ver horarios de recorrido"
                                  onClick={() => setHorariosFor({ zonaNombre: z.nombre, servicioNombre: a.servicioNombre, horarios: a.horarios })}
                                  style={{ opacity: a.horarios && a.horarios.length > 0 ? 1 : 0.35 }}>
                                  <Icon name="clock" size={13} /> Horarios
                                </button>
                                <button className="btn btn-ghost btn-sm" title="Editar asignación"
                                  onClick={() => openEditAsign(z, a)}>
                                  <Icon name="pencil" size={13} /> Editar
                                </button>
                                <button className="btn btn-ghost btn-sm" title="Quitar servicio"
                                  onClick={() => setConfirmQuitar({ zonaId: z.id, zonaNombre: z.nombre, servicioNombre: a.servicioNombre })}>
                                  <Icon name="x" size={13} /> Quitar
                                </button>
                              </div>
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

      {/* Modal editar zona */}
      {editZona && editDraft && (
        <Modal title={`Editar zona — ${editZona.nombre}`} onClose={() => { setEditZona(null); setEditDraft(null); }}
          footer={<>
            <Button kind="secondary" onClick={() => { setEditZona(null); setEditDraft(null); }}>Cancelar</Button>
            <Button kind="primary" onClick={submitEdit} icon="save">Guardar cambios</Button>
          </>}>
          <div className="grid grid-2">
            <Field label="Nombre" style={{ gridColumn: "1 / -1" }}>
              <input className="input" value={editDraft.nombre} onChange={(e) => setEditDraft({ ...editDraft, nombre: e.target.value })} />
            </Field>
            <Field label="Superficie (hectáreas)"><input className="input num" value={editDraft.hectareas} onChange={(e) => setEditDraft({ ...editDraft, hectareas: e.target.value })} placeholder="0" /></Field>
            <Field label="Cantidad de barrios"><input className="input num" value={editDraft.barrios} onChange={(e) => setEditDraft({ ...editDraft, barrios: e.target.value })} placeholder="0" /></Field>
          </div>
        </Modal>
      )}

      {/* Modal asignar servicio */}
      {asignModalOpen && selectedZona && (
        <Modal
          title={editAsign ? `Editar servicio — ${editAsign.servicioNombre}` : `Asignar servicio — ${selectedZona.nombre}`}
          onClose={() => { setAsignModalOpen(false); setEditAsign(null); }}
          footer={<>
            <Button kind="secondary" onClick={() => { setAsignModalOpen(false); setEditAsign(null); }}>Cancelar</Button>
            <Button kind="primary" onClick={submitAsign} icon="save" disabled={!asignDraft.servicioNombre}>Guardar</Button>
          </>}>
          <div style={{ display: "flex", flexDirection: "column", gap: 20 }}>
            <Field label="Tipo de servicio">
              {editAsign
                ? <div style={{ padding: "8px 12px", background: "var(--bg)", borderRadius: 6, fontSize: 14, fontWeight: 600, color: "var(--ink-700)", border: "1px solid var(--border)" }}>{editAsign.servicioNombre}</div>
                : <select className="select" value={asignDraft.servicioNombre} onChange={(e) => setAsignDraft({ ...asignDraft, servicioNombre: e.target.value })}>
                    <option value="">Seleccionar…</option>
                    {SERVICIOS.filter((s) => !asignaciones.find((a) => a.zonaId === selectedZona.id && a.servicioNombre === s))
                      .map((s) => <option key={s}>{s}</option>)}
                  </select>
              }
            </Field>

            <div>
              <label style={{ display: "flex", alignItems: "center", gap: 10, cursor: "pointer", userSelect: "none" }}>
                <span style={{ position: "relative", display: "inline-block", width: 36, height: 20, flexShrink: 0 }}>
                  <input type="checkbox" checked={asignDraft.turnosEnabled}
                    onChange={(e) => setAsignDraft((d) => ({ ...d, turnosEnabled: e.target.checked, turnos: [] }))}
                    style={{ opacity: 0, width: 0, height: 0, position: "absolute" }} />
                  <span style={{
                    position: "absolute", inset: 0, borderRadius: 999, transition: "background 200ms",
                    background: asignDraft.turnosEnabled ? "var(--primary)" : "var(--ink-300)",
                  }} />
                  <span style={{
                    position: "absolute", top: 3, left: asignDraft.turnosEnabled ? 19 : 3,
                    width: 14, height: 14, borderRadius: 999, background: "white",
                    boxShadow: "0 1px 3px rgba(0,0,0,.2)", transition: "left 200ms",
                  }} />
                </span>
                <span style={{ fontSize: 13, fontWeight: 500, color: "var(--ink-700)" }}>Opera con turnos</span>
              </label>

              {asignDraft.turnosEnabled && (
                <div style={{ display: "flex", gap: 8, marginTop: 12, marginLeft: 46 }}>
                  {["Diurna", "Nocturna"].map((t) => {
                    const active = asignDraft.turnos.includes(t);
                    return (
                      <button key={t} type="button"
                        onClick={() => toggleTurno(t)}
                        style={{
                          padding: "5px 16px", fontSize: 13, borderRadius: 6, cursor: "pointer",
                          border: "1px solid " + (active ? "var(--primary)" : "var(--border)"),
                          background: active ? "var(--primary)" : "transparent",
                          color: active ? "white" : "var(--ink-600)",
                          fontWeight: active ? 600 : 400,
                          transition: "all 120ms",
                        }}>
                        {t}
                      </button>
                    );
                  })}
                </div>
              )}
            </div>

            <div>
              <div style={{ fontSize: 13, fontWeight: 500, color: "var(--ink-700)", marginBottom: 2 }}>Horarios de recorrido</div>
              <div style={{ fontSize: 12, color: "var(--ink-400)", marginBottom: 10 }}>Optativo. Seleccioná los días y cargá las franjas horarias.</div>
              <HorarioEditorCompact
                horariosPorDia={asignDraft.horariosPorDia}
                onChange={(next) => setAsignDraft((d) => ({ ...d, horariosPorDia: next }))}
              />
            </div>
          </div>
        </Modal>
      )}

      {/* Modal confirmar quitar servicio */}
      {confirmQuitar && (
        <Modal title="Quitar servicio asignado" onClose={() => setConfirmQuitar(null)} maxWidth={440}
          footer={<>
            <Button kind="secondary" onClick={() => setConfirmQuitar(null)}>Cancelar</Button>
            <Button kind="danger" icon="x" onClick={() => removeAsign(confirmQuitar.zonaId, confirmQuitar.servicioNombre)}>Quitar</Button>
          </>}>
          <p>¿Querés quitar <b>{confirmQuitar.servicioNombre}</b> de la zona <b>{confirmQuitar.zonaNombre}</b>?</p>
          <p style={{ fontSize: 13, color: "var(--ink-500)", marginTop: 6 }}>El servicio deja de aparecer como opción en nuevos pesajes para esta zona.</p>
        </Modal>
      )}

      {/* Modal confirmar activar/desactivar zona */}
      {confirmToggle && (
        <Modal title={confirmToggle.estado === "Activo" ? "Desactivar zona" : "Activar zona"} onClose={() => setConfirmToggle(null)} maxWidth={440}
          footer={<>
            <Button kind="secondary" onClick={() => setConfirmToggle(null)}>Cancelar</Button>
            <Button kind={confirmToggle.estado === "Activo" ? "danger" : "primary"}
              icon={confirmToggle.estado === "Activo" ? "power" : "power"}
              onClick={() => toggleEstado(confirmToggle.id)}>
              {confirmToggle.estado === "Activo" ? "Desactivar" : "Activar"}
            </Button>
          </>}>
          {confirmToggle.estado === "Activo"
            ? <><p>¿Desactivar la zona <b>{confirmToggle.nombre}</b>?</p>
                <p style={{ fontSize: 13, color: "var(--ink-500)", marginTop: 6 }}>La zona deja de aparecer en el formulario de pesaje. Los pesajes históricos no se ven afectados.</p></>
            : <><p>¿Activar la zona <b>{confirmToggle.nombre}</b>?</p>
                <p style={{ fontSize: 13, color: "var(--ink-500)", marginTop: 6 }}>La zona vuelve a estar disponible en el formulario de pesaje.</p></>
          }
        </Modal>
      )}

      {/* Modal horarios de recorrido */}
      {horariosFor && (
        <Modal
          title={`Horarios — ${horariosFor.servicioNombre}`}
          onClose={() => setHorariosFor(null)}
          footer={<Button kind="secondary" onClick={() => setHorariosFor(null)}>Cerrar</Button>}>
          <p style={{ fontSize: 13, color: "var(--ink-500)", marginBottom: 12 }}>
            Zona <b>{horariosFor.zonaNombre}</b>
          </p>
          {horariosFor.horarios && horariosFor.horarios.length > 0
            ? (
              <div style={{ display: "flex", flexDirection: "column", gap: 6 }}>
                {horariosFor.horarios.map((h) => (
                  <div key={h.dia} style={{ display: "flex", alignItems: "baseline", gap: 10, fontSize: 13 }}>
                    <span style={{ width: 80, fontWeight: 600, color: "var(--ink-700)", flexShrink: 0 }}>{h.diaNombre}</span>
                    <span style={{ color: "var(--ink-500)" }}>
                      {h.franjas.map((f) => `${f.inicio}–${f.fin}`).join(" · ")}
                    </span>
                  </div>
                ))}
              </div>
            )
            : <p style={{ fontSize: 13, color: "var(--ink-400)" }}>Sin horarios configurados para este servicio.</p>
          }
        </Modal>
      )}
    </div>
  );
}

window.AbmZonas = AbmZonas;
