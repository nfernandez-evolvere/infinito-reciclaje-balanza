/* global React, Button, Card, Pill, Modal, Field, Icon, useAppContext */
const { useState } = React;

function AbmServicios() {
  const { servicios: rows, setServicios: setRows, tiposVehiculo } = useAppContext();
  const VEHICLE_TYPES = Object.fromEntries(tiposVehiculo.map((t) => [t.nombre, t]));
  const [modalOpen, setModalOpen] = useState(false);
  const [editServicio, setEditServicio] = useState(null);
  const [editDraft, setEditDraft] = useState(null);
  const [confirmToggle, setConfirmToggle] = useState(null);
  const [draft, setDraft] = useState({ nombre: "", tipoSugerido: "Compactador", estado: "Activo" });

  const submit = () => {
    if (!draft.nombre.trim()) return;
    setRows((rs) => [{ id: Date.now(), ...draft }, ...rs]);
    setDraft({ nombre: "", tipoSugerido: "Compactador", estado: "Activo" });
    setModalOpen(false);
  };

  const openEdit = (s) => {
    setEditServicio(s);
    setEditDraft({ nombre: s.nombre, tipoSugerido: s.tipoSugerido });
  };

  const submitEdit = () => {
    if (!editDraft.nombre.trim()) return;
    setRows((rs) => rs.map((r) => r.id === editServicio.id ? { ...r, ...editDraft } : r));
    setEditServicio(null);
    setEditDraft(null);
  };

  const toggleEstado = (id) => {
    setRows((rs) => rs.map((r) => r.id === id ? { ...r, estado: r.estado === "Activo" ? "Inactivo" : "Activo" } : r));
    setConfirmToggle(null);
  };

  return (
    <div className="page">
      <div style={{ display: "flex", alignItems: "flex-end", justifyContent: "space-between", marginBottom: 4 }}>
        <div>
          <h1>Tipos de servicio</h1>
          <p className="lede">Categorías de pesaje y vehículo habitual. Los turnos se configuran por zona.</p>
        </div>
        <Button icon="plus" onClick={() => setModalOpen(true)}>Agregar tipo</Button>
      </div>

      <Card compact>
        <div style={{ overflowX: "auto" }}>
        <table className="table">
          <thead>
            <tr>
              <th>Servicio</th>
              <th>Vehículo habitual</th>
              <th>Estado</th>
              <th style={{ textAlign: "right" }}>Acciones</th>
            </tr>
          </thead>
          <tbody>
            {rows.map((s) => (
              <tr key={s.id}>
                <td><b>{s.nombre}</b></td>
                <td><Pill kind="gray">{s.tipoSugerido}</Pill></td>
                <td>{s.estado === "Activo" ? <Pill kind="green" dot>Activo</Pill> : <Pill kind="gray" dot>Inactivo</Pill>}</td>
                <td style={{ textAlign: "right" }}>
                  <div className="actions">
                    <button className="btn btn-ghost btn-sm" onClick={() => openEdit(s)}>
                      <Icon name="pencil" size={14} /> Editar
                    </button>
                    <button className="btn btn-ghost btn-sm" onClick={() => setConfirmToggle(s)}>
                      <Icon name="power" size={14} /> {s.estado === "Activo" ? "Desactivar" : "Activar"}
                    </button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
        </div>
      </Card>

      {/* Modal nuevo tipo */}
      {modalOpen && (
        <Modal title="Nuevo tipo de servicio" onClose={() => setModalOpen(false)}
          footer={<>
            <Button kind="secondary" onClick={() => setModalOpen(false)}>Cancelar</Button>
            <Button kind="primary" onClick={submit} icon="save">Guardar</Button>
          </>}>
          <div className="grid grid-2">
            <Field label="Nombre" style={{ gridColumn: "1 / -1" }}>
              <input className="input" value={draft.nombre} onChange={(e) => setDraft({ ...draft, nombre: e.target.value })} placeholder="Ej. Poda urbana" />
            </Field>
            <Field label="Vehículo habitual" style={{ gridColumn: "1 / -1" }}>
              <select className="select" value={draft.tipoSugerido} onChange={(e) => setDraft({ ...draft, tipoSugerido: e.target.value })}>
                {Object.keys(VEHICLE_TYPES).map((t) => <option key={t}>{t}</option>)}
              </select>
            </Field>
          </div>
        </Modal>
      )}

      {/* Modal editar tipo */}
      {editServicio && editDraft && (
        <Modal title={`Editar — ${editServicio.nombre}`} onClose={() => { setEditServicio(null); setEditDraft(null); }}
          footer={<>
            <Button kind="secondary" onClick={() => { setEditServicio(null); setEditDraft(null); }}>Cancelar</Button>
            <Button kind="primary" onClick={submitEdit} icon="save">Guardar cambios</Button>
          </>}>
          <div className="grid grid-2">
            <Field label="Nombre" style={{ gridColumn: "1 / -1" }}>
              <input className="input" value={editDraft.nombre} onChange={(e) => setEditDraft({ ...editDraft, nombre: e.target.value })} />
            </Field>
            <Field label="Vehículo habitual" style={{ gridColumn: "1 / -1" }}>
              <select className="select" value={editDraft.tipoSugerido} onChange={(e) => setEditDraft({ ...editDraft, tipoSugerido: e.target.value })}>
                {Object.keys(VEHICLE_TYPES).map((t) => <option key={t}>{t}</option>)}
              </select>
            </Field>
          </div>
        </Modal>
      )}

      {/* Modal confirmar activar/desactivar */}
      {confirmToggle && (
        <Modal title={confirmToggle.estado === "Activo" ? "Desactivar tipo de servicio" : "Activar tipo de servicio"}
          onClose={() => setConfirmToggle(null)} maxWidth={440}
          footer={<>
            <Button kind="secondary" onClick={() => setConfirmToggle(null)}>Cancelar</Button>
            <Button kind={confirmToggle.estado === "Activo" ? "danger" : "primary"}
              icon="power" onClick={() => toggleEstado(confirmToggle.id)}>
              {confirmToggle.estado === "Activo" ? "Desactivar" : "Activar"}
            </Button>
          </>}>
          {confirmToggle.estado === "Activo"
            ? <><p>¿Desactivar <b>{confirmToggle.nombre}</b>?</p>
                <p style={{ fontSize: 13, color: "var(--ink-500)", marginTop: 6 }}>El servicio deja de aparecer como opción en nuevos pesajes. Los registros históricos no se ven afectados.</p></>
            : <><p>¿Activar <b>{confirmToggle.nombre}</b>?</p>
                <p style={{ fontSize: 13, color: "var(--ink-500)", marginTop: 6 }}>El servicio vuelve a estar disponible en el formulario de pesaje.</p></>
          }
        </Modal>
      )}
    </div>
  );
}

window.AbmServicios = AbmServicios;
