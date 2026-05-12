/* global React, Button, Field, Icon, Banner, Modal,
   SERVICIOS, ZONAS, fmtKg, fmtN */
const { useState } = React;

// Shared modal used by Historial (operator) and PesajesAdmin.
// Mandatory motivo. Emits one log entry per changed field via onSave(patch, motivo).
function EditPesajeModal({ pesaje, onClose, onSave, actorRole }) {
  const [bruto, setBruto]       = useState(pesaje.bruto);
  const [tara,  setTara]        = useState(pesaje.tara);
  const [servicio, setServicio] = useState(pesaje.servicio);
  const [zona, setZona]         = useState(pesaje.zona);
  const [motivo, setMotivo]     = useState("");

  const neto = Math.max(0, (parseInt(bruto, 10) || 0) - (parseInt(tara, 10) || 0));
  const trustBanner = actorRole === "operator"
    ? "Toda corrección queda registrada con tu usuario, hora y motivo. El admin puede ver el historial."
    : "Cada cambio queda asociado al usuario y al motivo. No se sobrescribe el original.";

  return (
    <Modal title={`Editar pesaje #${pesaje.id}`} onClose={onClose} maxWidth={620}
      footer={<>
        <Button kind="secondary" onClick={onClose}>Cancelar</Button>
        <Button kind="primary" icon="save" disabled={!motivo.trim()}
          onClick={() => onSave({
            bruto: parseInt(bruto, 10), tara: parseInt(tara, 10), servicio, zona,
          }, motivo)}>Guardar cambios</Button>
      </>}>
      <Banner kind="info" title="Toda edición se registra en el historial" body={trustBanner} />
      <div className="grid grid-2" style={{ marginTop: 16 }}>
        <Field label="Servicio">
          <select className="select" value={servicio} onChange={(e) => setServicio(e.target.value)}>
            {SERVICIOS.map((s) => <option key={s}>{s}</option>)}
          </select>
        </Field>
        <Field label="Zona">
          <select className="select" value={zona} onChange={(e) => setZona(e.target.value)}>
            {ZONAS.map((z) => <option key={z}>{z}</option>)}
          </select>
        </Field>
        <Field label="Peso bruto (kg)">
          <input className="input num" value={bruto} onChange={(e) => setBruto(e.target.value)} />
        </Field>
        <Field label="Tara (kg)">
          <input className="input num" value={tara} onChange={(e) => setTara(e.target.value)} />
        </Field>
        <Field label="Neto resultante" style={{ gridColumn: "1 / -1" }}>
          <div style={{ padding: "12px 14px", background: "var(--bg)", borderRadius: 6, fontSize: 18, fontWeight: 700, fontFeatureSettings: '"tnum" 1', color: "var(--green-700)" }}>{fmtKg(neto)}</div>
        </Field>
        <Field label="Motivo de la corrección" hint="Obligatorio" style={{ gridColumn: "1 / -1" }}>
          <input className="input" value={motivo} onChange={(e) => setMotivo(e.target.value)}
            placeholder="Ej. Tipeo en el bruto: faltaba un dígito." />
        </Field>
      </div>
    </Modal>
  );
}

// Egreso modal — captures exit time + optional outgoing weight (informational only).
// Net waste weight is NOT recomputed from brutoSalida in this iteration (see README §G).
function EgresoModal({ pesaje, onClose, onConfirm }) {
  const [brutoSalida, setBrutoSalida] = useState("");
  const now = new Date();
  const horaSalida = now.toLocaleTimeString("es-AR", { hour: "2-digit", minute: "2-digit" });

  const submit = () => {
    onConfirm({
      horaSalida,
      brutoSalida: brutoSalida ? parseInt(brutoSalida.replace(/\D/g, ""), 10) : null,
    });
  };

  return (
    <Modal title={`Marcar egreso · pesaje #${pesaje.id}`} onClose={onClose} maxWidth={520}
      footer={<>
        <Button kind="secondary" onClick={onClose}>Cancelar</Button>
        <Button kind="primary" icon="log-out" onClick={submit}>Confirmar egreso</Button>
      </>}>
      <div style={{ marginBottom: 16 }}>
        <div style={{ fontSize: 13, color: "var(--ink-700)" }}>
          Camión <b>{pesaje.patente}</b> ({pesaje.tipo}) · ingresó a las <b>{pesaje.horaEntrada}</b> con <b className="num">{fmtKg(pesaje.bruto)}</b> brutos.
        </div>
      </div>

      <div className="grid grid-2">
        <Field label="Hora de salida">
          <input className="input num" value={horaSalida} readOnly style={{ background: "var(--bg)" }} />
        </Field>
        <Field label="Peso de salida (opcional)" hint="No afecta el cálculo del neto." hintKind="">
          <input className="input num" value={brutoSalida} onChange={(e) => setBrutoSalida(e.target.value)}
            placeholder={String(pesaje.tara)} inputMode="numeric" />
        </Field>
      </div>

      <Banner kind="info" title="El peso de los residuos no cambia"
        body={`Se calcula como bruto entrada − tara (${fmtN(pesaje.bruto)} − ${fmtN(pesaje.tara)} = ${fmtN(pesaje.neto)} kg). El peso de salida queda registrado para auditoría.`} />
    </Modal>
  );
}

// Read-only log viewer
function LogModal({ pesaje, entries, onClose }) {
  return (
    <Modal title={`Historial de cambios · pesaje #${pesaje.id}`} onClose={onClose} maxWidth={620}
      footer={<Button kind="primary" onClick={onClose}>Cerrar</Button>}>
      {entries.length === 0 && <p className="muted">Este pesaje no fue editado.</p>}
      <div style={{ display: "flex", flexDirection: "column", gap: 10 }}>
        {entries.map((e) => (
          <div key={e.id} style={{ padding: 12, background: "var(--bg)", borderRadius: 6, fontSize: 13 }}>
            <div style={{ display: "flex", justifyContent: "space-between", marginBottom: 4 }}>
              <b>{e.campo}</b>
              <span className="muted" style={{ fontSize: 12, fontFeatureSettings: '"tnum" 1' }}>{e.fecha} · {e.usuario}</span>
            </div>
            <div style={{ display: "flex", alignItems: "center", gap: 8, fontFeatureSettings: '"tnum" 1' }}>
              <span style={{ textDecoration: "line-through", color: "var(--ink-500)" }}>{String(e.anterior)}</span>
              <Icon name="arrow-right" size={14} />
              <b style={{ color: "var(--green-700)" }}>{String(e.nuevo)}</b>
            </div>
            {e.motivo && <div className="muted" style={{ marginTop: 4 }}>{e.motivo}</div>}
          </div>
        ))}
      </div>
    </Modal>
  );
}

// Helper that produces a log entry per changed field; consumers call applyEdit
// in their state reducer.
function buildLogEntries({ pesajeId, patch, original, actor, motivo }) {
  const fecha = new Date().toLocaleString("es-AR", { hour12: false }).replace(",", "");
  return Object.entries(patch)
    .filter(([k, v]) => original[k] !== v)
    .map(([campo, nuevo]) => ({
      id: `${pesajeId}-${campo}-${Date.now()}-${Math.random().toString(36).slice(2, 6)}`,
      pesajeId, fecha, usuario: actor, campo,
      anterior: original[campo], nuevo, motivo,
    }));
}

Object.assign(window, { EditPesajeModal, EgresoModal, LogModal, buildLogEntries });
