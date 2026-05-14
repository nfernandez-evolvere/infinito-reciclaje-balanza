/* global React, Button, Card, Field, Icon, Pill, Modal, fmtN, useAppContext */
const { useState } = React;

function AbmTipos() {
  const { tiposVehiculo: rows, setTiposVehiculo: setRows } = useAppContext();
  const [modalOpen, setModalOpen] = useState(false);
  const [editTipo, setEditTipo] = useState(null);
  const [editDraft, setEditDraft] = useState(null);
  const [confirmToggle, setConfirmToggle] = useState(null);
  const [draft, setDraft] = useState({ nombre: "", rangeMin: "", rangeMax: "" });

  const submit = () => {
    if (!draft.nombre.trim() || !draft.rangeMin || !draft.rangeMax) return;
    setRows((rs) => [...rs, { id: Date.now(), nombre: draft.nombre, rangeMin: parseInt(draft.rangeMin, 10), rangeMax: parseInt(draft.rangeMax, 10), estado: "Activo" }]);
    setDraft({ nombre: "", rangeMin: "", rangeMax: "" });
    setModalOpen(false);
  };

  const openEdit = (r) => {
    setEditTipo(r);
    setEditDraft({ nombre: r.nombre, rangeMin: String(r.rangeMin), rangeMax: String(r.rangeMax) });
  };

  const submitEdit = () => {
    if (!editDraft.nombre.trim() || !editDraft.rangeMin || !editDraft.rangeMax) return;
    setRows((rs) => rs.map((r) => r.id === editTipo.id ? {
      ...r,
      nombre: editDraft.nombre,
      rangeMin: parseInt(editDraft.rangeMin, 10),
      rangeMax: parseInt(editDraft.rangeMax, 10),
    } : r));
    setEditTipo(null);
    setEditDraft(null);
  };

  const toggleEstado = (id) => {
    setRows((rs) => rs.map((r) => r.id === id ? { ...r, estado: r.estado === "Activo" ? "Inactivo" : "Activo" } : r));
    setConfirmToggle(null);
  };

  const FormFields = ({ d, set, isNew }) => (
    <div className="grid grid-2">
      <Field label="Nombre" style={{ gridColumn: "1 / -1" }}>
        <input className="input" value={d.nombre} onChange={(e) => set({ ...d, nombre: e.target.value })} placeholder="Ej. Semirremolque" disabled={!isNew} />
      </Field>
      <Field label="Peso bruto mínimo (kg)" hint="Lo mínimo que debería marcar la balanza con este tipo cargado.">
        <input className="input num" value={d.rangeMin} onChange={(e) => set({ ...d, rangeMin: e.target.value })} placeholder="7000" />
      </Field>
      <Field label="Peso bruto máximo (kg)" hint="Lo máximo esperado. Incluye el vehículo vacío más la carga.">
        <input className="input num" value={d.rangeMax} onChange={(e) => set({ ...d, rangeMax: e.target.value })} placeholder="30000" />
      </Field>
    </div>
  );

  return (
    <div className="page">
      <div style={{ display: "flex", alignItems: "flex-end", justifyContent: "space-between", marginBottom: 4 }}>
        <div>
          <h1>Tipos de vehículo</h1>
          <p className="lede">Rangos de <b>peso bruto</b> esperados por tipo (vehículo + carga). Se usan para detectar pesajes anómalos.</p>
        </div>
        <Button icon="plus" onClick={() => setModalOpen(true)}>Agregar tipo</Button>
      </div>

      <Card compact>
        <div style={{ overflowX: "auto" }}>
        <table className="table">
          <thead>
            <tr>
              <th>Tipo</th>
              <th className="num">Bruto mínimo</th>
              <th className="num">Bruto máximo</th>
              <th>Estado</th>
              <th style={{ textAlign: "right" }}>Acciones</th>
            </tr>
          </thead>
          <tbody>
            {rows.map((r) => (
              <tr key={r.id}>
                <td><b>{r.nombre}</b></td>
                <td className="num">{fmtN(r.rangeMin)} kg</td>
                <td className="num">{fmtN(r.rangeMax)} kg</td>
                <td>{r.estado === "Activo" ? <Pill kind="green" dot>Activo</Pill> : <Pill kind="gray" dot>Inactivo</Pill>}</td>
                <td style={{ textAlign: "right" }}>
                  <div className="actions">
                    <button className="btn btn-ghost btn-sm" onClick={() => openEdit(r)}>
                      <Icon name="pencil" size={14} /> Editar rangos
                    </button>
                    <button className="btn btn-ghost btn-sm" onClick={() => setConfirmToggle(r)}>
                      <Icon name="power" size={14} /> {r.estado === "Activo" ? "Desactivar" : "Activar"}
                    </button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
        </div>
      </Card>

      <div className="banner info" style={{ marginTop: 16 }}>
        <div className="ic" style={{ color: "var(--blue-700)" }}><Icon name="info" size={20} /></div>
        <div>
          <div className="title">Estos rangos son de peso bruto (vehículo + carga), no de tara.</div>
          <div className="body">Son informativos: si la balanza registra un valor fuera del rango esperado para el tipo, el sistema lo marca como anomalía en el dashboard pero el operador puede igual registrarlo. La tara de cada vehículo se configura por separado en el padrón de vehículos.</div>
        </div>
      </div>

      {/* Modal nuevo tipo */}
      {modalOpen && (
        <Modal title="Nuevo tipo de vehículo" onClose={() => { setModalOpen(false); setDraft({ nombre: "", rangeMin: "", rangeMax: "" }); }}
          footer={<>
            <Button kind="secondary" onClick={() => { setModalOpen(false); setDraft({ nombre: "", rangeMin: "", rangeMax: "" }); }}>Cancelar</Button>
            <Button kind="primary" onClick={submit} icon="save" disabled={!draft.nombre.trim() || !draft.rangeMin || !draft.rangeMax}>Guardar</Button>
          </>}>
          <FormFields d={draft} set={setDraft} isNew={true} />
        </Modal>
      )}

      {/* Modal editar tipo */}
      {editTipo && editDraft && (
        <Modal title={`Editar rangos — ${editTipo.nombre}`} onClose={() => { setEditTipo(null); setEditDraft(null); }}
          footer={<>
            <Button kind="secondary" onClick={() => { setEditTipo(null); setEditDraft(null); }}>Cancelar</Button>
            <Button kind="primary" onClick={submitEdit} icon="save" disabled={!editDraft.rangeMin || !editDraft.rangeMax}>Guardar cambios</Button>
          </>}>
          <FormFields d={editDraft} set={setEditDraft} isNew={false} />
        </Modal>
      )}

      {/* Modal confirmar activar/desactivar */}
      {confirmToggle && (
        <Modal title={confirmToggle.estado === "Activo" ? "Desactivar tipo" : "Activar tipo"}
          onClose={() => setConfirmToggle(null)} maxWidth={440}
          footer={<>
            <Button kind="secondary" onClick={() => setConfirmToggle(null)}>Cancelar</Button>
            <Button kind={confirmToggle.estado === "Activo" ? "danger" : "primary"}
              icon="power" onClick={() => toggleEstado(confirmToggle.id)}>
              {confirmToggle.estado === "Activo" ? "Desactivar" : "Activar"}
            </Button>
          </>}>
          {confirmToggle.estado === "Activo"
            ? <><p>¿Desactivar el tipo <b>{confirmToggle.nombre}</b>?</p>
                <p style={{ fontSize: 13, color: "var(--ink-500)", marginTop: 6 }}>Los vehículos de este tipo siguen activos, pero no se validará el rango de peso en nuevos pesajes.</p></>
            : <><p>¿Activar el tipo <b>{confirmToggle.nombre}</b>?</p>
                <p style={{ fontSize: 13, color: "var(--ink-500)", marginTop: 6 }}>La validación de rango de peso vuelve a aplicarse en nuevos pesajes.</p></>
          }
        </Modal>
      )}
    </div>
  );
}

window.AbmTipos = AbmTipos;
