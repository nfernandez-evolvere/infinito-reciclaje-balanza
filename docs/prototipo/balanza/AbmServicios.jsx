/* global React, Button, Card, Pill, Modal, Field, Icon,
   SERVICIOS_DATA, VEHICLE_TYPES */
const { useState } = React;

const TURNOS_OPCIONES = ["Diurna", "Nocturna"];

function AbmServicios() {
  const [rows, setRows] = useState(SERVICIOS_DATA);
  const [modalOpen, setModalOpen] = useState(false);
  const [draft, setDraft] = useState({ nombre: "", turnos: [], tipoSugerido: "Compactador", estado: "Activo" });

  const toggleTurno = (t) => {
    setDraft((d) => ({
      ...d,
      turnos: d.turnos.includes(t) ? d.turnos.filter((x) => x !== t) : [...d.turnos, t],
    }));
  };

  const submit = () => {
    if (!draft.nombre.trim()) return;
    setRows((rs) => [{ id: Date.now(), ...draft }, ...rs]);
    setDraft({ nombre: "", turnos: [], tipoSugerido: "Compactador", estado: "Activo" });
    setModalOpen(false);
  };

  return (
    <div className="page">
      <div style={{ display: "flex", alignItems: "flex-end", justifyContent: "space-between", marginBottom: 4 }}>
        <div>
          <h1>Tipos de servicio</h1>
          <p className="lede">Categorías de pesaje, turnos disponibles y vehículo habitual.</p>
        </div>
        <Button icon="plus" onClick={() => setModalOpen(true)}>Agregar tipo</Button>
      </div>

      <Card compact>
        <div style={{ overflowX: "auto" }}>
        <table className="table">
          <thead>
            <tr>
              <th>Servicio</th>
              <th>Turnos</th>
              <th>Vehículo habitual</th>
              <th>Estado</th>
              <th style={{ textAlign: "right" }}>Acciones</th>
            </tr>
          </thead>
          <tbody>
            {rows.map((s) => (
              <tr key={s.id}>
                <td><b>{s.nombre}</b></td>
                <td>
                  {s.turnos && s.turnos.length > 0
                    ? <div style={{ display: "flex", gap: 4 }}>{s.turnos.map((t) => <Pill key={t} kind="blue">{t}</Pill>)}</div>
                    : <span style={{ color: "var(--ink-400)" }}>—</span>}
                </td>
                <td><Pill kind="gray">{s.tipoSugerido}</Pill></td>
                <td>{s.estado === "Activo" ? <Pill kind="green" dot>Activo</Pill> : <Pill kind="gray" dot>Inactivo</Pill>}</td>
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
        <Modal title="Nuevo tipo de servicio" onClose={() => setModalOpen(false)}
          footer={<>
            <Button kind="secondary" onClick={() => setModalOpen(false)}>Cancelar</Button>
            <Button kind="primary" onClick={submit} icon="save">Guardar</Button>
          </>}>
          <div className="grid grid-2">
            <Field label="Nombre" style={{ gridColumn: "1 / -1" }}>
              <input className="input" value={draft.nombre} onChange={(e) => setDraft({ ...draft, nombre: e.target.value })} placeholder="Ej. Poda urbana" />
            </Field>
            <Field label="Turnos disponibles" style={{ gridColumn: "1 / -1" }}
              hint="El operador deberá elegir turno al registrar un pesaje de este servicio.">
              <div style={{ display: "flex", gap: 12 }}>
                {TURNOS_OPCIONES.map((t) => (
                  <label key={t} style={{ display: "flex", alignItems: "center", gap: 6, fontSize: 14, cursor: "pointer" }}>
                    <input
                      type="checkbox"
                      checked={draft.turnos.includes(t)}
                      onChange={() => toggleTurno(t)}
                    />
                    {t}
                  </label>
                ))}
              </div>
            </Field>
            <Field label="Vehículo habitual" style={{ gridColumn: "1 / -1" }}>
              <select className="select" value={draft.tipoSugerido} onChange={(e) => setDraft({ ...draft, tipoSugerido: e.target.value })}>
                {Object.keys(VEHICLE_TYPES).map((t) => <option key={t}>{t}</option>)}
              </select>
            </Field>
          </div>
        </Modal>
      )}
    </div>
  );
}

window.AbmServicios = AbmServicios;
