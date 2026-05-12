/* global React, Button, Field, Icon */
const { useState } = React;

function Login({ onLogin }) {
  const [user, setUser] = useState("");
  const [pass, setPass] = useState("");
  const [err, setErr] = useState("");

  const submit = (e) => {
    e && e.preventDefault();
    const u = user.trim().toLowerCase();
    if (u === "roberto" && pass === "1234")      onLogin({ user: "roberto", role: "operator", display: "Roberto" });
    else if (u === "nacho" && pass === "1234")   onLogin({ user: "nacho",   role: "admin",    display: "Nacho" });
    else setErr("Usuario o contraseña incorrectos.");
  };

  return (
    <div className="login-page">
      <div className="login-card">
        <div className="brand">
          <div className="glyph"><Icon name="recycle" size={22} /></div>
          <div>
            <div className="name">Infinito Reciclaje</div>
            <div className="sub">Gestión de Balanza</div>
          </div>
        </div>

        <h2>Iniciar sesión</h2>
        <p className="lede">Predio de disposición final · Municipalidad de Corrientes</p>

        <form className="form" onSubmit={submit}>
          <Field label="Usuario">
            <input className="input" value={user} onChange={(e) => setUser(e.target.value)} placeholder="usuario" autoFocus />
          </Field>
          <Field label="Contraseña" hint={err || undefined} hintKind={err ? "error" : undefined}>
            <input className={"input" + (err ? " error" : "")} type="password" value={pass} onChange={(e) => setPass(e.target.value)} placeholder="••••" />
          </Field>
          <Button kind="primary" block type="submit">Ingresar</Button>
        </form>

        <div className="demo">
          <div className="title">Credenciales de demo</div>
          <div className="row"><b>Operador</b><code>roberto</code><span>/</span><code>1234</code></div>
          <div className="row"><b>Admin</b><code>nacho</code><span>/</span><code>1234</code></div>
        </div>
      </div>
    </div>
  );
}

window.Login = Login;
