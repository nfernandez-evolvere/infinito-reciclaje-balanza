/* global React, Button, Card, Pill, Field, Icon,
   EditPesajeModal, EgresoModal, LogModal,
   fmtN, useAppContext */
const { useState, useMemo } = React;

function PesajesAdmin({ pesajes, log, actor, onEdit, onEgreso }) {
  const { servicioNames: SERVICIOS, zonaNames: ORIGENES, usuarios: USUARIOS } = useAppContext();
  const [query, setQuery] = useState("");
  const [fOrigen, setFOrigen] = useState("Todas");
  const [fServicio, setFServicio] = useState("Todos");
  const [fOperador, setFOperador] = useState("Todos");
  const [fEstado, setFEstado] = useState("Todos");
  const [editing, setEditing] = useState(null);
  const [egresoFor, setEgresoFor] = useState(null);
  const [logFor, setLogFor]   = useState(null);

  const filtered = useMemo(() => pesajes.filter((p) => {
    const q = query.trim().toLowerCase();
    if (q && !(p.patente.toLowerCase().includes(q) || String(p.id).includes(q))) return false;
    if (fOrigen   !== "Todas" && p.origen !== fOrigen)     return false;
    if (fServicio !== "Todos" && p.servicio !== fServicio) return false;
    if (fOperador !== "Todos" && p.operador !== fOperador) return false;
    if (fEstado   !== "Todos" && p.estado   !== fEstado)   return false;
    return true;
  }), [pesajes, query, fOrigen, fServicio, fOperador, fEstado]);

  const totalT = filtered.reduce((a, p) => a + p.neto / 1000, 0);

  return (
    <div className="page">
      <div style={{ display: "flex", alignItems: "flex-end", justifyContent: "space-between", marginBottom: 4 }}>
        <div>
          <h1>Pesajes</h1>
          <p className="lede">Registro completo · {filtered.length} de {pesajes.length} · {totalT.toLocaleString("es-AR", { minimumFractionDigits: 1, maximumFractionDigits: 1 })} t netas en la vista.</p>
        </div>
        <Button kind="secondary" icon="file-down">Exportar Excel</Button>
      </div>

      <Card compact>
        <div className="grid" style={{ gap: 12, gridTemplateColumns: "1.5fr 1fr 1fr 1fr 1fr" }}>
          <Field label="Buscar">
            <div style={{ position: "relative" }}>
              <input className="input" placeholder="Patente o ID" value={query} onChange={(e) => setQuery(e.target.value)} style={{ paddingLeft: 36 }} />
              <span style={{ position: "absolute", left: 12, top: 12, color: "var(--ink-500)" }}><Icon name="search" size={18} /></span>
            </div>
          </Field>
          <Field label="Estado">
            <select className="select" value={fEstado} onChange={(e) => setFEstado(e.target.value)}>
              <option>Todos</option><option>En predio</option><option>Cerrado</option>
            </select>
          </Field>
          <Field label="Origen">
            <select className="select" value={fOrigen} onChange={(e) => setFOrigen(e.target.value)}>
              <option>Todas</option>{ORIGENES.map((z) => <option key={z}>{z}</option>)}
            </select>
          </Field>
          <Field label="Servicio">
            <select className="select" value={fServicio} onChange={(e) => setFServicio(e.target.value)}>
              <option>Todos</option>{SERVICIOS.map((s) => <option key={s}>{s}</option>)}
            </select>
          </Field>
          <Field label="Operador">
            <select className="select" value={fOperador} onChange={(e) => setFOperador(e.target.value)}>
              <option>Todos</option>{USUARIOS.filter((u) => u.rol === "Operador").map((u) => <option key={u.usuario} value={u.usuario}>{u.nombre}</option>)}
            </select>
          </Field>
        </div>
      </Card>

      <Card compact style={{ marginTop: 16 }}>
        <div style={{ overflowX: "auto" }}>
        <table className="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Entrada</th>
              <th>Salida</th>
              <th>Estado</th>
              <th>Patente</th>
              <th>Servicio</th>
              <th>Origen</th>
              <th className="num">Bruto</th>
              <th className="num">Tara</th>
              <th className="num">Neto</th>
              <th>Operador</th>
              <th style={{ textAlign: "right" }}>Acciones</th>
            </tr>
          </thead>
          <tbody>
            {filtered.map((p) => {
              const op = USUARIOS.find((u) => u.usuario === p.operador);
              const hasLog = log.some((l) => l.pesajeId === p.id);
              return (
                <tr key={p.id}>
                  <td className="muted">#{p.id}</td>
                  <td>{p.horaEntrada}</td>
                  <td className="muted">{p.horaSalida || "—"}</td>
                  <td>
                    {p.estado === "En predio"
                      ? <Pill kind="orange" dot>En predio</Pill>
                      : <Pill kind="green" dot>Cerrado</Pill>}
                    {p.editado && <Pill kind="blue" >Editado</Pill>}
                  </td>
                  <td><b>{p.patente}</b></td>
                  <td>{p.servicio}</td>
                  <td><Pill kind="gray">{p.origen}</Pill></td>
                  <td className="num">{fmtN(p.bruto)}</td>
                  <td className="num">{fmtN(p.tara)}</td>
                  <td className="num"><b>{fmtN(p.neto)}</b></td>
                  <td>{op?.nombre || p.operador}</td>
                  <td style={{ textAlign: "right" }}>
                    <div className="actions">
                      {p.estado === "En predio" && (
                        <button className="btn btn-ghost btn-sm" title="Marcar egreso" onClick={() => setEgresoFor(p)}><Icon name="log-out" size={14} /></button>
                      )}
                      <button className="btn btn-ghost btn-sm" title="Editar" onClick={() => setEditing(p)}><Icon name="pencil" size={14} /></button>
                      <button className="btn btn-ghost btn-sm" title="Ver historial" onClick={() => setLogFor(p)} style={{ opacity: hasLog ? 1 : 0.4 }}><Icon name="history" size={14} /></button>
                    </div>
                  </td>
                </tr>
              );
            })}
            {filtered.length === 0 && (
              <tr><td colSpan={12} style={{ textAlign: "center", padding: 32, color: "var(--ink-500)" }}>Ningún pesaje coincide con los filtros.</td></tr>
            )}
          </tbody>
        </table>
        </div>
      </Card>

      {editing && (
        <EditPesajeModal
          pesaje={editing}
          actorRole="admin"
          onClose={() => setEditing(null)}
          onSave={(patch, motivo) => { onEdit(editing.id, patch, motivo); setEditing(null); }} />
      )}
      {egresoFor && (
        <EgresoModal
          pesaje={egresoFor}
          onClose={() => setEgresoFor(null)}
          onConfirm={(payload) => { onEgreso(egresoFor.id, payload); setEgresoFor(null); }} />
      )}
      {logFor && (
        <LogModal pesaje={logFor} entries={log.filter((l) => l.pesajeId === logFor.id)} onClose={() => setLogFor(null)} />
      )}
    </div>
  );
}

window.PesajesAdmin = PesajesAdmin;
