/* global React, Icon, Button */
const { useState, useEffect } = React;

function useClock() {
  const [now, setNow] = useState(new Date());
  useEffect(() => {
    const id = setInterval(() => setNow(new Date()), 1000);
    return () => clearInterval(id);
  }, []);
  return now;
}

function OperatorShell({ user, screen, onNav, onLogout, online = true, onToggleOnline, footer, children }) {
  const now = useClock();
  const hhmm = now.toLocaleTimeString("es-AR", { hour: "2-digit", minute: "2-digit" });
  return (
    <div className="app">
      <header className="shell-header">
        <div className="logo">
          <div className="glyph"><Icon name="recycle" size={18} /></div>
          <span>Infinito Reciclaje <span style={{ color: "var(--ink-500)", fontWeight: 500 }}>· Balanza</span></span>
        </div>
        <div className="spacer" />
        <nav style={{ display: "flex", gap: 4 }}>
          <Button kind={screen === "balanza" ? "primary" : "ghost"} size="sm" icon="scale" onClick={() => onNav("balanza")}>Pesaje</Button>
          <Button kind={screen === "historial" ? "primary" : "ghost"} size="sm" icon="list" onClick={() => onNav("historial")}>Historial</Button>
        </nav>
        <div style={{ width: 1, alignSelf: "stretch", background: "var(--line)", margin: "0 8px" }} />
        <button
          className={"conn" + (online ? "" : " off")}
          onClick={onToggleOnline}
          title={online ? "Simular sin conexión" : "Simular reconexión"}
          style={{ background: "transparent", border: 0, padding: 0, cursor: onToggleOnline ? "pointer" : "default", fontFamily: "inherit" }}>
          <span className="dot" />
          {online ? "En línea" : "Sin conexión"}
        </button>
        <span className="clock">{hhmm}</span>
        <span className="user-chip">
          <span className="av">R</span>
          <span><b style={{ color: "var(--ink-900)" }}>{user.display}</b> · Operador</span>
        </span>
        <button className="icon-btn" title="Cerrar sesión" onClick={onLogout}><Icon name="log-out" size={18} /></button>
      </header>

      {!online && (
        <div className="banner warn" style={{ borderRadius: 0, borderLeftWidth: 0, borderTop: 0, paddingLeft: 24 }}>
          <div className="ic" style={{ color: "var(--orange-700)" }}><Icon name="cloud-off" size={20} /></div>
          <div>
            <div className="title">Sin conexión a la red.</div>
            <div className="body">Podés seguir registrando pesajes — se guardan localmente y se sincronizan cuando vuelva la conexión.</div>
          </div>
        </div>
      )}

      <main style={{ flex: 1 }}>
        {children}
      </main>

      {footer}
    </div>
  );
}

function AdminShell({ user, screen, onNav, onLogout, children }) {
  const groups = [
    { id: "operacion", label: "Operación", items: [
      { id: "dashboard", label: "Dashboard", icon: "layout-dashboard" },
      { id: "pesajes",   label: "Pesajes",   icon: "scale" },
    ]},
    { id: "padrones", label: "Padrones", items: [
      { id: "vehiculos", label: "Vehículos",         icon: "truck" },
      { id: "zonas",     label: "Zonas",             icon: "map" },
      { id: "servicios", label: "Tipos de servicio", icon: "boxes" },
      { id: "tipos",     label: "Tipos de vehículo", icon: "settings" },
      { id: "usuarios",  label: "Usuarios",          icon: "users" },
    ]},
    { id: "analisis", label: "Análisis", items: [
      { id: "reportes",  label: "Reportes", icon: "bar-chart-3" },
    ]},
  ];

  // Auto-expand the group that contains the active screen
  const activeGroup = groups.find((g) => g.items.some((it) => it.id === screen))?.id;
  const [open, setOpen] = useState(() => Object.fromEntries(groups.map((g) => [g.id, g.id === activeGroup])));

  useEffect(() => {
    if (activeGroup) setOpen((o) => ({ ...o, [activeGroup]: true }));
  }, [activeGroup]);

  return (
    <div className="app has-sidebar">
      <aside className="sidebar">
        <div className="brand">
          <div className="glyph"><Icon name="recycle" size={18} /></div>
          <div>
            <div style={{ fontSize: 13 }}>Infinito Reciclaje</div>
            <div style={{ fontSize: 10, color: "var(--ink-500)", letterSpacing: "0.04em", textTransform: "uppercase", fontWeight: 600 }}>Balanza · Admin</div>
          </div>
        </div>
        <nav>
          {groups.map((g) => (
            <div key={g.id} className="nav-group">
              <div
                className={"nav-group-header" + (open[g.id] ? " open" : "")}
                onClick={() => setOpen((o) => ({ ...o, [g.id]: !o[g.id] }))}>
                {g.label}
                <span className="chev"><Icon name="chevron-right" size={14} /></span>
              </div>
              {open[g.id] && (
                <div className="nav-group-items">
                  {g.items.map((it) => (
                    <div key={it.id}
                         className={"nav-item" + (screen === it.id ? " active" : "")}
                         onClick={() => onNav(it.id)}>
                      <span className="ic"><Icon name={it.icon} size={18} /></span>
                      {it.label}
                    </div>
                  ))}
                </div>
              )}
            </div>
          ))}
        </nav>
        <div className="footer">
          <div className="av">N</div>
          <div>
            <div className="name">{user.display}</div>
            <div className="role">Admin</div>
          </div>
          <button className="logout" title="Cerrar sesión" onClick={onLogout}
            style={{ background: "transparent", border: 0, width: 32, height: 32, borderRadius: 999, cursor: "pointer", color: "var(--ink-500)", display: "grid", placeItems: "center" }}>
            <Icon name="log-out" size={16} />
          </button>
        </div>
      </aside>
      <div className="main-col">
        {children}
      </div>
    </div>
  );
}

Object.assign(window, { OperatorShell, AdminShell, useClock });
