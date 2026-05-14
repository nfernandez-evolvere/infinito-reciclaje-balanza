/* global React, ReactDOM,
   Login, OperatorShell, AdminShell,
   Balanza, Historial, Dashboard,
   PesajesAdmin, AbmVehiculos, AbmZonas, AbmServicios, AbmTipos, AbmUsuarios,
   Reportes, buildLogEntries, Modal, Button, Toast, Icon,
   AppProvider, useAppContext */
const { useState, useCallback, useEffect } = React;

function OpFooter({ pesajes }) {
  const last = pesajes[0];
  const tot = pesajes.reduce((a, p) => ({ count: a.count + 1, t: a.t + p.neto / 1000 }), { count: 0, t: 0 });
  const enPredio = pesajes.filter((p) => p.estado === "En predio").length;
  return (
    <footer className="op-footer">
      <div className="stat">
        <span className="lbl">Último pesaje</span>
        {last
          ? <span><b>{last.patente}</b> · <b>{fmtN(last.neto)} kg</b> · {last.horaEntrada}</span>
          : <span className="muted">—</span>}
      </div>
      <div className="stat">
        <span className="lbl">Turno</span>
        <span><b>{tot.count} pesajes</b> · <b>{tot.t.toLocaleString("es-AR", { minimumFractionDigits: 1, maximumFractionDigits: 1 })} t</b> netas</span>
      </div>
      <div className="stat">
        <span className="lbl">En predio</span>
        <span><b>{enPredio}</b> camión{enPredio === 1 ? "" : "es"}</span>
      </div>
      <div className="spacer" />
      <span className="muted body-sm">Turno tarde · 14:00 — 20:00</span>
    </footer>
  );
}

function LogoutConfirmModal({ hasUnsaved, onCancel, onConfirm }) {
  return (
    <Modal title="¿Cerrar sesión?" onClose={onCancel} maxWidth={440}
      footer={<>
        <Button kind="secondary" onClick={onCancel}>Cancelar</Button>
        <Button kind="danger" icon="log-out" onClick={onConfirm}>Cerrar sesión</Button>
      </>}>
      {hasUnsaved
        ? <p>Tenés un pesaje sin guardar. Si cerrás sesión ahora, se pierden los datos del formulario.</p>
        : <p>Vas a salir del sistema. Podés volver a ingresar cuando quieras.</p>}
    </Modal>
  );
}

