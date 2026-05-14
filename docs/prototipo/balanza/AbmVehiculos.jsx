/* global React, Button, Card, Pill, Modal, Field, Icon, fmtKg, useAppContext */
const { useState } = React;

const BLANK = { patente: "", interno: "", tipo: "Compactador", tara: "", titular: "Municipal", capacidad: "", observaciones: "", estado: "Activo" };

function AbmVehiculos() {
  const { vehiculos: rows, setVehiculos: setRows, tiposVehiculo } = useAppContext();
  const VEHICLE_TYPES = Object.fromEntries(tiposVehiculo.map((t) => [t.nombre, t]));
  const [query, setQuery] = useState("");
  const [modalOpen, setModalOpen] = useState(false);
  const [editVehiculo, setEditVehiculo] = useState(null);
  const [editDraft, setEditDraft] = useState(null);
  const [confirmToggle, setConfirmToggle] = useState(null);
  const [draft, setDraft] = useState(BLANK);

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
    setDraft(BLANK);
    setModalOpen(false);
  };

  const openEdit = (v) => {
    setEditVehiculo(v);
    setEditDraft({ patente: v.patente, interno: v.interno === "—" ? "" : v.interno, tipo: v.tipo, tara: String(v.tara), titular: v.titular });
  };

  const submitEdit = () => {
    if (!editDraft.patente.trim() || !editDraft.tara) return;
    setRows((rs) => rs.map((r) => r.id === editVehiculo.id ? {
      ...r,
      patente: editDraft.patente.toUpperCase(),
      interno: editDraft.interno || "—",
      tipo: editDraft.tipo,
      tara: parseInt(editDraft.tara, 10),
      titular: editDraft.titular,
    } : r));
    setEditVehiculo(null);
    setEditDraft(null);
  };

  const toggleEstado = (id) => {
    setRows((rs) => rs.map((r) => r.id === id ? { ...r, estado: r.estado === "Activo" ? "Inactivo" : "Activo" } : r));
    setConfirmToggle(null);
  };

  const FormFields = ({ d, set }) => (
    <div className="grid grid-2">
      <Field label="Patente"><input className="input" value={d.patente} onChange={(e) => set({ ...d, patente: e.target.value })} placeholder="ABC-123" /></Field>
      <Field label="N° interno"><input className="input" value={d.interno} onChange={(e) => set({ ...d, interno: e.target.value })} placeholder="00" /></Field>
      <Field label="Tipo">
        <select className="select" value={d.tipo} onChange={(e) => set({ ...d, tipo: e.target.value })}>
          {Object.keys(VEHICLE_TYPES).map((t) => <option key={t}>{t}</option>)}
        </select>
      </Field>
      <Field label="Tara (kg)"><input className="input num" value={d.tara} onChange={(e) => set({ ...d, tara: e.target.value })} placeholder="8500" /></Field>
      <Field label="Titular">
        <select className="select" value={d.titular} onChange={(e) => set({ ...d, titular: e.target.value })}>
          <option>Municipal</option><option>Particular</option>
        </select>
      </Field>
    </div>
  );

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
                <td>{v.estado === "Activo" ? <Pill kind="green" dot>Activo</Pill> : <Pill kind="gray" dot>Inactivo</Pill>}</td>
                <td style={{ textAlign: "right" }}>
                  <div className="actions">
                    <button className="btn btn-ghost btn-sm" onClick={() => openEdit(v)}>
                      <Icon name="pencil" size={14} /> Editar
                    </button>
                    <button className="btn btn-ghost btn-sm" onClick={() => setConfirmToggle(v)}>
                      <Icon name="power" size={14} /> {v.estado === "Activo" ? "Desactivar" : "Activar"}
                    </button>
                  </div>
                </td>
              </tr>
            ))}
            {filtered.length === 0 && (
              <tr><td colSpan={7} style={{ textAlign: "center", color: "var(--ink-500)", padding: 40 }}>Sin resultados para "{query}".</td></tr>
            )}
          </tbody>
        </table>
        </div>
      </Card>

      {/* Modal nuevo vehículo */}
      {modalOpen && (
        <Modal title="Nuevo vehículo" onClose={() => { setModalOpen(false); setDraft(BLANK); }}
          footer={<>
            <Button kind="secondary" onClick={() => { setModalOpen(false); setDraft(BLANK); }}>Cancelar</Button>
            <Button kind="primary" onClick={submit} icon="save" disabled={!draft.patente.trim() || !draft.tara}>Guardar</Button>
          </>}>
          <FormFields d={draft} set={setDraft} />
        </Modal>
      )}

      {/* Modal editar vehículo */}
      {editVehiculo && editDraft && (
        <Modal title={`Editar — ${editVehiculo.patente}`} onClose={() => { setEditVehiculo(null); setEditDraft(null); }}
          footer={<>
            <Button kind="secondary" onClick={() => { setEditVehiculo(null); setEditDraft(null); }}>Cancelar</Button>
            <Button kind="primary" onClick={submitEdit} icon="save" disabled={!editDraft.patente.trim() || !editDraft.tara}>Guardar cambios</Button>
          </>}>
          <FormFields d={editDraft} set={setEditDraft} />
        </Modal>
      )}

      {/* Modal confirmar activar/desactivar */}
      {confirmToggle && (
        <Modal title={confirmToggle.estado === "Activo" ? "Desactivar vehículo" : "Activar vehículo"}
          onClose={() => setConfirmToggle(null)} maxWidth={440}
          footer={<>
            <Button kind="secondary" onClick={() => setConfirmToggle(null)}>Cancelar</Button>
            <Button kind={confirmToggle.estado === "Activo" ? "danger" : "primary"}
              icon="power" onClick={() => toggleEstado(confirmToggle.id)}>
              {confirmToggle.estado === "Activo" ? "Desactivar" : "Activar"}
            </Button>
          </>}>
          {confirmToggle.estado === "Activo"
            ? <><p>¿Desactivar el vehículo <b>{confirmToggle.patente}</b>?</p>
                <p style={{ fontSize: 13, color: "var(--ink-500)", marginTop: 6 }}>El vehículo deja de aparecer en el autocompletado del operador. Los pesajes históricos no se ven afectados.</p></>
            : <><p>¿Activar el vehículo <b>{confirmToggle.patente}</b>?</p>
                <p style={{ fontSize: 13, color: "var(--ink-500)", marginTop: 6 }}>El vehículo vuelve a estar disponible en el formulario de pesaje.</p></>
          }
        </Modal>
      )}
    </div>
  );
}

window.AbmVehiculos = AbmVehiculos;
