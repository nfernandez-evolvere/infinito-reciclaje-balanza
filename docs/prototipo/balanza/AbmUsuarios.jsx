/* global React, Button, Card, Pill, Modal, Field, Icon,
   USUARIOS */
const { useState } = React;

function AbmUsuarios() {
  const [rows, setRows] = useState(USUARIOS);
  const [modalOpen, setModalOpen] = useState(false);
  const [draft, setDraft] = useState({ usuario: "", nombre: "", rol: "Operador", turno: "Mañana", estado: "Activo" });

  const submit = () => {
    if (!draft.usuario.trim() || !draft.nombre.trim()) return;
    setRows((rs) => [{ id: Date.now(), ...draft }, ...rs]);
    setDraft({ usuario: "", nombre: "", rol: "Operador", turno: "Mañana", estado: "Activo" });
    setModalOpen(false);
  };

  const initials = (n) => n.split(" ").map((x) => x[0]).slice(0, 2).join("").toUpperCase();

  return (
    <div className="page">
      <div style={{ display: "flex", alignItems: "flex-end", justifyContent: "space-between", marginBottom: 4 }}>
        <div>
          <h1>Usuarios</h1>
          <p className="lede">Operadores y administradores con acceso al sistema.</p>
        </div>
        <Button icon="plus" onClick={() => setModalOpen(true)}>Agregar usuario</Button>
      </div>

      <Card compact>
        <div style={{ overflowX: "auto" }}>
        <table className="table">
          <thead>
            <tr>
              <th>Usuario</th>
              <th>Nombre</th>
              <th>Rol</th>
              <th>Turno</th>
              <th>Estado</th>
              <th style={{ textAlign: "right" }}>Acciones</th>
            </tr>
          </thead>
          <tbody>
            {rows.map((u) => (
              <tr key={u.id}>
                <td><code style={{ background: "var(--bg)", padding: "2px 6px", borderRadius: 4, fontSize: 12, fontFamily: "ui-monospace, 'SF Mono', Menlo, monospace" }}>{u.usuario}</code></td>
                <td>
                  <div style={{ display: "inline-flex", alignItems: "center", gap: 10 }}>
                    <div style={{ width: 28, height: 28, borderRadius: 999, background: "var(--green-700)", color: "white", fontSize: 11, fontWeight: 700, display: "grid", placeItems: "center" }}>{initials(u.nombre)}</div>
                    <span><b>{u.nombre}</b></span>
                  </div>
                </td>
                <td>{u.rol === "Admin" ? <Pill kind="blue">Admin</Pill> : <Pill kind="gray">Operador</Pill>}</td>
                <td>{u.turno}</td>
                <td>{u.estado === "Activo" ? <Pill kind="green" dot>Activo</Pill> : <Pill kind="gray" dot>Inactivo</Pill>}</td>
                <td style={{ textAlign: "right" }}>
                  <div className="actions">
                    <button className="btn btn-ghost btn-sm" title="Editar"><Icon name="pencil" size={14} /></button>
                    <button className="btn btn-ghost btn-sm" title="Reiniciar contraseña"><Icon name="key" size={14} /></button>
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
        <Modal title="Nuevo usuario" onClose={() => setModalOpen(false)}
          footer={<>
            <Button kind="secondary" onClick={() => setModalOpen(false)}>Cancelar</Button>
            <Button kind="primary" onClick={submit} icon="save">Guardar</Button>
          </>}>
          <div className="grid grid-2">
            <Field label="Usuario"><input className="input" value={draft.usuario} onChange={(e) => setDraft({ ...draft, usuario: e.target.value })} placeholder="apellido" /></Field>
            <Field label="Nombre completo"><input className="input" value={draft.nombre} onChange={(e) => setDraft({ ...draft, nombre: e.target.value })} placeholder="Nombre y apellido" /></Field>
            <Field label="Rol">
              <select className="select" value={draft.rol} onChange={(e) => setDraft({ ...draft, rol: e.target.value })}>
                <option>Operador</option><option>Admin</option>
              </select>
            </Field>
            <Field label="Turno">
              <select className="select" value={draft.turno} onChange={(e) => setDraft({ ...draft, turno: e.target.value })}>
                <option>Mañana</option><option>Tarde</option><option>Noche</option><option>—</option>
              </select>
            </Field>
            <Field label="Contraseña inicial" style={{ gridColumn: "1 / -1" }} hint="Se le pedirá cambiarla en el primer ingreso.">
              <input className="input" type="password" placeholder="••••" />
            </Field>
          </div>
        </Modal>
      )}
    </div>
  );
}

window.AbmUsuarios = AbmUsuarios;
