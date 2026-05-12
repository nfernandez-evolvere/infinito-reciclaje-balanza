/* global React, Card, Pill, Button, Icon,
   EditPesajeModal, EgresoModal, LogModal,
   fmtKg, fmtN, fmtT */
const { useState } = React;

function Historial({ pesajes, log, actor, onEdit, onEgreso }) {
  const [editing, setEditing] = useState(null);
  const [egresoFor, setEgresoFor] = useState(null);
  const [logFor, setLogFor] = useState(null);

  // Operator only sees own shift / own user. For the prototype we filter by current user.
  const mine = pesajes.filter((p) => p.operador === actor);
  const sum = mine.reduce((a, p) => ({ count: a.count + 1, t: a.t + p.neto / 1000 }), { count: 0, t: 0 });
  const enPredio = mine.filter((p) => p.estado === "En predio").length;

  return (
    <div className="page" style={{ maxWidth: 1180 }}>
      <h1>Historial del turno</h1>
      <p className="lede">Pesajes registrados en esta sesión. Podés corregir errores y marcar el egreso del camión.</p>

      <div className="section">
        <div className="grid grid-4">
          <div className="kpi sm"><div className="label">Pesajes</div><div className="value">{sum.count}</div></div>
          <div className="kpi sm"><div className="label">Neto total</div><div className="value">{sum.t.toLocaleString("es-AR", { minimumFractionDigits: 1, maximumFractionDigits: 1 })}<span className="unit">t</span></div></div>
          <div className="kpi sm"><div className="label">Promedio por viaje</div><div className="value">{sum.count ? (sum.t / sum.count).toLocaleString("es-AR", { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : "—"}<span className="unit">t</span></div></div>
          <div className="kpi sm"><div className="label">En predio</div><div className="value">{enPredio}</div></div>
        </div>
      </div>

      <Card compact>
        <div style={{ overflowX: "auto" }}>
        <table className="table">
          <thead>
            <tr>
              <th>Entrada</th>
              <th>Salida</th>
              <th>Estado</th>
              <th>Patente</th>
              <th>Servicio</th>
              <th>Zona</th>
              <th className="num">Bruto</th>
              <th className="num">Tara</th>
              <th className="num">Neto</th>
              <th style={{ textAlign: "right" }}>Acciones</th>
            </tr>
          </thead>
          <tbody>
            {mine.slice(0, 20).map((p) => {
              const hasLog = log.some((l) => l.pesajeId === p.id);
              return (
                <tr key={p.id}>
                  <td className="num">{p.horaEntrada}</td>
                  <td className="num muted">{p.horaSalida || "—"}</td>
                  <td>
                    {p.estado === "En predio"
                      ? <Pill kind="orange" dot>En predio</Pill>
                      : <Pill kind="green" dot>Cerrado</Pill>}
                    {p.editado && <Pill kind="blue">Editado</Pill>}
                  </td>
                  <td><b>{p.patente}</b></td>
                  <td>{p.servicio}</td>
                  <td><Pill kind="gray">{p.zona}</Pill></td>
                  <td className="num">{fmtN(p.bruto)}</td>
                  <td className="num">{fmtN(p.tara)}</td>
                  <td className="num"><b>{fmtN(p.neto)}</b></td>
                  <td style={{ textAlign: "right" }}>
                    <div className="actions">
                      {p.estado === "En predio" && (
                        <button className="btn btn-secondary btn-sm" onClick={() => setEgresoFor(p)} title="Marcar egreso">
                          <Icon name="log-out" size={14} /> Egreso
                        </button>
                      )}
                      <button className="btn btn-ghost btn-sm" title="Editar" onClick={() => setEditing(p)}><Icon name="pencil" size={14} /></button>
                      <button className="btn btn-ghost btn-sm" title="Ver historial" onClick={() => setLogFor(p)} style={{ opacity: hasLog ? 1 : 0.4 }}><Icon name="history" size={14} /></button>
                    </div>
                  </td>
                </tr>
              );
            })}
            {mine.length === 0 && (
              <tr><td colSpan={10} style={{ textAlign: "center", color: "var(--ink-500)", padding: 40 }}>Sin pesajes en este turno todavía.</td></tr>
            )}
          </tbody>
        </table>
        </div>
      </Card>

      {editing && (
        <EditPesajeModal
          pesaje={editing}
          actorRole="operator"
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

window.Historial = Historial;