function AppInner() {
  const {
    pesajes, setPesajes, pesajesLog, setPesajesLog,
    vehiculos, zonas, zonaServicios, servicioNames, zonaNames, vehicleTypeMap, servicioCascade,
    loading,
  } = useAppContext();

  const [user, setUser] = useState(null);
  const [screen, setScreen] = useState("balanza");
  const [online, setOnline] = useState(true);
  const [formDirty, setFormDirty] = useState(false);
  const [confirmLogout, setConfirmLogout] = useState(false);
  const [toast, setToast] = useState(null);

  const addPesaje = useCallback((p) => setPesajes((ps) => [p, ...ps]), []);

  const editPesaje = useCallback((id, patch, motivo, actor) => {
    setPesajes((ps) => ps.map((p) => {
      if (p.id !== id) return p;
      const next = { ...p, ...patch, editado: true };
      if (patch.bruto != null || patch.tara != null) {
        next.neto = (patch.bruto ?? p.bruto) - (patch.tara ?? p.tara);
      }
      return next;
    }));
    const original = pesajes.find((p) => p.id === id);
    if (original) {
      const entries = buildLogEntries({ pesajeId: id, patch, original, actor, motivo });
      if (entries.length) setPesajesLog((l) => [...entries, ...l]);
    }
  }, [pesajes]);

  const markEgreso = useCallback((id, payload, actor) => {
    setPesajes((ps) => ps.map((p) =>
      p.id === id
        ? { ...p, horaSalida: payload.horaSalida, brutoSalida: payload.brutoSalida, estado: "Cerrado" }
        : p
    ));
    const fecha = new Date().toLocaleString("es-AR", { hour12: false }).replace(",", "");
    setPesajesLog((l) => [{
      id: `egreso-${id}-${Date.now()}`,
      pesajeId: id, fecha, usuario: actor,
      campo: "egreso", anterior: "—", nuevo: payload.horaSalida,
      motivo: payload.brutoSalida != null ? `Egreso registrado · peso de salida ${payload.brutoSalida.toLocaleString("es-AR")} kg` : "Egreso registrado.",
    }, ...l]);
  }, []);

  const toggleOnline = useCallback(() => {
    setOnline((was) => {
      const next = !was;
      if (next) setToast({ kind: "ok", title: "Conexión restablecida.", body: "Los pesajes se sincronizaron correctamente." });
      else setToast({ kind: "warn", title: "Sin conexión.", body: "Los pesajes se guardan localmente." });
      return next;
    });
  }, []);

  if (loading) {
    return (
      <div style={{ height: "100vh", display: "flex", alignItems: "center", justifyContent: "center", flexDirection: "column", gap: 16, color: "var(--ink-500)" }}>
        <Icon name="loader" size={32} style={{ animation: "spin 1s linear infinite" }} />
        <span>Cargando datos…</span>
      </div>
    );
  }

  if (!user) {
    return <Login onLogin={(u) => {
      setUser(u);
      setScreen(u.role === "operator" ? "balanza" : "dashboard");
    }} />;
  }

  const requestLogout = () => {
    if (user.role === "operator" && formDirty) setConfirmLogout(true);
    else doLogout();
  };
  const doLogout = () => {
    setUser(null); setScreen("balanza"); setFormDirty(false); setConfirmLogout(false);
  };

  return (
    <>
      {user.role === "operator" ? (
        <OperatorShell user={user} screen={screen} onNav={setScreen} onLogout={requestLogout}
          online={online} onToggleOnline={toggleOnline}
          footer={<OpFooter pesajes={pesajes} />}>
          {screen === "balanza"   && <Balanza
              pesajes={pesajes}
              vehiculos={vehiculos.filter((v) => v.estado === "Activo")}
              zonas={zonas}
              zonaServicios={zonaServicios}
              servicioNames={servicioNames}
              vehicleTypeMap={vehicleTypeMap}
              servicioCascade={servicioCascade}
              onSave={addPesaje}
              onDirtyChange={setFormDirty} />}
          {screen === "historial" && <Historial
              pesajes={pesajes}
              log={pesajesLog}
              actor={user.user}
              servicioNames={servicioNames}
              zonaNames={zonaNames}
              onEdit={(id, patch, motivo) => editPesaje(id, patch, motivo, user.user)}
              onEgreso={(id, payload) => markEgreso(id, payload, user.user)} />}
        </OperatorShell>
      ) : (
        <AdminShell user={user} screen={screen} onNav={setScreen} onLogout={requestLogout}>
          {screen === "dashboard"  && <Dashboard pesajes={pesajes} />}
          {screen === "pesajes"    && <PesajesAdmin
              pesajes={pesajes}
              log={pesajesLog}
              actor={user.user}
              servicioNames={servicioNames}
              zonaNames={zonaNames}
              onEdit={(id, patch, motivo) => editPesaje(id, patch, motivo, user.user)}
              onEgreso={(id, payload) => markEgreso(id, payload, user.user)} />}
          {screen === "vehiculos"  && <AbmVehiculos />}
          {screen === "zonas"      && <AbmZonas />}
          {screen === "servicios"  && <AbmServicios />}
          {screen === "tipos"      && <AbmTipos />}
          {screen === "usuarios"   && <AbmUsuarios />}
          {screen === "reportes"   && <Reportes />}
        </AdminShell>
      )}

      {confirmLogout && (
        <LogoutConfirmModal
          hasUnsaved={formDirty}
          onCancel={() => setConfirmLogout(false)}
          onConfirm={doLogout} />
      )}
      {toast && <Toast {...toast} onDismiss={() => setToast(null)} />}
    </>
  );
}

function App() {
  return (
    <AppProvider>
      <AppInner />
    </AppProvider>
  );
}

const root = ReactDOM.createRoot(document.getElementById("root"));
root.render(<App />);
