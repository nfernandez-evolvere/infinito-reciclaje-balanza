/* global React, Button, Card, Pill, Modal, Field, Icon,
   ZONAS_DATA, fmtN */
const { useState } = React;

function AbmZonas() {
  const [rows, setRows] = useState(ZONAS_DATA);
  const [modalOpen, setModalOpen] = useState(false);
  const [draft, setDraft] = useState({ nombre: "", hectareas: "", barrios: "", estado: "Activo" });

  const submit = () => {
    if (!draft.nombre.trim()) return;
    setRows((rs) => [{
      id: Date.now(),
      nombre: draft.nombre,
      hectareas: parseFloat(draft.hectareas) || 0,
      barrios: parseInt(draft.barrios, 10) || 0,
      estado: draft.estado,
    }, ...rs]);
    setDraft({ nombre: "", hectareas: "", barrios: "", estado: "Activo" });
    setModalOpen(false);
  };

  const totalHa = rows.reduce((a, r) => a + r.hectareas, 0);

  return (
    <div className="page">
      <div style={{ display: "flex", alignItems: "flex-end", justifyContent: "space-between", marginBottom: 4 }}>
        <div>
          <h1>Zonas operativas</h1>
          <p className="lede">Zonas geográficas servidas por el predio · {totalHa.toLocaleString("es-AR")} ha en total.</p>
        </div>
        <Button icon="plus" onClick={() => setModalOpen(true)}>Agregar zona</Button>
      </div>

      <Card compact>
        <div style={{ overflowX: "auto" }}>
        <table className="table">
          <thead>
            <tr>
              <th>Zona</th>
              <th className="num">Superficie</th>
              <th className="num">Barrios</th>
              <th>Estado</th>
              <th style={{ textAlign: "right" }}>Acciones</th>
            </tr>
          </thead>
          <tbody>
            {rows.map((z) => (
              <tr key={z.id}>
                <td><b>{z.nombre}</b></td>
                <td className="num">{z.hectareas.toLocaleString("es-AR")} ha</td>
                <td className="num">{z.barrios}</td>
                <td>{z.estado === "Activo" ? <Pill kind="green" dot>Activo</Pill> : <Pill kind="gray" dot>Inactivo</Pill>}</td>
                <td style={{ textAlign: "right" }}>
                  <div className="actions">
                    <button className="btn btn-ghost btn-sm" title="Editar"><Icon name="pencil" size={14} /></button>
                    <button className="btn btn-ghost btn-sm" title="Cambiar estado"><Icon name="power" size={14} /></button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
        </div>
      </Card>

      {modalOpen && (
        <Modal title="Nueva zona" onClose={() => setModalOpen(false)}
          footer={<>
            <Button kind="secondary" onClick={() => setModalOpen(false)}>Cancelar</Button>
            <Button kind="primary" onClick={submit} icon="save">Guardar</Button>
          </>}>
          <div className="grid grid-2">
            <Field label="Nombre" style={{ gridColumn: "1 / -1" }}>
              <input className="input" value={draft.nombre} onChange={(e) => setDraft({ ...draft, nombre: e.target.value })} placeholder="Ej. Costa Sur" />
            </Field>
            <Field label="Superficie (hectáreas)"><input className="input num" value={draft.hectareas} onChange={(e) => setDraft({ ...draft, hectareas: e.target.value })} placeholder="0" /></Field>
            <Field label="Cantidad de barrios"><input className="input num" value={draft.barrios} onChange={(e) => setDraft({ ...draft, barrios: e.target.value })} placeholder="0" /></Field>
            <Field label="Estado" style={{ gridColumn: "1 / -1" }}>
              <div style={{ display: "inline-flex", gap: 8 }}>
                {["Activo", "Inactivo"].map((s) => (
                  <button key={s} type="button"
                    className={"btn btn-sm " + (draft.estado === s ? "btn-primary" : "btn-secondary")}
                    onClick={() => setDraft({ ...draft, estado: s })}>{s}</button>
                ))}
              </div>
            </Field>
          </div>
        </Modal>
      )}
    </div>
  );
}

window.AbmZonas = AbmZonas;
