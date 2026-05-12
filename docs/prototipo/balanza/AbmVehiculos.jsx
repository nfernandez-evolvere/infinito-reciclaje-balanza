/* global React, Button, Card, Pill, Modal, Field, Icon,
   VEHICLES, VEHICLE_TYPES, fmtKg */
const { useState } = React;

function AbmVehiculos() {
  const [rows, setRows] = useState(VEHICLES);
  const [query, setQuery] = useState("");
  const [modalOpen, setModalOpen] = useState(false);
  const [draft, setDraft] = useState({ patente: "", interno: "", tipo: "Compactador", tara: "", titular: "Municipal", capacidad: "", observaciones: "", estado: "Activo" });

  const filtered = rows.filter((v) => {
    const q = query.trim().toLowerCase();
    if (!q) return true;
    return v.patente.toLowerCase().includes(q) || v.interno.includes(q) || v.tipo.toLowerCase().includes(q);
  });

  const submit = () => {
    if (!draft.patente.trim() || !draft.tara) return;
    setRows((rs) => [{
      id: Date.now(),
      patente: draft.patente.toUpperCase(),
      interno: draft.interno || "—",
      tipo: draft.tipo,
      tara: parseInt(draft.tara, 10),
      titular: draft.titular,
      estado: draft.estado,
    }, ...rs]);
    setDraft({ patente: "", interno: "", tipo: "Compactador", tara: "", titular: "Municipal", capacidad: "", observaciones: "", estado: "Activo" });
    setModalOpen(false);
  };

  return (
    <div className="page">
      <div style={{ display: "flex", alignItems: "flex-end", justifyContent: "space-between", marginBottom: 4 }}>
        <div>
          <h1>Padrón de vehículos</h1>
          <p className="lede">Vehículos habilitados para ingresar al predio.</p>
        </div>
        <Button icon="plus" onClick={() => setModalOpen(true)}>Agregar vehículo</Button>
      </div>

      <Card compact>
        <div style={{ display: "flex", gap: 12, alignItems: "center", marginBottom: 16 }}>
          <div className="field" style={{ flex: 1, position: "relative" }}>
            <input className="input" placeholder="Buscar por patente, número interno o tipo" value={query} onChange={(e) => setQuery(e.target.value)} style={{ paddingLeft: 36 }} />
            <span style={{ position: "absolute", left: 12, top: 12, color: "var(--ink-500)" }}><Icon name="search" size={18} /></span>
          </div>
          <span className="muted body-sm">{filtered.length} de {rows.length}</span>
        </div>

        <div style={{ overflowX: "auto" }}>
        <table className="table">
          <thead>
            <tr>
              <th>Patente</th>
              <th className="num">N° interno</th>
              <th>Tipo</th>
              <th className="num">Tara</th>
              <th>Titular</th>
              <th>Estado</th>
              <th style={{ textAlign: "right" }}>Acciones</th>
            </tr>
          </thead>
          <tbody>
            {filtered.map((v) => (
              <tr key={v.id}>
                <td><b>{v.patente}</b></td>
                <td className="num">{v.interno}</td>
                <td>{v.tipo}</td>
                <td className="num">{fmtKg(v.tara)}</td>
                <td>{v.titular === "Municipal" ? "Municipalidad" : v.titular}</td>
                <td>
                  {v.estado === "Activo"
                    ? <Pill kind="green" dot>Activo</Pill>
                    : <Pill kind="gray" dot>Inactivo</Pill>}
                </td>
                <td style={{ textAlign: "right" }}>
                  <div className="actions">
                    <button className="btn btn-ghost btn-sm" title="Editar"><Icon name="pencil" size={14} /></button>
                    <button className="btn btn-ghost btn-sm" title={v.estado === "Activo" ? "Desactivar" : "Activar"}><Icon name={v.estado === "Activo" ? "power-off" : "power"} size={14} /></button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
        </div>
      </Card>

      {modalOpen && (
        <Modal title="Nuevo vehículo" onClose={() => setModalOpen(false)}
          footer={<>
            <Button kind="secondary" onClick={() => setModalOpen(false)}>Cancelar</Button>
            <Button kind="primary" onClick={submit} icon="save">Guardar</Button>
          </>}>
          <div className="grid grid-2">
            <Field label="Patente"><input className="input" value={draft.patente} onChange={(e) => setDraft({ ...draft, patente: e.target.value })} placeholder="ABC-123" /></Field>
            <Field label="N° interno"><input className="input" value={draft.interno} onChange={(e) => setDraft({ ...draft, interno: e.target.value })} placeholder="00" /></Field>
            <Field label="Tipo">
              <select className="select" value={draft.tipo} onChange={(e) => setDraft({ ...draft, tipo: e.target.value })}>
                {Object.keys(VEHICLE_TYPES).map((t) => <option key={t}>{t}</option>)}
              </select>
            </Field>
            <Field label="Tara (kg)"><input className="input num" value={draft.tara} onChange={(e) => setDraft({ ...draft, tara: e.target.value })} placeholder="8500" /></Field>
            <Field label="Titular">
              <select className="select" value={draft.titular} onChange={(e) => setDraft({ ...draft, titular: e.target.value })}>
                <option>Municipal</option><option>Particular</option>
              </select>
            </Field>
            <Field label="Capacidad (m³)"><input className="input num" value={draft.capacidad} onChange={(e) => setDraft({ ...draft, capacidad: e.target.value })} placeholder="opcional" /></Field>
            <Field label="Observaciones" style={{ gridColumn: "1 / -1" }}>
              <input className="input" value={draft.observaciones} onChange={(e) => setDraft({ ...draft, observaciones: e.target.value })} placeholder="opcional" />
            </Field>
            <Field label="Estado" style={{ gridColumn: "1 / -1" }}>
              <div style={{ display: "inline-flex", gap: 8 }}>
                <button type="button"
                  className={"btn btn-sm " + (draft.estado === "Activo" ? "btn-primary" : "btn-secondary")}
                  onClick={() => setDraft({ ...draft, estado: "Activo" })}>Activo</button>
                <button type="button"
                  className={"btn btn-sm " + (draft.estado === "Inactivo" ? "btn-primary" : "btn-secondary")}
                  onClick={() => setDraft({ ...draft, estado: "Inactivo" })}>Inactivo</button>
              </div>
            </Field>
          </div>
        </Modal>
      )}
    </div>
  );
}

window.AbmVehiculos = AbmVehiculos;
